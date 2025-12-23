@extends('layouts.app')

@section('title', '薪资详情')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-eye"></i> 薪资详情 - {{ $payroll->year }}年{{ $payroll->month }}月</h4>
            <div class="btn-group">
                <a href="{{ route('payrolls.download', $payroll) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-download"></i> 下载工资单PDF
                </a>
                @if($payroll->status === 'pending')
                <form action="{{ route('payrolls.mark-as-paid', $payroll) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('确定标记为已发放吗？')">
                        <i class="bi bi-check-circle"></i> 标记为已发放
                    </button>
                </form>
                @endif
                <a href="{{ route('payrolls.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回
                </a>
            </div>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: center; width: 100%;">
                <div class="sheet">
                    <div class="page">

                        <!-- ===== TITLE ===== -->
                        <div class="title">Payslip for {{ strtoupper(\Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('M Y')) }}</div>
                        
                        <!-- 公司抬头（左上 Logo） -->
                        @if($payroll->user->company && $payroll->user->company->code)
                        <div class="company-header">
                            <div style="text-align:center; font-size:11px; margin-top:2px;">UEN: {{ $payroll->user->company->code }}</div>
                        </div>
                        @endif

                        <!-- ===== INFO BOXES ===== -->
                        <div class="info-row">
                            <div class="info-box left-box">
                                <div class="company-name">{{ $payroll->user->company->name ?? 'CACTUS GROUP (PTE. LTD.)' }}</div>
                                <div>Position: {{ $payroll->user->position ?? '-' }}</div>
                                <div>Name: {{ $payroll->user->name }}</div>
                                <div>NRIC / FIN: {{ $payroll->user->nric_fin ?? '-' }}</div>
                            </div>

                            <div class="info-box right-box">
                                <div class="page-no">Page 1 of 1</div>
                                <div>Basic Salary: {{ number_format($payroll->base_salary, 2, '.', ',') }}</div>
                                <div>Commencement Date: {{ $payroll->user->commencement_date ? $payroll->user->commencement_date->format('n/j/Y') : '-' }}</div>
                                <div>Last Date: {{ $payroll->user->last_date ? $payroll->user->last_date->format('n/j/Y') : '' }}</div>
                            </div>
                        </div>

                        <!-- 表头条（SGD / YTD） -->
                        <div class="amount-head">
                            <div class="ah-left"></div>
                            <div class="ah-mid">SGD</div>
                            <div class="ah-right">YTD(SGD)</div>
                        </div>

                        <div class="section">EARNINGS:</div>

                        <!-- ===== EARNINGS ===== -->
                        <table>
                            <tr><td class="l item-indent">Basic Salary</td><td class="m">{{ number_format($payroll->base_salary, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->base_salary, 2, '.', ',') }}</td></tr>
                            <tr><td class="l item-indent">Allowance</td><td class="m">{{ number_format($payroll->allowances, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->allowances, 2, '.', ',') }}</td></tr>
                            <tr><td class="l item-indent">Over Time / Other</td><td class="m">{{ number_format($payroll->overtime_other, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->overtime_other, 2, '.', ',') }}</td></tr>
                            <tr><td class="l item-indent">Bonus</td><td class="m">{{ number_format($payroll->bonus, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->bonus, 2, '.', ',') }}</td></tr>
                            <tr><td class="l item-indent">Unutilised pay leave</td><td class="m">{{ number_format($payroll->unutilised_pay_leave, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->unutilised_pay_leave, 2, '.', ',') }}</td></tr>
                            <tr><td class="l item-indent">Unpaid leave</td><td class="m">( {{ number_format($payroll->unpaid_leave, 2, '.', ',') }})</td><td class="r">( {{ number_format($payroll->unpaid_leave, 2, '.', ',') }})</td></tr>
                            <tr><td class="l"><b>Total Earnings</b></td><td class="m"><b>{{ number_format($payroll->total_earnings, 2, '.', ',') }}</b></td><td class="r"><b>{{ number_format($payroll->total_earnings, 2, '.', ',') }}</b></td></tr>
                        </table>

                        <!-- ===== DEDUCTIONS ===== -->
                        <div class="section">DEDUCTIONS:</div>
                        <table>
                            <tr><td class="l item-indent">Employee CPF</td><td class="m">( {{ number_format($payroll->employee_cpf, 2, '.', ',') }})</td><td class="r">( {{ number_format($payroll->employee_cpf, 2, '.', ',') }})</td></tr>
                            <tr><td class="l item-indent">CDAC/ MBMF/</td><td class="m"></td><td class="r"></td></tr>
                            <tr><td class="l item-indent">SINDA Fund</td><td class="m">( {{ number_format($payroll->cdac_mbmf_sinda, 2, '.', ',') }})</td><td class="r">( {{ number_format($payroll->cdac_mbmf_sinda, 2, '.', ',') }})</td></tr>
                            <tr><td class="l"><b>Total Deduction</b></td><td class="m"><b>( {{ number_format($payroll->total_deduction, 2, '.', ',') }})</b></td><td class="r"><b>( {{ number_format($payroll->total_deduction, 2, '.', ',') }})</b></td></tr>
                            <tr><td class="l"><b>Net Pay</b></td><td class="m"><b>{{ number_format($payroll->net_pay, 2, '.', ',') }}</b></td><td class="r"><b>{{ number_format($payroll->net_pay, 2, '.', ',') }}</b></td></tr>
                        </table>

                        <!-- ===== OTHER DEDUCTION ===== -->
                        <div class="section">OTHER DEDUCTION:</div>
                        <table>
                            <tr><td class="l item-indent">Advance / Loan</td><td class="m">(- {{ number_format(abs($payroll->advance_loan), 2, '.', ',') }})</td><td class="r">(- {{ number_format(abs($payroll->advance_loan), 2, '.', ',') }})</td></tr>
                            <tr><td class="l"><b>Net Pay after other deduction</b></td><td class="m"><b>{{ number_format($payroll->net_pay_after_other_deduction, 2, '.', ',') }}</b></td><td class="r"><b>{{ number_format($payroll->net_pay_after_other_deduction, 2, '.', ',') }}</b></td></tr>
                        </table>

                        <!-- ===== EMPLOYER CONTRIBUTIONS ===== -->
                        <div class="section">EMPLOYER CONTRIBUTIONS:</div>
                        <table style="margin-top:4px;">
                            <tr><td class="l item-indent">Employer CPF</td><td class="m">{{ number_format($payroll->employer_cpf, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->employer_cpf, 2, '.', ',') }}</td></tr>
                            <tr><td class="l item-indent">SDL</td><td class="m">{{ number_format($payroll->sdl, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->sdl, 2, '.', ',') }}</td></tr>
                        </table>

                        <!-- ===== BANK ===== -->
                        <div class="footer">
                            To Be Credited to<br>
                            Bank Name: {{ $payroll->bank_name ?? '' }} &nbsp; &nbsp; &nbsp;  Bank Code:<br>
                            Account Number: {{ $payroll->bank_account_number ?? '' }} &nbsp;&nbsp;&nbsp;&nbsp; Branch Code:<br>
                            On {{ $payroll->credit_date ? $payroll->credit_date->format('n/j/Y') : '' }}
                        </div>

                        <!-- ===== SIGN ===== -->
                        <div class="sign">
                            <div>Prepared by __{{ $payroll->preparer->name ?? '' }}_____</div>
                            <div>Approved by __{{ $payroll->approver->name ?? '' }}_____</div>
                        </div>

                        <!-- ===== PAGE FOOTER ===== -->
                        <div class="company-footer">
                            <div>Page 1 of 1</div>
                            <div></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* ===== BASE ===== */
