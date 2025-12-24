<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\Auth\PortalLoginController;
use App\Http\Controllers\Portal\EmployeeLeaveController;
use App\Http\Controllers\Portal\EmployeePayrollController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // 如果已登录，重定向到后台首页；否则重定向到登录页
    if (auth()->check()) {
        return redirect()->route('dashboard.index');
    }
    return redirect()->route('portal.login');
});

// 管理后台路由组（需要登录）
Route::middleware('auth')->group(function () {
    // 公司后台首页
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // 请假管理路由
    Route::resource('leaves', LeaveController::class);
    Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    
    // 薪资管理路由 - 自定义路由必须在resource路由之前定义
    Route::get('payrolls/monthly-calculation', [PayrollController::class, 'monthlyCalculation'])->name('payrolls.monthly-calculation');
    Route::post('payrolls/roll-over', [PayrollController::class, 'rollOver'])->name('payrolls.roll-over'); // [NEW]
    Route::post('payrolls/clear', [PayrollController::class, 'clearMonth'])->name('payrolls.clear'); // [NEW]
    Route::post('payrolls/lock', [PayrollController::class, 'lockMonth'])->name('payrolls.lock'); // [NEW]
    Route::post('payrolls/batch-store', [PayrollController::class, 'batchStore'])->name('payrolls.batch-store');
    Route::post('payrolls/{payroll}/mark-as-paid', [PayrollController::class, 'markAsPaid'])->name('payrolls.mark-as-paid');
    Route::get('payrolls/{payroll}/download', [PayrollController::class, 'download'])->name('payrolls.download');
    Route::get('payrolls/template/upload', [PayrollController::class, 'showTemplateUpload'])->name('payrolls.template-upload');
    Route::post('payrolls/template/upload', [PayrollController::class, 'uploadTemplate'])->name('payrolls.template-upload.post');
    Route::post('payrolls/template/delete', [PayrollController::class, 'deleteTemplate'])->name('payrolls.template-delete');
    Route::resource('payrolls', PayrollController::class);
    
    // 公司管理路由
    Route::resource('companies', CompanyController::class);
    
        // 公司切换路由
        Route::post('companies/{company}/switch', [\App\Http\Controllers\CompanySwitchController::class, 'switch'])->name('companies.switch');
        
        // 角色切换路由（测试用）
        Route::post('role/switch', [\App\Http\Controllers\RoleSwitchController::class, 'switch'])->name('role.switch');
        
        // 员工管理路由
        Route::resource('employees', EmployeeController::class);
    });

// 测试路由
Route::get('/test-leaves', function() {
    try {
        return 'Test: Leave routes working';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// 员工门户路由组
Route::prefix('portal')->name('portal.')->group(function () {
    // 认证路由（不需要登录）
    Route::get('login', [PortalLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [PortalLoginController::class, 'login'])->name('login');
    Route::post('logout', [PortalLoginController::class, 'logout'])->name('logout');
    
    // 需要登录的路由
    Route::middleware('auth')->group(function () {
        // 首页
        Route::get('dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
        
        // 请假管理
        Route::resource('leaves', EmployeeLeaveController::class);
        
        // 工资单管理
        Route::get('payrolls', [EmployeePayrollController::class, 'index'])->name('payrolls.index');
        Route::get('payrolls/{payroll}', [EmployeePayrollController::class, 'show'])->name('payrolls.show');
        Route::get('payrolls/{payroll}/download', [EmployeePayrollController::class, 'download'])->name('payrolls.download');
    });
});
