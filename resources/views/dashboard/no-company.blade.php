@extends('layouts.app')

@section('title', '选择公司')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-building" style="font-size: 5rem; color: #6c757d;"></i>
                </div>
                <h3 class="mb-3">请先选择要管理的公司</h3>
                <p class="text-muted mb-4">您还没有选择公司，或者还没有创建公司。请先创建或选择一个公司来开始使用系统。</p>
                
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('companies.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> 创建新公司
                    </a>
                    <a href="{{ route('companies.index') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-list"></i> 查看公司列表
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

