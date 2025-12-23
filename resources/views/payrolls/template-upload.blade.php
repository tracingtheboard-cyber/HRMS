@extends('layouts.app')

@section('title', 'Word模板管理')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-word"></i> Word工资单模板管理</h2>
        <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> 返回薪资管理
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">上传Word模板</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('payrolls.template-upload.post') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="template" class="form-label">选择Word模板文件 (.docx)</label>
                            <input type="file" class="form-control @error('template') is-invalid @enderror" 
                                   id="template" name="template" accept=".docx" required>
                            @error('template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 文件大小限制：最大10MB，仅支持 .docx 格式
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="bi bi-lightbulb"></i> 模板使用说明：</h6>
                            <ul class="mb-0">
                                <li>在Word模板中使用 <code>${变量名}</code> 作为占位符</li>
                                <li>例如：<code>${EMPLOYEE_NAME}</code>、<code>${TOTAL_EARNINGS_SGD}</code></li>
                                <li>上传后会替换旧的模板文件</li>
                                <li>所有可用的变量列表请查看 <a href="{{ asset('WORD_TEMPLATE_GUIDE.md') }}" target="_blank">使用指南</a></li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> 上传模板
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">当前模板状态</h5>
                </div>
                <div class="card-body">
                    @if($templateExists)
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <strong>模板已上传</strong>
                        </div>
                        
                        <div class="mb-3">
                            <strong>文件信息：</strong>
                            <ul class="list-unstyled mt-2">
                                <li><i class="bi bi-file-earmark-word"></i> 文件名：payslip_template.docx</li>
                                <li><i class="bi bi-hdd"></i> 文件大小：{{ number_format($templateInfo['size'] / 1024, 2) }} KB</li>
                                <li><i class="bi bi-clock"></i> 上传时间：{{ date('Y-m-d H:i:s', $templateInfo['modified']) }}</li>
                            </ul>
                        </div>

                        <form action="{{ route('payrolls.template-delete') }}" method="POST" 
                              onsubmit="return confirm('确定要删除当前模板吗？删除后系统将使用HTML方式生成PDF。');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-trash"></i> 删除模板
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> <strong>未上传模板</strong>
                        </div>
                        <p class="text-muted">
                            当前未上传Word模板，系统将使用HTML方式生成PDF。上传Word模板后，系统将优先使用模板生成PDF。
                        </p>
                    @endif

                    <hr>

                    <div class="alert alert-secondary">
                        <h6><i class="bi bi-question-circle"></i> PDF转换工具：</h6>
                        <p class="small mb-2">系统按优先级尝试以下方式将Word转换为PDF：</p>
                        <ol class="small mb-0">
                            <li><strong>CloudConvert API</strong>（云端服务，需配置API密钥）</li>
                            <li><strong>Microsoft Office</strong>（Windows，如已安装）</li>
                            <li><strong>LibreOffice</strong>（免费，跨平台）</li>
                            <li><strong>备用方案</strong>：HTML/dompdf（无需安装）</li>
                        </ol>
                        <p class="small mt-2 mb-0">
                            <i class="bi bi-info-circle"></i> 推荐使用CloudConvert API，无需安装任何软件。
                            <a href="https://cloudconvert.com/api/v2" target="_blank">了解更多</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">常用模板变量</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>员工信息</h6>
                            <ul class="list-unstyled small">
                                <li><code>${EMPLOYEE_NAME}</code> - 员工姓名</li>
                                <li><code>${EMPLOYEE_POSITION}</code> - 职位</li>
                                <li><code>${EMPLOYEE_NRIC_FIN}</code> - NRIC/FIN</li>
                                <li><code>${COMMENCEMENT_DATE}</code> - 入职日期</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>收入项</h6>
                            <ul class="list-unstyled small">
                                <li><code>${EARNINGS_BASIC_SALARY_SGD}</code> - 基本工资</li>
                                <li><code>${EARNINGS_ALLOWANCE_SGD}</code> - 津贴</li>
                                <li><code>${TOTAL_EARNINGS_SGD}</code> - 总收入</li>
                                <li><code>${NET_PAY_SGD}</code> - 净工资</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>其他</h6>
                            <ul class="list-unstyled small">
                                <li><code>${COMPANY_NAME}</code> - 公司名称</li>
                                <li><code>${UEN}</code> - UEN号</li>
                                <li><code>${BANK_NAME}</code> - 银行名称</li>
                                <li><code>${PREPARED_BY}</code> - 准备人</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ asset('WORD_TEMPLATE_GUIDE.md') }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-book"></i> 查看完整变量列表和使用指南
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

