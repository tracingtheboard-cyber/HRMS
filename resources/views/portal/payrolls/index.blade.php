@extends('portal.layout')

@section('title', '我的工资单')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cash-stack"></i> 我的工资单</h2>
</div>

<!-- 筛选 -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('portal.payrolls.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">年份</label>
                <select name="year" class="form-select">
                    <option value="">全部年份</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}年
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">月份</label>
                <select name="month" class="form-select">
                    <option value="">全部月份</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ $i }}月
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">状态</label>
                <select name="status" class="form-select">
                    <option value="">全部状态</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>待发放</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>已发放</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> 筛选
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 工资单列表 -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>年月</th>
                        <th>基本工资</th>
                        <th>津贴</th>
                        <th>扣除</th>
                        <th>税费</th>
                        <th>实发金额</th>
                        <th>状态</th>
                        <th>发放时间</th>
                        <th width="150">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                    <tr>
                        <td>
                            <strong>{{ $payroll->year }}年{{ $payroll->month }}月</strong>
                        </td>
                        <td>¥{{ number_format($payroll->base_salary, 2) }}</td>
                        <td class="text-success">+¥{{ number_format($payroll->allowances, 2) }}</td>
                        <td class="text-danger">-¥{{ number_format($payroll->deductions, 2) }}</td>
                        <td class="text-danger">-¥{{ number_format($payroll->tax, 2) }}</td>
                        <td>
                            <strong class="text-primary fs-5">¥{{ number_format($payroll->total_amount, 2) }}</strong>
                        </td>
                        <td>
                            @if($payroll->status === 'paid')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> 已发放
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> 待发放
                                </span>
                            @endif
                        </td>
                        <td>
                            {{ $payroll->paid_at ? $payroll->paid_at->format('Y-m-d H:i') : '-' }}
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('portal.payrolls.show', $payroll) }}" class="btn btn-outline-info" title="查看">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('portal.payrolls.download', $payroll) }}" class="btn btn-outline-success" title="下载">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">暂无工资单记录</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payrolls->hasPages())
        <div class="mt-3">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>
</div>
@endsection



