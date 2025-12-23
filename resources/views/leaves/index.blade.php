@extends('layouts.app')

@section('title', '请假管理')

@section('content')
@php
    $hasCompany = auth()->check() && (auth()->user()->company_id || auth()->user()->current_company_id);
@endphp

@if(!$hasCompany)
<div class="alert alert-info mb-4">
    <h5><i class="bi bi-info-circle"></i> 提示</h5>
    <p class="mb-2">您还没有关联公司，请先创建或选择一个公司才能使用请假管理功能。</p>
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
    <h2><i class="bi bi-calendar-check"></i> 请假管理</h2>
    @if($hasCompany)
    <a href="{{ route('leaves.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> 新建请假申请
    </a>
    @endif
</div>

<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 class="text-primary mb-1">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">总请假记录</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h3 class="text-warning mb-1">{{ $stats['pending'] }}</h3>
                <p class="text-muted mb-0">待审批</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="text-success mb-1">{{ $stats['approved'] }}</h3>
                <p class="text-muted mb-0">已批准</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">{{ $stats['rejected'] }}</h3>
                <p class="text-muted mb-0">已拒绝</p>
            </div>
        </div>
    </div>
</div>

<!-- 筛选和搜索 -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('leaves.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">状态筛选</label>
                <select name="status" class="form-select">
                    <option value="">全部状态</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>待审批</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>已批准</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>已拒绝</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">请假类型</label>
                <select name="leave_type" class="form-select">
                    <option value="">全部类型</option>
                    <option value="annual" {{ request('leave_type') == 'annual' ? 'selected' : '' }}>年假</option>
                    <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>病假</option>
                    <option value="personal" {{ request('leave_type') == 'personal' ? 'selected' : '' }}>事假</option>
                    <option value="maternity" {{ request('leave_type') == 'maternity' ? 'selected' : '' }}>产假</option>
                    <option value="paternity" {{ request('leave_type') == 'paternity' ? 'selected' : '' }}>陪产假</option>
                    <option value="other" {{ request('leave_type') == 'other' ? 'selected' : '' }}>其他</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">开始日期</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">结束日期</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">搜索员工</label>
                <input type="text" name="search" class="form-control" placeholder="员工姓名" value="{{ request('search') }}">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> 筛选
                </button>
                <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> 重置
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 请假列表 -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>员工</th>
                        <th>请假类型</th>
                        <th>开始日期</th>
                        <th>结束日期</th>
                        <th>天数</th>
                        <th>状态</th>
                        <th>申请时间</th>
                        <th width="150">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td>{{ $leave->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    {{ mb_substr($leave->user->name, 0, 1) }}
                                </div>
                                {{ $leave->user->name }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $leave->leave_type_text }}</span>
                        </td>
                        <td>{{ $leave->start_date->format('Y-m-d') }}</td>
                        <td>{{ $leave->end_date->format('Y-m-d') }}</td>
                        <td><strong>{{ $leave->days }}</strong> 天</td>
                        <td>
                            @if($leave->status === 'pending')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> 待审批
                                </span>
                            @elseif($leave->status === 'approved')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> 已批准
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> 已拒绝
                                </span>
                            @endif
                        </td>
                        <td>{{ $leave->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('leaves.show', $leave) }}" class="btn btn-outline-info" title="查看详情">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($leave->status === 'pending')
                                    @if($leave->user_id === auth()->id())
                                        <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-outline-warning" title="编辑">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @else
                                        <!-- HR可以快速审批 -->
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="quickApprove({{ $leave->id }})" title="快速批准">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="quickReject({{ $leave->id }})" title="快速拒绝">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">暂无请假记录</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
        <div class="mt-3">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>
</div>

<!-- 快速审批模态框 -->
<div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="quickApproveForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="approve">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> 批准请假申请</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>确定要批准该请假申请吗？</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">确认批准</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 快速拒绝模态框 -->
<div class="modal fade" id="quickRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="quickRejectForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle"></i> 拒绝请假申请</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">拒绝原因 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required placeholder="请输入拒绝原因..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">确认拒绝</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function quickApprove(leaveId) {
        const form = document.getElementById('quickApproveForm');
        form.action = `/leaves/${leaveId}/approve`;
        
        const modal = new bootstrap.Modal(document.getElementById('quickApproveModal'));
        modal.show();
    }

    function quickReject(leaveId) {
        const form = document.getElementById('quickRejectForm');
        form.action = `/leaves/${leaveId}/approve`;
        
        // 清空之前的输入
        document.getElementById('rejection_reason').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('quickRejectModal'));
        modal.show();
    }
</script>
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
