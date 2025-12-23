<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'HR管理系统')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
            <a class="navbar-brand" href="{{ route('dashboard.index') }}">HR管理系统</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('companies.index') }}">
                            <i class="bi bi-building"></i> 公司管理
                        </a>
                    </li>
                    @auth
                    @php
                        $user = auth()->user();
                        $currentCompany = $user->currentCompany ?? $user->company;
                        $allCompanies = $user->companies->merge(collect([$user->company])->filter());
                    @endphp
                    @if($currentCompany)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="companyDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-building"></i> {{ $currentCompany->name }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="companyDropdown">
                            @foreach($allCompanies->unique('id') as $company)
                                <li>
                                    @if($company->id == $currentCompany->id)
                                        <a class="dropdown-item active" href="#">
                                            <i class="bi bi-check-circle"></i> {{ $company->name }}
                                        </a>
                                    @else
                                        <form action="{{ route('companies.switch', $company) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                {{ $company->name }}
                                            </button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employees.index') }}">
                            <i class="bi bi-people"></i> 员工管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('leaves.index') }}">
                            <i class="bi bi-calendar-check"></i> 请假管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('payrolls.index') }}">
                            <i class="bi bi-cash-stack"></i> 薪资管理
                        </a>
                    </li>
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

