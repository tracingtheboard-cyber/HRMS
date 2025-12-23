<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeLeaveController extends Controller
{
    /**
     * 显示员工的所有请假记录
     */
    public function index()
    {
        $user = Auth::user();
        $leaves = $user->leaves()
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('portal.leaves.index', compact('leaves'));
    }

    /**
     * 显示申请请假表单
     */
    public function create()
    {
        return view('portal.leaves.create');
    }

    /**
     * 保存请假申请
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

        return redirect()->route('portal.leaves.index')
            ->with('success', '请假申请已提交，等待审批');
    }

    /**
     * 显示请假详情
     */
    public function show(Leave $leave)
    {
        // 确保只能查看自己的请假记录
        if ($leave->user_id !== Auth::id()) {
            abort(403, '无权查看此请假记录');
        }
        
        $leave->load('approver');
        return view('portal.leaves.show', compact('leave'));
    }

    /**
     * 编辑待审批的请假申请
     */
    public function edit(Leave $leave)
    {
        // 确保只能编辑自己的待审批申请
        if ($leave->user_id !== Auth::id() || $leave->status !== 'pending') {
            abort(403, '只能编辑自己的待审批申请');
        }
        
        return view('portal.leaves.edit', compact('leave'));
    }

    /**
     * 更新请假申请
     */
    public function update(Request $request, Leave $leave)
    {
        // 确保只能更新自己的待审批申请
        if ($leave->user_id !== Auth::id() || $leave->status !== 'pending') {
            abort(403, '只能编辑自己的待审批申请');
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

        return redirect()->route('portal.leaves.index')
            ->with('success', '请假申请已更新');
    }

    /**
     * 取消/删除待审批的请假申请
     */
    public function destroy(Leave $leave)
    {
        // 确保只能删除自己的待审批申请
        if ($leave->user_id !== Auth::id() || $leave->status !== 'pending') {
            abort(403, '只能删除自己的待审批申请');
        }
        
        $leave->delete();

        return redirect()->route('portal.leaves.index')
            ->with('success', '请假申请已删除');
    }
}
