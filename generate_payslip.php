<?php
/**
 * CACTUS GROUP (PTE. LTD.) 工资单生成脚本
 * 使用 mPDF 库生成 1:1 还原的工资单 PDF
 */

require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

// 配置 mPDF (适配 v6.x)
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 9,
    'margin_footer' => 9,
    'default_font' => 'dejavusans',
    'tempDir' => sys_get_temp_dir(),
]);

// 员工数据数组
$employees = [
    [
        'name' => 'JIA SHIBO',
        'position' => 'Senior Project Manager',
        'nric' => 'S81833721',
        'basic_salary' => 2500.00,
        'commencement_date' => '2/1/2019',
        'last_date' => '',
        'earnings' => [
            'basic_salary' => 2500.00,
            'allowance' => 0.00,
            'overtime_other' => 0.00,
            'bonus' => 0.00,
            'unutilised_pay_leave' => 0.00,
            'unpaid_leave' => 0.00,
        ],
        'deductions' => [
            'employee_cpf' => 500.00,
            'cdac_mbmf_sinda' => 1.00,
        ],
        'other_deduction' => [
            'advance_loan' => 50.50,
        ],
        'employer_contributions' => [
            'employer_cpf' => 425.00,
            'sdl' => 6.25,
        ],
        'bank' => [
            'name' => 'DBS BANK LTD.',
            'account_number' => '207908967',
            'credit_date' => '1/25/2025',
            'bank_code' => '',
            'branch_code' => '',
        ],
        'prepared_by' => 'CHONG CHING SIONG',
        'approved_by' => 'FELIX ZHAO',
    ],
    // 可以添加更多员工数据
];

// 计算函数
function calculateTotalEarnings($earnings) {
    return $earnings['basic_salary'] + 
           $earnings['allowance'] + 
           $earnings['overtime_other'] + 
           $earnings['bonus'] + 
           $earnings['unutilised_pay_leave'] - 
           $earnings['unpaid_leave'];
}

function calculateTotalDeduction($deductions) {
    return $deductions['employee_cpf'] + $deductions['cdac_mbmf_sinda'];
}

function calculateNetPay($totalEarnings, $totalDeduction) {
    return $totalEarnings - $totalDeduction;
}

function formatAmount($amount, $isNegative = false) {
    if ($isNegative && $amount > 0) {
        return '(' . number_format($amount, 2, '.', ',') . ')';
    }
    return number_format($amount, 2, '.', ',');
}

function formatAdvanceLoan($amount) {
    if ($amount > 0) {
        return '(- ' . number_format($amount, 2, '.', ',') . ')';
    }
    return number_format($amount, 2, '.', ',');
}

// CSS 样式
$css = '
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.3;
        color: #000;
    }
    .header-top {
        text-align: right;
        font-size: 9pt;
        margin-bottom: 5px;
        padding-bottom: 0;
    }
    .header {
        text-align: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #000;
    }
    .header-title {
        font-size: 16pt;
        font-weight: bold;
        margin-bottom: 3px;
        letter-spacing: 0.5px;
    }
    .header-uen {
        font-size: 9pt;
        margin-top: 2px;
        margin-bottom: 3px;
    }
    .company-name {
        font-size: 12pt;
        font-weight: bold;
        margin-top: 4px;
    }
    .employee-info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        font-size: 9.5pt;
    }
    .employee-info-table td {
        padding: 2px 0;
        border: none;
        vertical-align: top;
    }
    .employee-info-label {
        font-weight: bold;
        width: 200px;
        padding-right: 10px;
    }
    .employee-info-value {
        padding-left: 0;
    }
    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        font-size: 9.5pt;
    }
    .main-table th,
    .main-table td {
        padding: 3px 6px;
        text-align: left;
        vertical-align: top;
        border: 1px solid #000;
    }
    .main-table th {
        background-color: transparent;
        font-weight: bold;
        text-align: center;
        border: 1px solid #000;
    }
    .main-table .label-col {
        width: 45%;
        font-weight: normal;
        text-align: left;
    }
    .main-table .sgd-col {
        width: 27.5%;
        text-align: right;
        font-family: "Courier New", monospace;
    }
    .main-table .ytd-col {
        width: 27.5%;
        text-align: right;
        font-family: "Courier New", monospace;
    }
    .main-table .section-label {
        font-weight: bold;
        padding-top: 6px;
        padding-bottom: 2px;
    }
    .main-table .total-row {
        border-top: 1px dotted #666;
        font-weight: bold;
        padding-top: 4px;
    }
    .main-table .total-row td {
        border-top: 1px dotted #666;
    }
    .main-table .section-divider {
        border-top: 1px dotted #666;
        padding-top: 4px;
    }
    .main-table .section-divider td {
        border-top: 1px dotted #666;
    }
    .bank-info-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        margin-bottom: 8px;
        font-size: 9.5pt;
    }
    .bank-info-table td {
        padding: 2px 0;
        border: none;
        vertical-align: top;
    }
    .bank-info-label {
        font-weight: bold;
        width: 130px;
        padding-right: 10px;
    }
    .bank-info-value {
        padding-left: 0;
    }
    .bank-info-title {
        font-weight: bold;
        margin-bottom: 3px;
    }
    .signature-section {
        margin-top: 35px;
        display: table;
        width: 100%;
    }
    .signature-box {
        display: table-cell;
        width: 50%;
        vertical-align: bottom;
    }
    .signature-line {
        border-top: 1px solid #000;
        margin-top: 65px;
        padding-top: 4px;
        font-size: 9pt;
        text-align: left;
        width: 200px;
    }
</style>
';

// 生成每位员工的工资单
foreach ($employees as $index => $employee) {
    if ($index > 0) {
        $mpdf->AddPage();
    }
    
    // 计算值
    $totalEarnings = calculateTotalEarnings($employee['earnings']);
    $totalDeduction = calculateTotalDeduction($employee['deductions']);
    $netPay = calculateNetPay($totalEarnings, $totalDeduction);
    $netPayAfterOtherDeduction = $netPay - $employee['other_deduction']['advance_loan'];
    
    // 月份名称（假设是 JAN 2025）
    $monthYear = 'JAN 2025';
    
    // HTML 内容
    $html = $css . '
    <div class="header-top">Page 1 of 1</div>
    
    <div class="header">
        <div class="header-title">Payslip for ' . $monthYear . '</div>
        <div class="header-uen">UEN: 201406160G</div>
        <div class="company-name">CACTUS GROUP (PTE. LTD.)</div>
    </div>
    
    <table class="employee-info-table">
        <tr>
            <td class="employee-info-label">Company Name:</td>
            <td class="employee-info-value">CACTUS GROUP (PTE. LTD.)</td>
        </tr>
        <tr>
            <td class="employee-info-label">Position:</td>
            <td class="employee-info-value">' . $employee['position'] . '</td>
        </tr>
        <tr>
            <td class="employee-info-label">Name:</td>
            <td class="employee-info-value">' . $employee['name'] . '</td>
        </tr>
        <tr>
            <td class="employee-info-label">NRIC / FIN:</td>
            <td class="employee-info-value">' . $employee['nric'] . '</td>
        </tr>
        <tr>
            <td class="employee-info-label">Basic Salary (Employment Detail):</td>
            <td class="employee-info-value">' . formatAmount($employee['basic_salary']) . '</td>
        </tr>
        <tr>
            <td class="employee-info-label">Commencement Date:</td>
            <td class="employee-info-value">' . $employee['commencement_date'] . '</td>
        </tr>
        <tr>
            <td class="employee-info-label">Last Date:</td>
            <td class="employee-info-value">' . $employee['last_date'] . '</td>
        </tr>
    </table>
    
    <table class="main-table">
        <thead>
            <tr>
                <th class="label-col"></th>
                <th class="sgd-col">SGD</th>
                <th class="ytd-col">YTD(SGD)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Earnings Section -->
            <tr>
                <td class="label-col section-label">EARNINGS:</td>
                <td class="sgd-col"></td>
                <td class="ytd-col"></td>
            </tr>
            <tr>
                <td class="label-col">Basic Salary</td>
                <td class="sgd-col">' . formatAmount($employee['earnings']['basic_salary']) . '</td>
                <td class="ytd-col">' . formatAmount($employee['earnings']['basic_salary']) . '</td>
            </tr>
            <tr>
                <td class="label-col">Allowance</td>
                <td class="sgd-col">' . formatAmount($employee['earnings']['allowance']) . '</td>
                <td class="ytd-col">' . formatAmount($employee['earnings']['allowance']) . '</td>
            </tr>
            <tr>
                <td class="label-col">Over Time / Other</td>
                <td class="sgd-col">' . formatAmount($employee['earnings']['overtime_other']) . '</td>
                <td class="ytd-col">' . formatAmount($employee['earnings']['overtime_other']) . '</td>
            </tr>
            <tr>
                <td class="label-col">Bonus</td>
                <td class="sgd-col">' . formatAmount($employee['earnings']['bonus']) . '</td>
                <td class="ytd-col">' . formatAmount($employee['earnings']['bonus']) . '</td>
            </tr>
            <tr>
                <td class="label-col">Unutilised pay leave</td>
                <td class="sgd-col">' . formatAmount($employee['earnings']['unutilised_pay_leave']) . '</td>
                <td class="ytd-col">' . formatAmount($employee['earnings']['unutilised_pay_leave']) . '</td>
            </tr>
            <tr>
                <td class="label-col">Unpaid leave</td>
                <td class="sgd-col">' . formatAmount($employee['earnings']['unpaid_leave'], true) . '</td>
                <td class="ytd-col">' . formatAmount($employee['earnings']['unpaid_leave'], true) . '</td>
            </tr>
            <tr class="total-row">
                <td class="label-col"><strong>Total Earnings</strong></td>
                <td class="sgd-col"><strong>' . formatAmount($totalEarnings) . '</strong></td>
                <td class="ytd-col"><strong>' . formatAmount($totalEarnings) . '</strong></td>
            </tr>
            
            <!-- Deductions Section -->
            <tr class="section-divider">
                <td class="label-col section-label">DEDUCTIONS:</td>
                <td class="sgd-col"></td>
                <td class="ytd-col"></td>
            </tr>
            <tr>
                <td class="label-col">Employee CPF</td>
                <td class="sgd-col">' . formatAmount($employee['deductions']['employee_cpf'], true) . '</td>
                <td class="ytd-col">' . formatAmount($employee['deductions']['employee_cpf'], true) . '</td>
            </tr>
            <tr>
                <td class="label-col">CDAC / MBMF / SINDA Fund</td>
                <td class="sgd-col">' . formatAmount($employee['deductions']['cdac_mbmf_sinda'], true) . '</td>
                <td class="ytd-col">' . formatAmount($employee['deductions']['cdac_mbmf_sinda'], true) . '</td>
            </tr>
            <tr class="total-row">
                <td class="label-col"><strong>Total Deduction</strong></td>
                <td class="sgd-col"><strong>' . formatAmount($totalDeduction, true) . '</strong></td>
                <td class="ytd-col"><strong>' . formatAmount($totalDeduction, true) . '</strong></td>
            </tr>
            
            <!-- Net Pay Section -->
            <tr class="section-divider">
                <td class="label-col section-label">NET PAY:</td>
                <td class="sgd-col"></td>
                <td class="ytd-col"></td>
            </tr>
            <tr>
                <td class="label-col">Net Pay</td>
                <td class="sgd-col">' . formatAmount($netPay) . '</td>
                <td class="ytd-col">' . formatAmount($netPay) . '</td>
            </tr>
            
            <!-- Other Deduction Section -->
            <tr class="section-divider">
                <td class="label-col section-label">OTHER DEDUCTION:</td>
                <td class="sgd-col"></td>
                <td class="ytd-col"></td>
            </tr>
            <tr>
                <td class="label-col">Advance / Loan</td>
                <td class="sgd-col">' . formatAdvanceLoan($employee['other_deduction']['advance_loan']) . '</td>
                <td class="ytd-col">' . formatAdvanceLoan($employee['other_deduction']['advance_loan']) . '</td>
            </tr>
            <tr>
                <td class="label-col">Net Pay after other deduction</td>
                <td class="sgd-col">' . formatAmount($netPayAfterOtherDeduction) . '</td>
                <td class="ytd-col">' . formatAmount($netPayAfterOtherDeduction) . '</td>
            </tr>
            
            <!-- Employer Contributions Section -->
            <tr class="section-divider">
                <td class="label-col section-label">EMPLOYER CONTRIBUTIONS:</td>
                <td class="sgd-col"></td>
                <td class="ytd-col"></td>
            </tr>
            <tr>
                <td class="label-col">Employer CPF</td>
                <td class="sgd-col">' . formatAmount($employee['employer_contributions']['employer_cpf']) . '</td>
                <td class="ytd-col">' . formatAmount($employee['employer_contributions']['employer_cpf']) . '</td>
            </tr>
            <tr>
                <td class="label-col">SDL</td>
                <td class="sgd-col">' . formatAmount($employee['employer_contributions']['sdl']) . '</td>
                <td class="ytd-col">' . formatAmount($employee['employer_contributions']['sdl']) . '</td>
            </tr>
        </tbody>
    </table>
    
    <table class="bank-info-table">
        <tr>
            <td colspan="2" class="bank-info-title">To Be Credited to:</td>
        </tr>
        <tr>
            <td class="bank-info-label">Bank Name:</td>
            <td class="bank-info-value">' . $employee['bank']['name'] . '</td>
        </tr>
        <tr>
            <td class="bank-info-label">Account Number:</td>
            <td class="bank-info-value">' . $employee['bank']['account_number'] . '</td>
        </tr>
        <tr>
            <td class="bank-info-label">On:</td>
            <td class="bank-info-value">' . $employee['bank']['credit_date'] . '</td>
        </tr>
        <tr>
            <td class="bank-info-label">Bank Code:</td>
            <td class="bank-info-value">' . $employee['bank']['bank_code'] . '</td>
        </tr>
        <tr>
            <td class="bank-info-label">Branch Code:</td>
            <td class="bank-info-value">' . $employee['bank']['branch_code'] . '</td>
        </tr>
    </table>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                ' . $employee['prepared_by'] . '<br>Prepared by
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                ' . $employee['approved_by'] . '<br>Approved by
            </div>
        </div>
    </div>
    ';
    
    $mpdf->WriteHTML($html);
}

// 输出 PDF
$mpdf->Output('payslips_' . date('Y-m-d') . '.pdf', 'I');
// 如果要保存文件，使用：
// $mpdf->Output('payslips_' . date('Y-m-d') . '.pdf', 'F');
