@extends('portal.layout')

@section('title', '编辑请假申请')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-pencil"></i> 编辑请假申请</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('portal.leaves.update', $leave) }}" method="POST" id="leaveForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="leave_type" class="form-label fw-bold">
                            <i class="bi bi-tag"></i> 请假类型 <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg @error('leave_type') is-invalid @enderror" id="leave_type" name="leave_type" required>
                            <option value="">请选择请假类型</option>
                            <option value="annual" {{ old('leave_type', $leave->leave_type) === 'annual' ? 'selected' : '' }}>📅 年假</option>
                            <option value="sick" {{ old('leave_type', $leave->leave_type) === 'sick' ? 'selected' : '' }}>🏥 病假</option>
                            <option value="personal" {{ old('leave_type', $leave->leave_type) === 'personal' ? 'selected' : '' }}>📝 事假</option>
                            <option value="maternity" {{ old('leave_type', $leave->leave_type) === 'maternity' ? 'selected' : '' }}>👶 产假</option>
                            <option value="paternity" {{ old('leave_type', $leave->leave_type) === 'paternity' ? 'selected' : '' }}>👨 陪产假</option>
                            <option value="other" {{ old('leave_type', $leave->leave_type) === 'other' ? 'selected' : '' }}>📌 其他</option>
                        </select>
                        @error('leave_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-bold">
                                <i class="bi bi-calendar-event"></i> 开始日期 <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" 
                                   value="{{ old('start_date', $leave->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-bold">
                                <i class="bi bi-calendar-check"></i> 结束日期 <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" 
                                   value="{{ old('end_date', $leave->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="daysInfo"></small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="form-label fw-bold">
                            <i class="bi bi-file-text"></i> 请假原因 <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" 
                                  id="reason" name="reason" rows="5" 
                                  placeholder="请详细说明请假原因..." required>{{ old('reason', $leave->reason) }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="evidence" class="form-label fw-bold">
                            <i class="bi bi-paperclip"></i> 证据文件（可选）
                        </label>
                        @if($leave->evidence)
                        <div class="mb-2">
                            <div class="alert alert-info">
                                <i class="bi bi-file-earmark-check"></i> 
                                当前文件：<a href="{{ Storage::url($leave->evidence) }}" target="_blank" class="text-decoration-none">
                                    {{ basename($leave->evidence) }}
                                </a>
                            </div>
                        </div>
                        @endif
                        <input type="file" class="form-control @error('evidence') is-invalid @enderror" 
                               id="evidence" name="evidence" 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        @error('evidence')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> 支持格式：JPG、PNG、PDF、DOC、DOCX，最大10MB。上传新文件将替换现有文件。
                        </small>
                        <div id="filePreview" class="mt-2"></div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('portal.leaves.index') }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> 返回列表
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> 更新申请
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').min = today;
    document.getElementById('end_date').min = today;

    function calculateDays() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            const daysInfo = document.getElementById('daysInfo');
            if (diffDays > 0) {
                daysInfo.textContent = `共 ${diffDays} 天`;
                daysInfo.className = 'text-success';
            }
        }
    }

    document.getElementById('start_date').addEventListener('change', function() {
        document.getElementById('end_date').min = this.value;
        calculateDays();
    });

    document.getElementById('end_date').addEventListener('change', calculateDays);

    // 文件预览
    document.getElementById('evidence').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('filePreview');
        
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileName = file.name;
            
            preview.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-file-earmark"></i> 
                    <strong>新文件：${fileName}</strong> (${fileSize} MB)
                    <br><small>将替换现有文件</small>
                </div>
            `;
        } else {
            preview.innerHTML = '';
        }
    });
</script>
@endsection