body{
  margin:0;
  font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
  color:#000;
}
.page{
  padding:10mm;
  font-size:12px;
  line-height:1.3;
}

/* ===== TITLE ===== */
.title{
  text-align:center;
  font-size:15px;
  font-weight:700;
  margin-bottom:2mm;
}
.right-head{
  text-align:right;
  font-weight:700;
  font-size:12px;
  margin-top:-12px;
}

/* ===== INFO BOXES (比例方案) ===== */
.info-row{
  display:flex;
  width:100%;
  gap:3%;
  margin:4mm 0 6mm 0;
}

.info-box{
  border:1px solid #000;
  border-radius:10px;
  padding:6px 10px;
  box-sizing:border-box;
  font-size:12px;
  line-height:1.35;
}

.left-box{ flex:0 0 30%; }
.right-box{ flex:0 0 30%; margin-left: auto;text-align:right; }

.company-name{ font-weight:700; }
.page-no{ font-weight:700; margin-bottom:4px; }

/* ===== SECTIONS ===== */
.section{
  font-weight:700;
  margin-top:8px;
}

/* ===== TABLE ===== */
table{
  width:100%;
  border-collapse:collapse;
  font-size:12px;
}
td{
  padding:1px 0;
}
.l{ width:64%; }
.m{ width:18%; text-align:right; }
.r{ width:18%; text-align:right; }

/* 子项目右缩进（锁定 8mm） */
.item-indent{
  padding-left:8mm;
}

/* ===== FOOTER ===== */
.footer{
  margin-top:6px;
}
.sign{
  display:flex;
  justify-content:space-between;
  margin-top:8px;
  margin-right: 20%;
}
.company-footer{
  display:flex;
  justify-content:space-between;
  margin-top:6px;
  font-weight:700;
}


/* 金额表头条 */
.amount-head{
  display: grid;
  grid-template-columns: 64% 18% 18%; /* 和表格三列一致 */
  border: 1px solid #000;
  border-radius: 10px;
  padding: 4px 0;
  margin: 6px 0 4px 0;
  font-size: 12px;
  font-weight: 700;
}

/* 左空区（项目列） */
.amount-head .ah-left{
  padding-left: 8mm; /* 对齐子项目缩进 */
}

/* 中、右标题 */
.amount-head .ah-mid,
.amount-head .ah-right{
  text-align: right;
  padding-right: 4mm;
}
.sheet{
  width: 210mm;
  max-width: 210mm;
  min-width: 210mm;
}

/* 确保卡片体内容居中 */
.card-body {
    padding: 20px;
    display: flex;
    justify-content: center;
    overflow-x: auto;
}
</style>
@endsection
