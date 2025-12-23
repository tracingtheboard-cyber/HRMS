@extends('layouts.app')

@section('title', '公司管理')

@section('content')
@auth
@php
    $user = auth()->user();
    $hasCompany = $user->company_id || $user->companies->count() > 0;
@endphp
@if(!$hasCompany)
<div class="alert alert-warning">
    <h5><i class="bi bi-exclamation-triangle"></i> 提示</h5>
    <p>您还没有关联任何公司。请先创建一个公司，或者联系管理员将您添加到现有公司。</p>
    <p class="mb-0">如果您是HR，需要管理多个公司，请联系管理员为您分配公司权限。</p>
</div>
@endif
@endauth

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> 公司管理</h2>
    <a href="{{ route('companies.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> 新建公司
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>公司名称</th>
                    <th>公司编码</th>
                    <th>联系电话</th>
                    <th>邮箱</th>
                    <th>员工数</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th width="250">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr class="{{ isset($currentCompanyId) && $company->id == $currentCompanyId ? 'table-primary' : '' }}">
                    <td>{{ $company->id }}</td>
                    <td>
                        <strong>{{ $company->name }}</strong>
                        @if(isset($currentCompanyId) && $company->id == $currentCompanyId)
                            <span class="badge bg-success ms-2">
                                <i class="bi bi-check-circle"></i> 当前选择
                            </span>
                        @endif
                    </td>
                    <td>{{ $company->code ?? '-' }}</td>
                    <td>{{ $company->phone ?? '-' }}</td>
                    <td>{{ $company->email ?? '-' }}</td>
                    <td>{{ $company->users_count }}</td>
                    <td>
                        @if($company->is_active)
                            <span class="badge bg-success">启用</span>
                        @else
                            <span class="badge bg-secondary">禁用</span>
                        @endif
                    </td>
                    <td>{{ $company->created_at->format('Y-m-d') }}</td>
                    <td>
                        @if(isset($currentCompanyId) && $company->id == $currentCompanyId)
                            <span class="badge bg-success me-2">
                                <i class="bi bi-check-circle"></i> 当前公司
                            </span>
                        @else
                            <form action="{{ route('companies.switch', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('确定要切换到「{{ $company->name }}」吗？切换后，您将管理该公司的员工、请假和薪资记录。');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary mb-1" title="切换到这个公司进行管理">
                                    <i class="bi bi-arrow-left-right"></i> 选择
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info mb-1" title="查看详情">
                            <i class="bi bi-eye"></i> 查看
                        </a>
                        <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning mb-1" title="编辑公司">
                            <i class="bi bi-pencil"></i> 编辑
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">暂无公司记录</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $companies->links() }}
        </div>
    </div>
</div>
@endsection

