@extends('portal.layout')

@section('title', '工资单详情')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-eye"></i> 工资单详情 - {{ $payroll->year }}年{{ $payroll->month }}月</h4>
                <div>
                    <a href="{{ route('portal.payrolls.download', $payroll) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-download"></i> 下载工资单PDF
                    </a>
                    <a href="{{ route('portal.payrolls.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> 返回
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">员工姓名</small>
                                <h5 class="mb-0">
                                    <i class="bi bi-person-circle text-primary"></i>
                                    {{ $payroll->user->name }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">状态</small>
                                <h5 class="mb-0">
                                    @if($payroll->status === 'paid')
                                        <span class="badge bg-success fs-6">
                                            <i class="bi bi-check-circle"></i> 已发放
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="bi bi-clock"></i> 待发放
                                        </span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <small class="text-muted d-block mb-2">工资月份</small>
                                <h3 class="text-primary mb-0">
                                    {{ $payroll->year }}年{{ $payroll->month }}月
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row mb-2">
                    <div class="col-md-8">
                        <strong>基本工资：</strong>
                    </div>
                    <div class="col-md-4 text-end">
                        <strong>¥{{ number_format($payroll->base_salary, 2) }}</strong>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-8">
                        <strong class="text-success">津贴：</strong>
                    </div>
                    <div class="col-md-4 text-end text-success">
                        + ¥{{ number_format($payroll->allowances, 2) }}
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-8">
                        <strong class="text-danger">扣除：</strong>
                    </div>
                    <div class="col-md-4 text-end text-danger">
                        - ¥{{ number_format($payroll->deductions, 2) }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-8">
                        <strong class="text-danger">税费：</strong>
                    </div>
                    <div class="col-md-4 text-end text-danger">
                        - ¥{{ number_format($payroll->tax, 2) }}
                    </div>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="mb-0">实发金额：</h4>
                    </div>
                    <div class="col-md-4 text-end">
                        <h3 class="text-primary mb-0">¥{{ number_format($payroll->total_amount, 2) }}</h3>
                    </div>
                </div>

                @if($payroll->notes)
                <div class="mb-3">
                    <strong>备注：</strong>
                    <div class="border rounded p-3 mt-2 bg-light">{{ $payroll->notes }}</div>
                </div>
                @endif

                @if($payroll->status === 'paid' && $payroll->paid_at)
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">发放时间</small>
                    <p class="mb-0">
                        <i class="bi bi-calendar-check"></i>
                        {{ $payroll->paid_at->format('Y年m月d日 H:i:s') }}
                    </p>
                </div>
                @endif

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">创建时间</small>
                    <p class="mb-0">
                        <i class="bi bi-calendar"></i>
                        {{ $payroll->created_at->format('Y年m月d日 H:i:s') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

