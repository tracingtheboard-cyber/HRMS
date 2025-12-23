<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getActiveCompanyId();
        
        // 如果没有公司ID，返回空数据但仍然显示页面
        if (!$companyId) {
            $leaves = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                15,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $stats = [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ];
            return view('leaves.index', compact('leaves', 'stats'));
        }
        
        // 构建查询
        $query = Leave::with(['user', 'approver'])
            ->whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        
        // 状态筛选
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // 类型筛选
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }
        
        // 搜索（员工姓名）
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        
        // 日期范围筛选
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }
        
        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // 统计数据
        $stats = [
            'total' => Leave::whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->count(),
            'pending' => Leave::whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('status', 'pending')->count(),
            'approved' => Leave::whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('status', 'approved')->count(),
            'rejected' => Leave::whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('status', 'rejected')->count(),
        ];

        return view('leaves.index', compact('leaves', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('leaves.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|in:annual,sick,personal,maternity,paternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // 最大10MB
        ]);

        // 处理文件上传
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('leave_evidence', $fileName, 'public');
            $validated['evidence'] = $path;
        }

        // 计算请假天数
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        $validated['user_id'] = Auth::id();
        $validated['days'] = $days;
        $validated['status'] = 'pending';

        Leave::create($validated);

        return redirect()->route('leaves.index')
            ->with('success', '请假申请已提交，等待审批');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function show(Leave $leave)
    {
        $leave->load(['user', 'approver']);
        
        // 检查权限：管理员可以查看所有公司的请假记录，其他用户只能查看当前选择公司的
        $user = Auth::user();
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($leave->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('leaves.index')
                ->with('error', '您无权查看该请假记录');
        }

        return view('leaves.show', compact('leave'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function edit(Leave $leave)
    {
        $leave->load('user');
        $user = Auth::user();
        
        // 检查权限：管理员可以编辑任何公司的申请，其他用户只能编辑当前选择公司且自己的待审批申请
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($leave->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('leaves.index')
                ->with('error', '您无权编辑该请假记录');
        }
        
        if ($leave->user_id !== Auth::id() || $leave->status !== 'pending') {
            return redirect()->route('leaves.index')
                ->with('error', '您只能编辑自己的待审批申请');
        }

        return view('leaves.edit', compact('leave'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Leave $leave)
    {
        $leave->load('user');
        
        // 检查权限：管理员可以更新任何公司的申请，其他用户只能更新当前选择公司且自己的待审批申请
        $user = Auth::user();
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($leave->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('leaves.index')
                ->with('error', '您无权编辑该请假记录');
        }
        
        if ($leave->user_id !== Auth::id() || $leave->status !== 'pending') {
            return redirect()->route('leaves.index')
                ->with('error', '您只能编辑自己的待审批申请');
        }

        $validated = $request->validate([
            'leave_type' => 'required|in:annual,sick,personal,maternity,paternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // 最大10MB
        ]);

        // 处理文件上传
        if ($request->hasFile('evidence')) {
            // 删除旧文件
            if ($leave->evidence && Storage::disk('public')->exists($leave->evidence)) {
                Storage::disk('public')->delete($leave->evidence);
            }
            
            $file = $request->file('evidence');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('leave_evidence', $fileName, 'public');
            $validated['evidence'] = $path;
        }

        // 计算请假天数
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $validated['days'] = $startDate->diffInDays($endDate) + 1;

        $leave->update($validated);

        return redirect()->route('leaves.index')
            ->with('success', '请假申请已更新');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function destroy(Leave $leave)
    {
        $leave->load('user');
        
        // 检查权限：管理员可以删除任何公司的申请，其他用户只能删除当前选择公司且自己的待审批申请
        $user = Auth::user();
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($leave->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('leaves.index')
                ->with('error', '您无权删除该请假记录');
        }
        
        if (!$user->isAdmin() && ($leave->user_id !== Auth::id() || $leave->status !== 'pending')) {
            return redirect()->route('leaves.index')
                ->with('error', '您只能删除自己的待审批申请');
        }

        $leave->delete();

        return redirect()->route('leaves.index')
            ->with('success', '请假申请已删除');
    }

    /**
     * 审批请假申请
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, Leave $leave)
    {
        $leave->load('user');
        $user = Auth::user();
        
        // 检查权限：管理员可以审批任何公司的请假申请，其他用户只能审批当前选择公司的
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($leave->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('leaves.index')
                ->with('error', '您无权审批该请假申请');
        }
        
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ]);

        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.index')
                ->with('error', '该申请已处理');
        }

        if ($validated['action'] === 'approve') {
            $leave->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            $message = '请假申请已批准';
        } else {
            $leave->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);
            $message = '请假申请已拒绝';
        }

        return redirect()->route('leaves.show', $leave)
            ->with('success', $message);
    }
}
