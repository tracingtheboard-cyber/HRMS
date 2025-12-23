@extends('layouts.app')

@section('title', '公司后台 - ' . $company->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-speedometer2"></i> {{ $company->name }} - 后台管理</h2>
        <p class="text-muted mb-0">欢迎使用HR管理系统</p>
    </div>
    <a href="{{ route('companies.show', $company) }}" class="btn btn-outline-primary">
        <i class="bi bi-building"></i> 查看公司详情
    </a>
</div>

<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">员工总数</h6>
                        <h3 class="mb-0">{{ $stats['total_employees'] }}</h3>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">待审批请假</h6>
                        <h3 class="mb-0">{{ $stats['pending_leaves'] }}</h3>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem;">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">待发放薪资</h6>
                        <h3 class="mb-0">{{ $stats['pending_payrolls'] }}</h3>
                    </div>
                    <div class="text-info" style="font-size: 2.5rem;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">本月薪资总额</h6>
                        <h5 class="mb-0">¥{{ number_format($stats['monthly_payroll_total'], 2) }}</h5>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-currency-yen"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 快捷操作 -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> 快捷操作</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('leaves.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> 新建请假申请
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('payrolls.create') }}" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle"></i> 新建薪资记录
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('leaves.index') }}" class="btn btn-warning w-100">
                            <i class="bi bi-calendar-check"></i> 查看请假
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('payrolls.index') }}" class="btn btn-info w-100">
                            <i class="bi bi-cash"></i> 查看薪资
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 最近记录 -->
<div class="row">
    <!-- 最近请假记录 -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> 最近请假记录</h5>
                <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-primary">查看全部</a>
            </div>
            <div class="card-body">
                @if($recent_leaves->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>员工</th>
                                <th>类型</th>
                                <th>天数</th>
                                <th>状态</th>
                                <th>日期</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_leaves as $leave)
                            <tr>
                                <td>{{ $leave->user->name }}</td>
                                <td>{{ $leave->leave_type_text }}</td>
                                <td>{{ $leave->days }}天</td>
                                <td>
                                    @if($leave->status === 'pending')
                                        <span class="badge bg-warning">待审批</span>
                                    @elseif($leave->status === 'approved')
                                        <span class="badge bg-success">已批准</span>
                                    @else
                                        <span class="badge bg-danger">已拒绝</span>
                                    @endif
                                </td>
                                <td>{{ $leave->start_date->format('m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center mb-0">暂无请假记录</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- 最近薪资记录 -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> 最近薪资记录</h5>
                <a href="{{ route('payrolls.index') }}" class="btn btn-sm btn-outline-primary">查看全部</a>
            </div>
            <div class="card-body">
                @if($recent_payrolls->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>员工</th>
                                <th>月份</th>
                                <th>总金额</th>
                                <th>状态</th>
                                <th>日期</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_payrolls as $payroll)
                            <tr>
                                <td>{{ $payroll->user->name }}</td>
                                <td>{{ $payroll->year }}-{{ str_pad($payroll->month, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>¥{{ number_format($payroll->total_amount, 2) }}</td>
                                <td>
                                    @if($payroll->status === 'pending')
                                        <span class="badge bg-warning">待发放</span>
                                    @else
                                        <span class="badge bg-success">已发放</span>
                                    @endif
                                </td>
                                <td>{{ $payroll->created_at->format('m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center mb-0">暂无薪资记录</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 统计概览 -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> 数据概览</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-primary">{{ $stats['total_leaves'] }}</h4>
                            <p class="mb-0 text-muted">总请假记录</p>
                            <small class="text-success">{{ $stats['approved_leaves'] }} 已批准</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-warning">{{ $stats['pending_leaves'] }}</h4>
                            <p class="mb-0 text-muted">待审批</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-info">{{ $stats['total_payrolls'] }}</h4>
                            <p class="mb-0 text-muted">总薪资记录</p>
                            <small class="text-success">{{ $stats['paid_payrolls'] }} 已发放</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-warning">{{ $stats['pending_payrolls'] }}</h4>
                            <p class="mb-0 text-muted">待发放</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

