<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 显示所有公司，让用户可以查看和管理
        $user = Auth::user();
        $currentCompanyId = null;
        
        if ($user) {
            $currentCompanyId = $user->current_company_id ?? $user->company_id;
        }
        
        // 如果是管理员，显示所有公司；否则只显示用户有权限的公司
        if ($user && $user->isAdmin()) {
            $companies = Company::withCount(['users', 'leaves', 'payrolls'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // 获取用户有权限的公司ID列表
            $companyIds = collect();
            if ($user) {
                $companyIds = $user->companies->pluck('id');
                if ($user->company_id) {
                    $companyIds->push($user->company_id);
                }
                $companyIds = $companyIds->unique();
            }
            
            $companies = Company::withCount(['users', 'leaves', 'payrolls'])
                ->whereIn('id', $companyIds)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('companies.index', compact('companies', 'currentCompanyId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('companies.create');
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
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:companies,code',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $company = Company::create($validated);

        // 将当前用户关联到新创建的公司
        $user = Auth::user();
        if ($user) {
            // 如果用户还没有主公司，设置为新创建的公司为主公司
            if (!$user->company_id) {
                $user->company_id = $company->id;
            }
            
            // 将新公司设置为当前选择的公司
            $user->current_company_id = $company->id;
            
            // 将用户添加到公司的管理列表中（多对多关系）
            $user->companies()->syncWithoutDetaching([$company->id]);
            
            $user->save();
        }

        // 重定向到公司后台首页
        return redirect()->route('dashboard.index')
            ->with('success', '公司创建成功，您已自动关联到该公司');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        $company->load(['users', 'leaves' => function($query) {
            $query->latest()->limit(10);
        }, 'payrolls' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:companies,code,' . $company->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', '公司信息已更新');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        // 检查是否有用户关联
        if ($company->users()->count() > 0) {
            return redirect()->route('companies.index')
                ->with('error', '该公司下还有员工，无法删除');
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', '公司已删除');
    }
}
