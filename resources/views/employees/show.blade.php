@extends('layouts.app')

@section('title', '员工详情')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> 员工信息</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ mb_substr($employee->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <h4>{{ $employee->name }}</h4>
                        <p class="text-muted mb-1"><i class="bi bi-envelope"></i> {{ $employee->email }}</p>
                        <p class="text-muted mb-0"><i class="bi bi-calendar"></i> 注册时间：{{ $employee->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">员工ID</dt>
                            <dd class="col-sm-8">{{ $employee->id }}</dd>

                            <dt class="col-sm-4">邮箱</dt>
                            <dd class="col-sm-8">{{ $employee->email }}</dd>

                            <dt class="col-sm-4">注册时间</dt>
                            <dd class="col-sm-8">{{ $employee->created_at->format('Y-m-d H:i:s') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> 编辑
                </a>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回列表
                </a>
            </div>
        </div>

        <!-- 统计信息 -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-1">{{ $employee->leaves->count() }}</h3>
                        <p class="text-muted mb-0">请假记录</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-1">{{ $employee->leaves->where('status', 'pending')->count() }}</h3>
                        <p class="text-muted mb-0">待审批</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-1">{{ $employee->payrolls->count() }}</h3>
                        <p class="text-muted mb-0">薪资记录</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">快速操作</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('employees.edit', $employee) }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-pencil"></i> 编辑员工信息
                </a>
                <a href="{{ route('leaves.index', ['search' => $employee->name]) }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-calendar-check"></i> 查看请假记录
                </a>
                <a href="{{ route('payrolls.index', ['user_id' => $employee->id]) }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-cash-stack"></i> 查看薪资记录
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .avatar-lg {
        font-weight: 500;
    }
</style>
@endsection

