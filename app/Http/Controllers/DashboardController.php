<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * 显示公司后台首页
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // 如果没有用户，也显示no-company页面（因为可能需要先创建用户）
        if (!$user) {
            return view('dashboard.no-company');
        }
        
        $companyId = $this->getActiveCompanyId();
        
        // 管理员如果没有选择公司，自动选择第一个公司（方便测试）
        if (!$companyId && $user->isAdmin()) {
            $firstCompany = Company::first();
            if ($firstCompany) {
                $user->current_company_id = $firstCompany->id;
                $user->save();
                $companyId = $firstCompany->id;
            }
        }
        
        if (!$companyId) {
            // 如果没有公司，显示提示页面
            return view('dashboard.no-company');
        }
        
        // 检查权限（管理员总是有权限）
        if (!$this->hasAccessToCompany($companyId)) {
            return redirect()->route('companies.index')
                ->with('error', '您没有权限访问该公司');
        }
        
        $company = Company::findOrFail($companyId);
        
        // 获取统计数据
        $stats = [
            // 员工统计
            'total_employees' => User::where('company_id', $companyId)->count(),
            
            // 请假统计
            'total_leaves' => Leave::whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->count(),
            'pending_leaves' => Leave::whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', 'pending')->count(),
            'approved_leaves' => Leave::whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', 'approved')->count(),
            
            // 薪资统计
            'total_payrolls' => Payroll::whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->count(),
            'pending_payrolls' => Payroll::whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', 'pending')->count(),
            'paid_payrolls' => Payroll::whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', 'paid')->count(),
            
            // 本月薪资总额
            'monthly_payroll_total' => Payroll::whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('year', Carbon::now()->year)
            ->where('month', Carbon::now()->month)
            ->sum('total_amount') ?? 0,
        ];
        
        // 最近的请假记录
        $recent_leaves = Leave::with(['user', 'approver'])
            ->whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // 最近的薪资记录
        $recent_payrolls = Payroll::with('user')
            ->whereHas('user', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboard.index', compact('company', 'stats', 'recent_leaves', 'recent_payrolls'));
    }
}
