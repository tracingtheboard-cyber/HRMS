@extends('layouts.app')

@section('title', '添加员工')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> 添加新员工</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.store') }}" method="POST" id="employeeForm">
                    @csrf

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 请填写员工的基本信息，创建后员工可以使用邮箱和密码登录员工门户。
                    </div>

                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">
                            <i class="bi bi-person"></i> 姓名 <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="请输入员工姓名" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">
                            <i class="bi bi-envelope"></i> 邮箱 <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="example@company.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">邮箱将作为员工登录账号，必须唯一</small>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">
                            <i class="bi bi-lock"></i> 密码 <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   id="password" name="password" 
                                   placeholder="请输入密码" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="bi bi-shield-check"></i> 密码长度至少8位，建议包含字母和数字
                        </small>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-bold">
                            <i class="bi bi-lock-fill"></i> 确认密码 <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="请再次输入密码" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                <i class="bi bi-eye" id="eyeIconConfirmation"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">请再次输入密码以确认</small>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> 返回列表
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> 创建员工
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 提示卡片 -->
        <div class="card mt-4 border-info">
            <div class="card-body">
                <h6 class="text-info"><i class="bi bi-lightbulb"></i> 提示</h6>
                <ul class="mb-0 small">
                    <li>创建员工后，员工会立即关联到当前选择的公司</li>
                    <li>员工可以使用邮箱和密码登录 <strong>员工门户</strong> 查看自己的请假记录和工资单</li>
                    <li>密码创建后可在"编辑员工"页面进行修改</li>
                    <li>建议首次登录后提醒员工修改密码</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 密码显示/隐藏切换
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    });

    document.getElementById('togglePasswordConfirmation').addEventListener('click', function() {
        const passwordInput = document.getElementById('password_confirmation');
        const eyeIcon = document.getElementById('eyeIconConfirmation');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    });

    // 密码强度检查
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const confirmation = document.getElementById('password_confirmation');
        
        if (password.length > 0 && confirmation.value.length > 0) {
            if (password !== confirmation.value) {
                confirmation.setCustomValidity('密码不匹配');
            } else {
                confirmation.setCustomValidity('');
            }
        }
    });

    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmation = this.value;
        
        if (confirmation.length > 0) {
            if (password !== confirmation) {
                this.setCustomValidity('密码不匹配');
            } else {
                this.setCustomValidity('');
            }
        } else {
            this.setCustomValidity('');
        }
    });

    // 表单提交前的验证
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('两次输入的密码不一致，请检查后重新输入！');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('密码长度至少需要8位！');
            return false;
        }
    });
</script>
@endsection

@section('styles')
<style>
    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    .card-header.bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    }
    .input-group .btn {
        border-left: 0;
    }
    .input-group .form-control:focus + .btn {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endsection
