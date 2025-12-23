@extends('portal.layout')

@section('title', '员工门户首页')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">欢迎回来，{{ $user->name }}！</h2>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-envelope"></i> {{ $user->email }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="avatar-lg bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 class="text-primary mt-2 mb-1">{{ $stats['total_leaves'] }}</h3>
                <p class="text-muted mb-0">请假记录</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-clock" style="font-size: 2rem; color: #ffc107;"></i>
                <h3 class="text-warning mt-2 mb-1">{{ $stats['pending_leaves'] }}</h3>
                <p class="text-muted mb-0">待审批</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-check-circle" style="font-size: 2rem; color: #198754;"></i>
                <h3 class="text-success mt-2 mb-1">{{ $stats['approved_leaves'] }}</h3>
                <p class="text-muted mb-0">已批准</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack" style="font-size: 2rem; color: #0dcaf0;"></i>
                <h3 class="text-info mt-2 mb-1">{{ $stats['total_payrolls'] }}</h3>
                <p class="text-muted mb-0">工资单</p>
            </div>
        </div>
    </div>
</div>

<!-- 快捷操作 -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> 快捷操作</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('portal.leaves.create') }}" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-plus-circle"></i> 申请请假
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('portal.leaves.index') }}" class="btn btn-outline-primary btn-lg w-100">
                            <i class="bi bi-calendar-check"></i> 查看请假记录
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('portal.payrolls.index') }}" class="btn btn-outline-info btn-lg w-100">
                            <i class="bi bi-cash-stack"></i> 查看工资单
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- 最近请假记录 -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> 最近请假记录</h5>
                <a href="{{ route('portal.leaves.index') }}" class="btn btn-sm btn-outline-primary">查看全部</a>
            </div>
            <div class="card-body">
                @forelse($recentLeaves as $leave)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <strong>{{ $leave->leave_type_text }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $leave->start_date->format('Y-m-d') }} 至 {{ $leave->end_date->format('Y-m-d') }}
                            ({{ $leave->days }}天)
                        </small>
                    </div>
                    <div>
                        @if($leave->status === 'pending')
                            <span class="badge bg-warning text-dark">待审批</span>
                        @elseif($leave->status === 'approved')
                            <span class="badge bg-success">已批准</span>
                        @else
                            <span class="badge bg-danger">已拒绝</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">暂无请假记录</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 最近工资单 -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> 最近工资单</h5>
                <a href="{{ route('portal.payrolls.index') }}" class="btn btn-sm btn-outline-info">查看全部</a>
            </div>
            <div class="card-body">
                @forelse($recentPayrolls as $payroll)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <strong>{{ $payroll->year }}年{{ $payroll->month }}月</strong>
                        <br>
                        <small class="text-muted">
                            总金额：<strong class="text-success">¥{{ number_format($payroll->total_amount, 2) }}</strong>
                        </small>
                    </div>
                    <div>
                        @if($payroll->status === 'paid')
                            <span class="badge bg-success">已发放</span>
                        @else
                            <span class="badge bg-warning text-dark">待发放</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">暂无工资单</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection



