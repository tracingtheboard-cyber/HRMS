<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Payslip for {{ strtoupper(\Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('M Y')) }}</title>

<style>
/* ===== A4 ===== */
@page { size:A4; margin:12mm; }
@media print{
  body{ background:#fff; }
}

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
  width: 100%;
  margin: 0 auto;
}


</style>
</head>

<body>
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
    <tr><td class="l item-indent">Unpaid leave</td><td class="m">{{ number_format($payroll->unpaid_leave, 2, '.', ',') }}</td><td class="r">{{ number_format($payroll->unpaid_leave, 2, '.', ',') }}</td></tr>
    <tr><td class="l item-indent"><b>Total Earnings</b></td><td class="m"><b>{{ number_format($payroll->total_earnings, 2, '.', ',') }}</b></td><td class="r"><b>{{ number_format($payroll->total_earnings, 2, '.', ',') }}</b></td></tr>
  </table>

  <!-- ===== DEDUCTIONS ===== -->
  <div class="section">DEDUCTIONS:</div>
  <table>
    <tr><td class="l item-indent">Employee CPF</td><td class="m">( {{ number_format($payroll->employee_cpf, 2, '.', ',') }})</td><td class="r">( {{ number_format($payroll->employee_cpf, 2, '.', ',') }})</td></tr>
    <tr><td class="l item-indent">CDAC/ MBMF/</td><td class="m"></td><td class="r"></td></tr>
    <tr><td class="l item-indent">SINDA Fund</td><td class="m">( {{ number_format($payroll->cdac_mbmf_sinda, 2, '.', ',') }})</td><td class="r">( {{ number_format($payroll->cdac_mbmf_sinda, 2, '.', ',') }})</td></tr>
    <tr><td class="l item-indent"><b>Total Deduction</b></td><td class="m"><b>( {{ number_format($payroll->total_deduction, 2, '.', ',') }})</b></td><td class="r"><b>( {{ number_format($payroll->total_deduction, 2, '.', ',') }})</b></td></tr>
    <tr><td class="l item-indent"><b>Net Pay</b></td><td class="m"><b>{{ number_format($payroll->net_pay, 2, '.', ',') }}</b></td><td class="r"><b>{{ number_format($payroll->net_pay, 2, '.', ',') }}</b></td></tr>
  </table>

  <!-- ===== OTHER DEDUCTION ===== -->
  <div class="section">OTHER DEDUCTION:</div>
  <table>
    <tr><td class="l item-indent">Advance / Loan</td><td class="m">(- {{ number_format(abs($payroll->advance_loan), 2, '.', ',') }})</td><td class="r">(- {{ number_format(abs($payroll->advance_loan), 2, '.', ',') }})</td></tr>
    <tr><td class="l item-indent"><b>Net Pay after other deduction</b></td><td class="m"><b>{{ number_format($payroll->net_pay_after_other_deduction, 2, '.', ',') }}</b></td><td class="r"><b>{{ number_format($payroll->net_pay_after_other_deduction, 2, '.', ',') }}</b></td></tr>
  </table>

  <!-- ===== EMPLOYER ===== -->
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
  </div>

</div>
</div>
</body>
</html>
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
  width: 100%;
  margin: 0 auto;
}


</style>
</head>

