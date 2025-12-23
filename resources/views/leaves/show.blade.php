@extends('layouts.app')

@section('title', '请假详情')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-eye"></i> 请假详情 #{{ $leave->id }}</h4>
                <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回
                </a>
            </div>
            <div class="card-body">
                <!-- 基本信息 -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">员工姓名</small>
                                <h5 class="mb-0">
                                    <i class="bi bi-person-circle text-primary"></i> 
                                    {{ $leave->user->name }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">请假类型</small>
                                <h5 class="mb-0">
                                    <span class="badge bg-info">{{ $leave->leave_type_text }}</span>
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

                <!-- 状态 -->
                <div class="mb-4">
                    <small class="text-muted d-block mb-2">审批状态</small>
                    @if($leave->status === 'pending')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="bi bi-clock"></i> 待审批
                        </span>
                    @elseif($leave->status === 'approved')
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle"></i> 已批准
                        </span>
                    @else
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-x-circle"></i> 已拒绝
                        </span>
                    @endif
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
                <div class="card {{ $leave->status === 'approved' ? 'border-success' : 'border-danger' }} mb-4">
                    <div class="card-header bg-{{ $leave->status === 'approved' ? 'success' : 'danger' }} bg-opacity-10">
                        <h6 class="mb-0">
                            <i class="bi bi-{{ $leave->status === 'approved' ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                            审批信息
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block mb-1">审批人</small>
                                <p class="mb-0 fs-5">
                                    <i class="bi bi-person-check text-{{ $leave->status === 'approved' ? 'success' : 'danger' }}"></i>
                                    <strong>{{ $leave->approver->name ?? 'N/A' }}</strong>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block mb-1">审批时间</small>
                                <p class="mb-0 fs-5">
                                    <i class="bi bi-clock-history"></i>
                                    <strong>{{ $leave->approved_at ? $leave->approved_at->format('Y年m月d日 H:i:s') : 'N/A' }}</strong>
                                </p>
                            </div>
                        </div>
                        @if($leave->status === 'rejected' && $leave->rejection_reason)
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">拒绝原因</small>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>{{ $leave->rejection_reason }}</strong>
                            </div>
                        </div>
                        @endif
                    </div>
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
                <!-- 审批操作区 -->
                @if($leave->user_id !== auth()->id())
                    <!-- HR审批按钮 -->
                    <div class="card border-warning">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h6 class="mb-0"><i class="bi bi-clipboard-check"></i> 审批操作</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-end gap-3">
                                <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('确定批准该请假申请吗？')">
                                        <i class="bi bi-check-circle"></i> 批准申请
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle"></i> 拒绝申请
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 拒绝模态框 -->
                    <div class="modal fade" id="rejectModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('leaves.approve', $leave) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bi bi-x-circle"></i> 拒绝请假申请</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i> 请填写拒绝原因，这将通知申请人。
                                        </div>
                                        <div class="mb-3">
                                            <label for="rejection_reason" class="form-label fw-bold">拒绝原因 <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="请输入详细的拒绝原因..."></textarea>
                                            <small class="form-text text-muted">拒绝原因将显示给申请人查看</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-x-circle"></i> 确认拒绝
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- 申请人自己的操作 -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 您的请假申请正在等待审批中，您可以继续编辑或删除此申请。
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> 编辑申请
                        </a>
                    </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

