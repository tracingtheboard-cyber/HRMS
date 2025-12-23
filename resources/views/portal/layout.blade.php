<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '员工门户') - HR管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    @yield('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            @auth
            <!-- 角色切换（测试用）- 左上角 -->
            <div class="me-3">
                <form action="{{ route('role.switch') }}" method="POST" id="roleSwitchForm" class="d-inline">
                    @csrf
                    <input type="hidden" name="role" id="roleInput" value="">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-badge"></i> 
                            <span id="currentRoleText">{{ auth()->user()->role_text ?? '员工' }}</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="#" class="dropdown-item {{ auth()->user()->role === 'employee' ? 'active' : '' }}" data-role="employee">
                                    <i class="bi bi-person"></i> 员工
                                    @if(auth()->user()->role === 'employee')
                                        <i class="bi bi-check-circle float-end"></i>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item {{ auth()->user()->role === 'hr' ? 'active' : '' }}" data-role="hr">
                                    <i class="bi bi-briefcase"></i> HR
                                    @if(auth()->user()->role === 'hr')
                                        <i class="bi bi-check-circle float-end"></i>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item {{ auth()->user()->role === 'admin' ? 'active' : '' }}" data-role="admin">
                                    <i class="bi bi-shield-check"></i> 管理员
                                    @if(auth()->user()->role === 'admin')
                                        <i class="bi bi-check-circle float-end"></i>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                </form>
            </div>
            @endauth
            <a class="navbar-brand" href="{{ route('portal.dashboard') }}">
                <i class="bi bi-person-circle"></i> 员工门户
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('portal.dashboard') }}">
                            <i class="bi bi-house"></i> 首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('portal.leaves.index') }}">
                            <i class="bi bi-calendar-check"></i> 我的请假
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('portal.payrolls.index') }}">
                            <i class="bi bi-cash-stack"></i> 我的工资单
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('portal.dashboard') }}">
                                <i class="bi bi-person"></i> 我的信息
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('portal.logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> 退出登录
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 角色切换功能
        document.addEventListener('DOMContentLoaded', function() {
            const roleSwitchForm = document.getElementById('roleSwitchForm');
            if (roleSwitchForm) {
                const roleLinks = roleSwitchForm.querySelectorAll('.dropdown-item[data-role]');
                roleLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const role = this.getAttribute('data-role');
                        document.getElementById('roleInput').value = role;
                        roleSwitchForm.submit();
                    });
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

