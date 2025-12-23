<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PortalLoginController extends Controller
{
    /**
     * 显示员工登录页面
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.auth.login');
    }

    /**
     * 处理员工登录
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('portal.dashboard'))
                ->with('success', '欢迎回来！');
        }

        throw ValidationException::withMessages([
            'email' => ['邮箱或密码错误'],
        ]);
    }

    /**
     * 员工登出
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('portal.login')
            ->with('success', '已成功登出');
    }
}