<body>
<div class="sheet">
<div class="page">

  <!-- ===== HEADER ===== -->
  <table style="width:100%; margin-bottom: 20px;">
    <tr>
      <td style="text-align: center;">
        @if($payroll->user->company && $payroll->user->company->code)
          <div style="font-size: 12px;">UEN: {{ $payroll->user->company->code }}</div>
        @endif
        <div style="font-size: 14px; font-weight: bold; margin-top: 10px; text-transform: uppercase;">
          Payslip for {{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F Y') }}
        </div>
      </td>
    </tr>
  </table>

  <!-- ===== INFO BOXES ===== -->
  <table style="width:100%; border: 1px solid #000; padding: 10px; margin-bottom: 20px;">
    <tr>
      <td style="width: 50%; vertical-align: top;">
         <div style="margin-bottom: 4px;"><span style="font-weight: bold;">Name:</span> {{ $payroll->user->name }}</div>
         <div style="margin-bottom: 4px;"><span style="font-weight: bold;">Position:</span> {{ $payroll->user->position ?? '-' }}</div>
         <div><span style="font-weight: bold;">NRIC/FIN:</span> {{ $payroll->user->nric_fin ?? '-' }}</div>
      </td>
      <td style="width: 50%; vertical-align: top; text-align: right;">
         <div style="margin-bottom: 4px;"><span style="font-weight: bold;">Commencement Date:</span> {{ $payroll->user->commencement_date ? $payroll->user->commencement_date->format('d/m/Y') : '-' }}</div>
         <div><span style="font-weight: bold;">Pay Period:</span> {{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->startOfMonth()->format('d/m/Y') }} - {{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->endOfMonth()->format('d/m/Y') }}</div>
      </td>
    </tr>
  </table>

  <!-- ===== MAIN CONTENT (2 COLUMN TABLE) ===== -->
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <tr>
      <!-- LEFT COLUMN: EARNINGS -->
      <td style="width: 48%; vertical-align: top; border: 1px solid #000; padding: 0;">
        <div style="padding: 5px 10px; font-weight: bold; font-size: 12px; border-bottom: 1px solid #000;">EARNINGS</div>
        <table style="width: 100%; padding: 5px;">
            <tr>
                <td style="padding: 3px;">Basic Salary</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->base_salary, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">Allowances</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->allowances, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">Bonus</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->bonus, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">Overtime / Others</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->overtime_other, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">Unutilised Pay Leave</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->unutilised_pay_leave, 2) }}</td>
            </tr>
            @if($payroll->unpaid_leave > 0)
            <tr>
                <td style="padding: 3px;">Unpaid Leave</td>
                <td style="text-align: right; padding: 3px;">-{{ number_format($payroll->unpaid_leave, 2) }}</td>
            </tr>
            @endif
             <!-- Spacer to push total to bottom if needed, or just let it flow -->
             <tr><td colspan="2" style="height: 10px;"></td></tr>
            <tr style="font-weight: bold; border-top: 1px solid #ccc;">
                <td style="padding: 5px 3px;">Total Earnings</td>
                <td style="text-align: right; padding: 5px 3px;">{{ number_format($payroll->total_earnings, 2) }}</td>
            </tr>
        </table>
      </td>

      <!-- SPACER COLUMN -->
      <td style="width: 4%;"></td>

      <!-- RIGHT COLUMN: DEDUCTIONS -->
      <td style="width: 48%; vertical-align: top; border: 1px solid #000; padding: 0;">
        <div style="padding: 5px 10px; font-weight: bold; font-size: 12px; border-bottom: 1px solid #000;">DEDUCTIONS</div>
        <table style="width: 100%; padding: 5px;">
            <tr>
                <td style="padding: 3px;">Employee CPF</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->employee_cpf, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">CDAC / MBMF / SINDA</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->cdac_mbmf_sinda, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">Tax</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->tax, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">Other Deductions</td>
                <td style="text-align: right; padding: 3px;">{{ number_format($payroll->deductions, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px;">Advance / Loan</td>
                <td style="text-align: right; padding: 3px;">{{ number_format(abs($payroll->advance_loan), 2) }}</td>
            </tr>
            
            <tr><td colspan="2" style="height: 10px;"></td></tr>
            <tr style="font-weight: bold; border-top: 1px solid #ccc;">
                <td style="padding: 5px 3px;">Total Deductions</td>
                <td style="text-align: right; padding: 5px 3px;">{{ number_format($payroll->total_deduction + abs($payroll->advance_loan), 2) }}</td>
            </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- ===== NET PAY ===== -->
  <div style="border: 1px solid #000; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
    <table style="width: 100%;">
        <tr>
            <td style="font-size: 14px; font-weight: bold;">NET PAY</td>
            <td style="text-align: right; font-size: 18px; font-weight: bold;">SGD {{ number_format($payroll->net_pay_after_other_deduction, 2) }}</td>
        </tr>
    </table>
  </div>

  <!-- ===== EMPLOYER & BANK INFO ===== -->
  <table style="width: 100%; margin-bottom: 30px;">
    <tr>
        <td style="width: 48%; vertical-align: top; border: 1px solid #000; padding: 10px;">
            <div style="font-weight: bold; margin-bottom: 5px;">EMPLOYER CONTRIBUTIONS</div>
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td>Employer CPF:</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($payroll->employer_cpf, 2) }}</td>
                </tr>
                <tr>
                    <td>SDL:</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($payroll->sdl, 2) }}</td>
                </tr>
            </table>
        </td>
        <td style="width: 4%;"></td>
        <td style="width: 48%; vertical-align: top; border: 1px solid #000; padding: 10px;">
             <div style="font-weight: bold; margin-bottom: 5px;">PAYMENT DETAILS</div>
             <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td>Bank Name:</td>
                    <td style="text-align: right; font-weight: bold;">{{ $payroll->bank_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Account No:</td>
                    <td style="text-align: right; font-weight: bold;">{{ $payroll->bank_account_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Payment Date:</td>
                    <td style="text-align: right; font-weight: bold;">{{ $payroll->credit_date ? $payroll->credit_date->format('d/m/Y') : 'Pending' }}</td>
                </tr>
            </table>
        </td>
    </tr>
  </table>

  <!-- ===== SIGNATURES ===== -->
  <table style="width: 100%; margin-top: 40px;">
    <tr>
        <td style="width: 40%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
            Prepared by: {{ $payroll->preparer->name ?? 'HR Department' }}
        </td>
        <td style="width: 20%;"></td>
        <td style="width: 40%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
            Approved by: {{ $payroll->approver->name ?? 'Management' }}
        </td>
    </tr>
  </table>

</div>
</div>
</body>
</html>
