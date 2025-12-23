<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    /**
     * 切换用户角色（仅用于测试）
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function switch(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:employee,hr,admin',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('portal.login')
                ->with('error', '请先登录');
        }

        // 更新用户角色
        $user->role = $validated['role'];
        $user->save();

        // 根据角色重定向到相应页面
        if ($validated['role'] === 'employee') {
            return redirect()->route('portal.dashboard')
                ->with('success', '角色已切换为：员工');
        } else {
            return redirect()->route('dashboard.index')
                ->with('success', "角色已切换为：{$user->role_text}");
        }
    }
}
