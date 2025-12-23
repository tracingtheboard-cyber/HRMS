<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySwitchController extends Controller
{
    /**
     * 切换当前选择的公司
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function switch(Request $request, Company $company)
    {
        $user = Auth::user();
        
        // 管理员可以切换到任何公司，其他用户需要检查权限
        if (!$user->isAdmin() && !$user->hasAccessToCompany($company->id)) {
            return back()->with('error', '您没有权限访问该公司');
        }

        // 更新当前选择的公司
        $user->current_company_id = $company->id;
        $user->save();

        // 重定向到公司后台首页
        return redirect()->route('dashboard.index')
            ->with('success', "已切换到公司：{$company->name}");
    }
}
