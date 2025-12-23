<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    /**
     * 员工门户首页
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // 加载用户的请假和工资单数据
        $user->load(['leaves', 'payrolls']);
        
        // 统计数据
        $stats = [
            'total_leaves' => $user->leaves()->count(),
            'pending_leaves' => $user->leaves()->where('status', 'pending')->count(),
            'approved_leaves' => $user->leaves()->where('status', 'approved')->count(),
            'total_payrolls' => $user->payrolls()->count(),
            'paid_payrolls' => $user->payrolls()->where('status', 'paid')->count(),
        ];
        
        // 最近的请假记录
        $recentLeaves = $user->leaves()->orderBy('created_at', 'desc')->limit(5)->get();
        
        // 最近的工资单
        $recentPayrolls = $user->payrolls()->orderBy('created_at', 'desc')->limit(5)->get();
        
        return view('portal.dashboard', compact('user', 'stats', 'recentLeaves', 'recentPayrolls'));
    }
}
