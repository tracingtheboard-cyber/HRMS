@extends('layouts.app')

@section('title', '薪资管理')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cash-stack"></i> 薪资管理</h2>
    <div class="btn-group">
        @if(Auth::user()->isHR() || Auth::user()->isAdmin())
        <a href="{{ route('payrolls.template-upload') }}" class="btn btn-info">
            <i class="bi bi-file-earmark-word"></i> Word模板管理
        </a>
        @endif
        <a href="{{ route('payrolls.monthly-calculation') }}" class="btn btn-success">
            <i class="bi bi-table"></i> 月度工资核算
        </a>
        <a href="{{ route('payrolls.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> 新建薪资记录
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>员工</th>
                    <th>月份</th>
                    <th>基本工资</th>
                    <th>津贴</th>
                    <th>扣除</th>
                    <th>税费</th>
                    <th>总金额</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                <tr>
                    <td>{{ $payroll->id }}</td>
                    <td>{{ $payroll->user->name }}</td>
                    <td>{{ $payroll->year }}年{{ $payroll->month }}月</td>
                    <td>¥{{ number_format($payroll->base_salary, 2) }}</td>
                    <td>¥{{ number_format($payroll->allowances, 2) }}</td>
                    <td>¥{{ number_format($payroll->deductions, 2) }}</td>
                    <td>¥{{ number_format($payroll->tax, 2) }}</td>
                    <td><strong>¥{{ number_format($payroll->total_amount, 2) }}</strong></td>
                    <td>
                        @if($payroll->status === 'pending')
                            <span class="badge bg-warning">待发放</span>
                        @else
                            <span class="badge bg-success">已发放</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('payrolls.show', $payroll) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> 查看
                        </a>
                        @if($payroll->status === 'pending')
                            <a href="{{ route('payrolls.edit', $payroll) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> 编辑
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center">暂无薪资记录</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $payrolls->links() }}
        </div>
    </div>
</div>
@endsection

