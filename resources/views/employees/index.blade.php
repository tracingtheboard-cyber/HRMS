@extends('layouts.app')

@section('title', '员工管理')

@section('content')
@php
    // 如果没有传递hasCompany变量，使用默认判断
    $hasCompany = $hasCompany ?? (auth()->check() && (auth()->user()->company_id || auth()->user()->current_company_id));
@endphp

@if(!$hasCompany)
<div class="alert alert-info mb-4">
    <h5><i class="bi bi-info-circle"></i> 提示</h5>
    <p class="mb-2">您还没有关联公司，请先创建或选择一个公司才能使用员工管理功能。</p>
    <div>
        <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> 创建公司
        </a>
        <a href="{{ route('companies.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-list"></i> 查看公司列表
        </a>
    </div>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> 员工管理</h2>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> 添加员工
    </a>
</div>

<!-- 统计卡片 -->
@if($hasCompany && isset($stats) && $stats)
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-people" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 class="text-primary mt-2 mb-1">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">员工总数</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-person-plus" style="font-size: 2rem; color: #198754;"></i>
                <h3 class="text-success mt-2 mb-1">{{ $stats['this_month'] }}</h3>
                <p class="text-muted mb-0">本月新增</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- 搜索 -->
@if($hasCompany)
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="搜索员工姓名或邮箱..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> 搜索
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> 重置
                </a>
            </div>
        </form>
    </div>
</div>
@endif

<!-- 员工列表 -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>姓名</th>
                        <th>邮箱</th>
                        <th>注册时间</th>
                        <th width="200">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                    {{ mb_substr($employee->name, 0, 1) }}
                                </div>
                                <strong>{{ $employee->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-info" title="查看">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-warning" title="编辑">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($employee->id !== auth()->id())
                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('确定要删除该员工吗？此操作不可撤销！');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="删除">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-people" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">暂无员工记录</p>
                                <a href="{{ route('employees.create') }}" class="btn btn-primary mt-3">
                                    <i class="bi bi-person-plus"></i> 添加第一个员工
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
        <div class="mt-3">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .avatar-sm {
        font-size: 0.875rem;
        font-weight: 500;
    }
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }
</style>
@endsection

