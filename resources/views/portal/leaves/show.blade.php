@extends('portal.layout')

@section('title', '请假详情')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-eye"></i> 请假详情 #{{ $leave->id }}</h4>
                <a href="{{ route('portal.leaves.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回
                </a>
            </div>
            <div class="card-body">
                <!-- 基本信息 -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">请假类型</small>
                                <h5 class="mb-0">
                                    <span class="badge bg-info fs-6">{{ $leave->leave_type_text }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">审批状态</small>
                                <h5 class="mb-0">
                                    @if($leave->status === 'pending')
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="bi bi-clock"></i> 待审批
                                        </span>
                                    @elseif($leave->status === 'approved')
                                        <span class="badge bg-success fs-6">
                                            <i class="bi bi-check-circle"></i> 已批准
                                        </span>
                                    @else
                                        <span class="badge bg-danger fs-6">
                                            <i class="bi bi-x-circle"></i> 已拒绝
                                        </span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 日期信息 -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <small class="text-muted d-block mb-2">开始日期</small>
                                <h4 class="text-primary mb-0">
                                    <i class="bi bi-calendar-event"></i>
                                    {{ $leave->start_date->format('m月d日') }}
                                </h4>
                                <small class="text-muted">{{ $leave->start_date->format('Y年') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <small class="text-muted d-block mb-2">结束日期</small>
                                <h4 class="text-success mb-0">
                                    <i class="bi bi-calendar-check"></i>
                                    {{ $leave->end_date->format('m月d日') }}
                                </h4>
                                <small class="text-muted">{{ $leave->end_date->format('Y年') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <small class="text-muted d-block mb-2">请假天数</small>
                                <h4 class="text-warning mb-0">
                                    <i class="bi bi-calendar-range"></i>
                                    {{ $leave->days }} 天
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 请假原因 -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-file-text"></i> 请假原因
                    </label>
                    <div class="border rounded p-4 bg-light">
                        <p class="mb-0">{{ $leave->reason }}</p>
                    </div>
                </div>

                <hr>

                <!-- 审批信息 -->
                @if($leave->status !== 'pending')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1">审批人</small>
                        <p class="mb-0">
                            <i class="bi bi-person-check text-success"></i>
                            {{ $leave->approver->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1">审批时间</small>
                        <p class="mb-0">
                            <i class="bi bi-clock-history"></i>
                            {{ $leave->approved_at ? $leave->approved_at->format('Y-m-d H:i:s') : 'N/A' }}
                        </p>
                    </div>
                </div>
                @endif

                @if($leave->status === 'rejected' && $leave->rejection_reason)
                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-triangle"></i> 拒绝原因</h6>
                    <p class="mb-0">{{ $leave->rejection_reason }}</p>
                </div>
                @endif

                <!-- 证据文件 -->
                @if($leave->evidence)
                <div class="mb-4">
                    <small class="text-muted d-block mb-2">证据文件</small>
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="bi bi-paperclip text-info"></i>
                                    <strong>{{ basename($leave->evidence) }}</strong>
                                </div>
                                <a href="{{ Storage::url($leave->evidence) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> 下载/查看
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">申请时间</small>
                    <p class="mb-0">
                        <i class="bi bi-calendar"></i>
                        {{ $leave->created_at->format('Y年m月d日 H:i:s') }}
                    </p>
                </div>

                @if($leave->status === 'pending')
                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('portal.leaves.edit', $leave) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> 编辑
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

