<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 获取当前用户的活动公司ID
     * 优先使用current_company_id，否则使用company_id
     * 如果没有设置，自动使用company_id作为current_company_id
     *
     * @return int|null
     */
    protected function getActiveCompanyId()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        
        $activeCompanyId = $user->active_company_id;
        
        // 如果没有current_company_id但有company_id，自动设置
        if (!$user->current_company_id && $user->company_id) {
            $user->current_company_id = $user->company_id;
            $user->save();
            $activeCompanyId = $user->company_id;
        }
        
        return $activeCompanyId;
    }

    /**
     * 检查用户是否有权限访问指定的公司
     *
     * @param int $companyId
     * @return bool
     */
    protected function hasAccessToCompany($companyId)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        // 管理员可以访问所有公司
        if ($user->isAdmin()) {
            return true;
        }
        
        return $user->hasAccessToCompany($companyId);
    }
    
    /**
     * 检查当前用户是否是管理员
     *
     * @return bool
     */
    protected function isAdmin()
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }
}
