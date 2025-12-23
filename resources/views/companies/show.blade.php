@extends('layouts.app')

@section('title', '公司详情')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-eye"></i> 公司详情 - {{ $company->name }}</h4>
                <div>
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> 编辑
                    </a>
                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> 返回
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>公司名称：</strong> {{ $company->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>公司编码：</strong> {{ $company->code ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>联系电话：</strong> {{ $company->phone ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>邮箱：</strong> {{ $company->email ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>公司地址：</strong> {{ $company->address ?? '-' }}
                    </div>
                </div>

                @if($company->description)
                <div class="mb-3">
                    <strong>公司描述：</strong>
                    <div class="border rounded p-3 mt-2">{{ $company->description }}</div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>状态：</strong>
                        @if($company->is_active)
                            <span class="badge bg-success">启用</span>
                        @else
                            <span class="badge bg-secondary">禁用</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>创建时间：</strong> {{ $company->created_at->format('Y-m-d H:i:s') }}
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="card-title">{{ $company->users_count }}</h5>
                                <p class="card-text">员工总数</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="card-title">{{ $company->leaves_count }}</h5>
                                <p class="card-text">请假记录</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="card-title">{{ $company->payrolls_count }}</h5>
                                <p class="card-text">薪资记录</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@auth
@php
    $user = auth()->user();
    $hasAccess = $user->hasAccessToCompany($company->id);
@endphp
@if(!$hasAccess)
<div class="row mt-3">
    <div class="col-md-10 offset-md-1">
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> 提示</h5>
            <p>您还没有管理该公司的权限。如果您需要管理此公司，请联系系统管理员为您分配权限，或者您可以在"公司管理"页面创建新公司。</p>
        </div>
    </div>
</div>
@endif
@endauth
@endsection

