<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $companyId = $this->getActiveCompanyId();
        $hasCompany = (bool) $companyId;
        
        if (!$companyId) {
            $employees = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                15,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $stats = [
                'total' => 0,
                'this_month' => 0,
            ];
            return view('employees.index', compact('employees', 'stats', 'hasCompany'));
        }
        
        // 构建查询：只显示当前公司的员工
        $query = User::where('company_id', $companyId);
        
        // 搜索功能
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        $employees = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // 统计数据
        $stats = [
            'total' => User::where('company_id', $companyId)->count(),
            'this_month' => User::where('company_id', $companyId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];
        
        return view('employees.index', compact('employees', 'stats', 'hasCompany'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companyId = $this->getActiveCompanyId();
        if (!$companyId) {
            return redirect()->route('employees.index')
                ->with('error', '请先选择要管理的公司');
        }
        
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $companyId = $this->getActiveCompanyId();
        if (!$companyId) {
            return redirect()->route('employees.index')
                ->with('error', '请先选择要管理的公司');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['company_id'] = $companyId;
        
        User::create($validated);

        return redirect()->route('employees.index')
            ->with('success', '员工创建成功');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(User $employee)
    {
        $companyId = $this->getActiveCompanyId();
        
        // 检查权限：管理员可以查看任何公司的员工，其他用户只能查看当前公司的
        $user = Auth::user();
        if (!$user->isAdmin() && ($employee->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('employees.index')
                ->with('error', '您无权查看该员工信息');
        }
        
        // 加载关联数据
        $employee->load(['leaves', 'payrolls']);
        
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(User $employee)
    {
        $companyId = $this->getActiveCompanyId();
        
        // 检查权限：管理员可以编辑任何公司的员工，其他用户只能编辑当前公司的
        $user = Auth::user();
        if (!$user->isAdmin() && ($employee->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('employees.index')
                ->with('error', '您无权编辑该员工信息');
        }
        
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $employee)
    {
        $companyId = $this->getActiveCompanyId();
        
        // 检查权限：管理员可以更新任何公司的员工，其他用户只能更新当前公司的
        $user = Auth::user();
        if (!$user->isAdmin() && ($employee->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('employees.index')
                ->with('error', '您无权编辑该员工信息');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', '员工信息已更新');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $employee)
    {
        $companyId = $this->getActiveCompanyId();
        
        // 检查权限：管理员可以删除任何公司的员工，其他用户只能删除当前公司的
        $user = Auth::user();
        if (!$user->isAdmin() && ($employee->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('employees.index')
                ->with('error', '您无权删除该员工');
        }
        
        // 不能删除自己
        if ($employee->id === Auth::id()) {
            return redirect()->route('employees.index')
                ->with('error', '不能删除自己的账号');
        }
        
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', '员工已删除');
    }
}
