@extends('layouts.app')

@section('title', '月度工资核算')

@section('content')
<div class="container-fluid py-4">
    <div class="card" style="width: 100%;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-table"></i> 月度工资核算</h4>
            <div class="d-flex gap-2">
                <form action="{{ route('payrolls.monthly-calculation') }}" method="GET" class="d-flex gap-2">
                    <select name="year" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}年</option>
                        @endfor
                    </select>
                    <select name="month" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}月</option>
                        @endfor
                    </select>
                </form>

                @if(isset($isLocked) && $isLocked)
                    <span class="badge bg-danger d-flex align-items-center">
                        <i class="bi bi-lock-fill me-1"></i> 已锁定
                    </span>
                @else
                    {{-- Roll Over Form --}}
                    <form action="{{ route('payrolls.roll-over') }}" method="POST" onsubmit="return confirm('确定要将上个月的工资数据滚存到本月吗？这将创建新的记录。')">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <button type="submit" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-arrow-repeat"></i> 从上月滚存
                        </button>
                    </form>

                    {{-- Clear Form --}}
                    {{-- Clear Button (Client-side only) --}}
                    <button type="button" class="btn btn-warning btn-sm text-dark" onclick="clearTableInputs()">
                        <i class="bi bi-trash"></i> 一键清空 (当前页)
                    </button>

                    <button type="button" class="btn btn-primary btn-sm" onclick="saveAllPayrolls()">
                        <i class="bi bi-save"></i> 保存所有
                    </button>
                    
                    {{-- Lock Form --}}
                    <form action="{{ route('payrolls.lock') }}" method="POST" onsubmit="return confirm('确定要锁定本月工资吗？锁定后将无法修改！')">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-lock"></i> 锁定 (Close)
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('payrolls.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> 返回列表
                </a>
            </div>
        </div>
        <div class="card-body" style="overflow-x: auto; padding: 1.5rem 0.5rem;">
            @if(isset($isLocked) && $isLocked)
                <div class="alert alert-warning">
                    <i class="bi bi-lock-fill"></i> 当前月份的工资已锁定，无法进行编辑。
                </div>
            @endif

            <form id="payrollForm" method="POST" action="{{ route('payrolls.batch-store') }}">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <fieldset {{ (isset($isLocked) && $isLocked) ? 'disabled' : '' }}>
                <table class="table table-bordered table-hover mx-auto" id="payrollTable" style="width: auto; border-collapse: collapse;">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 50px; background-color: #f0f0f0;">
                                <strong>S/N</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 110px; background-color: #f0f0f0;">
                                <strong>Commencement<br>Date<br>(DD.MM.YY)</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 110px; background-color: #f0f0f0;">
                                <strong>Last Date</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 60px; background-color: #f0f0f0;">
                                <strong>Sex</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 120px; background-color: #f0f0f0;">
                                <strong>Position</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 100px; background-color: #f0f0f0;">
                                <strong>NRIC / FIN</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 100px; background-color: #f0f0f0;">
                                <strong>Name of Employee</strong>
                            </th>
                            <th colspan="6" class="text-center" style="background-color: #e3f2fd;">
                                <strong>收入项 (Earnings)</strong>
                            </th>
                            <th colspan="5" class="text-center" style="background-color: #fff3e0;">
                                <strong>扣除项 (Deductions)</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 100px; background-color: #f1f8e9;">
                                <strong>净工资<br>(Net Pay)</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 100px; background-color: #fce4ec;">
                                <strong>其他扣除<br>(Advance/Loan)</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 120px; background-color: #e8f5e9;">
                                <strong>最终发放<br>(Final Pay)</strong>
                            </th>
                            <th colspan="2" class="text-center" style="background-color: #f3e5f5;">
                                <strong>雇主贡献</strong>
                            </th>
                            <th colspan="3" class="text-center" style="background-color: #e0f2f1;">
                                <strong>银行信息</strong>
                            </th>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 100px;">
                                <strong>备注</strong>
                            </th>
                        </tr>
                        <tr>
                            <!-- 收入项列 -->
                            <th class="text-center" style="background-color: #e3f2fd;">基本工资<br>(Base Salary)</th>
                            <th class="text-center" style="background-color: #e3f2fd;">津贴<br>(Allowance)</th>
                            <th class="text-center" style="background-color: #e3f2fd;">加班/其他<br>(OT/Other)</th>
                            <th class="text-center" style="background-color: #e3f2fd;">奖金<br>(Bonus)</th>
                            <th class="text-center" style="background-color: #e3f2fd;">未用带薪假<br>(Unused Leave)</th>
                            <th class="text-center" style="background-color: #e3f2fd;">无薪假<br>(Unpaid Leave)</th>
                            
                            <!-- 扣除项列 -->
                            <th class="text-center" style="background-color: #fff3e0;">员工CPF<br>(Employee CPF)</th>
                            <th class="text-center" style="background-color: #fff3e0;">CDAC/MBMF/SINDA</th>
                            <th class="text-center" style="background-color: #fff3e0;">其他扣除<br>(Other)</th>
                            <th class="text-center" style="background-color: #fff3e0;">税费<br>(Tax)</th>
                            <th class="text-center" style="background-color: #fff3e0;">总扣除<br>(Total Deduction)</th>
                            
                            <!-- 雇主贡献列 -->
                            <th class="text-center" style="background-color: #f3e5f5;">雇主CPF<br>(Employer CPF)</th>
                            <th class="text-center" style="background-color: #f3e5f5;">SDL</th>
                            
                            <!-- 银行信息列 -->
                            <th class="text-center" style="background-color: #e0f2f1;">银行名称</th>
                            <th class="text-center" style="background-color: #e0f2f1;">账号</th>
                            <th class="text-center" style="background-color: #e0f2f1;">入账日期</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // 临时测试数据（从图片中提取）
                            $testData = [
                                'JIA SHIBO' => [
                                    'commencement_date' => '01.02.19',
                                    'last_date' => '',
                                    'sex' => 'M',
                                    'position' => 'Senior Project Manager',
                                    'nric_fin' => 'S91933721',
                                    'base_salary' => 2500.00,
                                    'allowances' => 0,
                                    'overtime_other' => 0,
                                    'bonus' => 0,
                                    'unutilised_pay_leave' => 0,
                                    'unpaid_leave' => 0,
                                    'employee_cpf' => 500.00,
                                    'cdac_mbmf_sinda' => 1.00,
                                    'deductions' => 0,
                                    'tax' => 0,
                                    'advance_loan' => 50.50,
                                    'employer_cpf' => 425.00,
                                    'sdl' => 6.25,
                                    'bank_name' => 'DBSBANK LTD.',
                                    'bank_account_number' => '207908967',
                                ],
                                'BAI SHANSHAN' => [
                                    'commencement_date' => '01.03.19',
                                    'last_date' => '',
                                    'sex' => 'F',
                                    'position' => 'Senior Business Development Manager',
                                    'nric_fin' => 'S9193371J',
                                    'base_salary' => 4500.00,
                                    'allowances' => 0,
                                    'overtime_other' => 0,
                                    'bonus' => 0,
                                    'unutilised_pay_leave' => 0,
                                    'unpaid_leave' => 0,
                                    'employee_cpf' => 900.00,
                                    'cdac_mbmf_sinda' => 1.50,
                                    'deductions' => 0,
                                    'tax' => 0,
                                    'advance_loan' => 50.50,
                                    'employer_cpf' => 765.00,
                                    'sdl' => 11.25,
                                    'bank_name' => 'DBSBANK LTD.',
                                    'bank_account_number' => '207908967',
                                ],
                                'ZHAO PENG' => [
                                    'commencement_date' => '01.12.14',
                                    'last_date' => '',
                                    'sex' => 'M',
                                    'position' => 'Finance Director',
                                    'nric_fin' => 'S7767617B',
                                    'base_salary' => 0,
                                    'allowances' => 0,
                                    'overtime_other' => 0,
                                    'bonus' => 0,
                                    'unutilised_pay_leave' => 0,
                                    'unpaid_leave' => 0,
                                    'employee_cpf' => 0,
                                    'cdac_mbmf_sinda' => 0,
                                    'deductions' => 0,
                                    'tax' => 0,
                                    'advance_loan' => 0,
                                    'employer_cpf' => 0,
                                    'sdl' => 0,
                                    'bank_name' => 'UOB',
                                    'bank_account_number' => '2412069945',
                                ],
                                'ANG LEE WUN' => [
                                    'commencement_date' => '01.11.23',
                                    'last_date' => '',
                                    'sex' => 'F',
                                    'position' => 'ACCOUNTANT',
                                    'nric_fin' => 'S0145747G',
                                    'base_salary' => 3200.00,
                                    'allowances' => 0,
                                    'overtime_other' => 0,
                                    'bonus' => 0,
                                    'unutilised_pay_leave' => 0,
                                    'unpaid_leave' => 0,
                                    'employee_cpf' => 640.00,
                                    'cdac_mbmf_sinda' => 1.00,
                                    'deductions' => 0,
                                    'tax' => 0,
                                    'advance_loan' => 200.00,
                                    'employer_cpf' => 544.00,
                                    'sdl' => 8.00,
                                    'bank_name' => 'DBS BANK LTD.',
                                    'bank_account_number' => '2710041409',
                                ],
                                'LI RUNSHENG' => [
                                    'commencement_date' => '01.03.20',
                                    'last_date' => '',
                                    'sex' => 'M',
                                    'position' => 'IT Director',
                                    'nric_fin' => 'S8300321B',
                                    'base_salary' => 0,
                                    'allowances' => 0,
                                    'overtime_other' => 0,
                                    'bonus' => 0,
                                    'unutilised_pay_leave' => 0,
                                    'unpaid_leave' => 0,
                                    'employee_cpf' => 0,
                                    'cdac_mbmf_sinda' => 0,
                                    'deductions' => 0,
                                    'tax' => 0,
                                    'advance_loan' => 0,
                                    'employer_cpf' => 0,
                                    'sdl' => 0,
                                    'bank_name' => '',
                                    'bank_account_number' => '',
                                ],
                                'CHEN TIK MEI' => [
                                    'commencement_date' => '01.07.24',
                                    'last_date' => '',
                                    'sex' => 'F',
                                    'position' => 'ACCOUNTANT ASSISTANT',
                                    'nric_fin' => 'M34676250',
                                    'base_salary' => 1000.00,
                                    'allowances' => 0,
                                    'overtime_other' => 90.00,
                                    'bonus' => 0,
                                    'unutilised_pay_leave' => 0,
                                    'unpaid_leave' => 0,
                                    'employee_cpf' => 0,
                                    'cdac_mbmf_sinda' => 0,
                                    'deductions' => 0,
                                    'tax' => 0,
                                    'advance_loan' => 0,
                                    'employer_cpf' => 0,
                                    'sdl' => 4.50,
                                    'bank_name' => 'DBSBANK LTD.',
                                    'bank_account_number' => '453947912',
                                ],
                            ];
                        @endphp
                        @foreach($employees as $index => $employee)
                            @php
                                $payroll = $existingPayrolls->get($employee->id);
                                $employeeData = $testData[strtoupper($employee->name)] ?? [];
                            @endphp
                            <tr data-user-id="{{ $employee->id }}">
                                <td class="text-center" style="background-color: #f0f0f0;">
                                    {{ $loop->iteration }}
                                </td>
                                <td style="background-color: #f0f0f0;">
                                    <input type="text" class="form-control form-control-sm text-center" 
                                           name="employees[{{ $employee->id }}][commencement_date]" 
                                           placeholder="DD.MM.YY"
                                           value="{{ old("employees.{$employee->id}.commencement_date", $employeeData['commencement_date'] ?? ($employee->commencement_date ? $employee->commencement_date->format('d.m.y') : '')) }}"
                                           style="width: 100%; font-size: 0.85rem;">
                                </td>
                                <td style="background-color: #f0f0f0;">
                                    <input type="text" class="form-control form-control-sm text-center" 
                                           name="employees[{{ $employee->id }}][last_date]" 
                                           value="{{ old("employees.{$employee->id}.last_date", $employeeData['last_date'] ?? ($employee->last_date ? $employee->last_date->format('Y-m-d') : '')) }}"
                                           style="width: 100%; font-size: 0.85rem;">
                                </td>
                                <td style="background-color: #f0f0f0;">
                                    <input type="text" class="form-control form-control-sm text-center" 
                                           name="employees[{{ $employee->id }}][sex]" 
                                           value="{{ old("employees.{$employee->id}.sex", $employeeData['sex'] ?? ($employee->sex ?? '')) }}"
                                           style="width: 100%; font-size: 0.85rem;">
                                </td>
                                <td style="background-color: #f0f0f0;">
                                    <input type="text" class="form-control form-control-sm text-center" 
                                           name="employees[{{ $employee->id }}][position]" 
                                           value="{{ old("employees.{$employee->id}.position", $employeeData['position'] ?? ($employee->position ?? '')) }}"
                                           style="width: 100%; font-size: 0.85rem;">
                                </td>
                                <td style="background-color: #f0f0f0;">
                                    <input type="text" class="form-control form-control-sm text-center" 
                                           name="employees[{{ $employee->id }}][nric_fin]" 
                                           value="{{ old("employees.{$employee->id}.nric_fin", $employeeData['nric_fin'] ?? ($employee->nric_fin ?? '')) }}"
                                           style="width: 100%; font-size: 0.85rem;">
                                </td>
                                <td class="fw-bold text-center" style="background-color: #f0f0f0;">
                                    {{ $employee->name }}
                                </td>
                                
                                <!-- 收入项 -->
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][base_salary]" 
                                           value="{{ old("payrolls.{$employee->id}.base_salary", $payroll->base_salary ?? ($employeeData['base_salary'] ?? '')) }}" 
                                           data-field="base_salary" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][allowances]" 
                                           value="{{ old("payrolls.{$employee->id}.allowances", $payroll->allowances ?? ($employeeData['allowances'] ?? '')) }}" 
                                           data-field="allowances" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][overtime_other]" 
                                           value="{{ old("payrolls.{$employee->id}.overtime_other", $payroll->overtime_other ?? ($employeeData['overtime_other'] ?? '')) }}" 
                                           data-field="overtime_other" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][bonus]" 
                                           value="{{ old("payrolls.{$employee->id}.bonus", $payroll->bonus ?? ($employeeData['bonus'] ?? '')) }}" 
                                           data-field="bonus" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][unutilised_pay_leave]" 
                                           value="{{ old("payrolls.{$employee->id}.unutilised_pay_leave", $payroll->unutilised_pay_leave ?? ($employeeData['unutilised_pay_leave'] ?? '')) }}" 
                                           data-field="unutilised_pay_leave" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][unpaid_leave]" 
                                           value="{{ old("payrolls.{$employee->id}.unpaid_leave", $payroll->unpaid_leave ?? ($employeeData['unpaid_leave'] ?? '')) }}" 
                                           data-field="unpaid_leave" data-user-id="{{ $employee->id }}">
                                </td>
                                
                                <!-- 扣除项 -->
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][employee_cpf]" 
                                           value="{{ old("payrolls.{$employee->id}.employee_cpf", $payroll->employee_cpf ?? '') }}" 
                                           data-field="employee_cpf" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][cdac_mbmf_sinda]" 
                                           value="{{ old("payrolls.{$employee->id}.cdac_mbmf_sinda", $payroll->cdac_mbmf_sinda ?? '') }}" 
                                           data-field="cdac_mbmf_sinda" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][deductions]" 
                                           value="{{ old("payrolls.{$employee->id}.deductions", $payroll->deductions ?? '') }}" 
                                           data-field="deductions" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][tax]" 
                                           value="{{ old("payrolls.{$employee->id}.tax", $payroll->tax ?? '') }}" 
                                           data-field="tax" data-user-id="{{ $employee->id }}">
                                </td>
                                <td class="text-end calculated-cell" data-calculation="total_deduction" data-user-id="{{ $employee->id }}">
                                    <strong>0.00</strong>
                                </td>
                                
                                <!-- 净工资 -->
                                <td class="text-end calculated-cell" data-calculation="net_pay" data-user-id="{{ $employee->id }}">
                                    <strong>0.00</strong>
                                </td>
                                
                                <!-- 其他扣除 -->
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][advance_loan]" 
                                           value="{{ old("payrolls.{$employee->id}.advance_loan", $payroll->advance_loan ?? ($employeeData['advance_loan'] ?? '')) }}" 
                                           data-field="advance_loan" data-user-id="{{ $employee->id }}">
                                </td>
                                
                                <!-- 最终发放 -->
                                <td class="text-end calculated-cell" data-calculation="final_pay" data-user-id="{{ $employee->id }}">
                                    <strong class="text-primary">0.00</strong>
                                </td>
                                
                                <!-- 雇主贡献 -->
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][employer_cpf]" 
                                           value="{{ old("payrolls.{$employee->id}.employer_cpf", $payroll->employer_cpf ?? ($employeeData['employer_cpf'] ?? '')) }}" 
                                           data-field="employer_cpf" data-user-id="{{ $employee->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm editable-cell" 
                                           name="payrolls[{{ $employee->id }}][sdl]" 
                                           value="{{ old("payrolls.{$employee->id}.sdl", $payroll->sdl ?? ($employeeData['sdl'] ?? '')) }}" 
                                           data-field="sdl" data-user-id="{{ $employee->id }}">
                                </td>
                                
                                <!-- 银行信息 -->
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="payrolls[{{ $employee->id }}][bank_name]" 
                                           value="{{ old("payrolls.{$employee->id}.bank_name", $payroll->bank_name ?? ($employeeData['bank_name'] ?? '')) }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="payrolls[{{ $employee->id }}][bank_account_number]" 
                                           value="{{ old("payrolls.{$employee->id}.bank_account_number", $payroll->bank_account_number ?? ($employeeData['bank_account_number'] ?? '')) }}">
                                </td>
                                <td>
                                    <input type="date" class="form-control form-control-sm" 
                                           name="payrolls[{{ $employee->id }}][credit_date]" 
                                           value="{{ old("payrolls.{$employee->id}.credit_date", $payroll && $payroll->credit_date ? $payroll->credit_date->format('Y-m-d') : \Carbon\Carbon::createFromDate($year, $month)->endOfMonth()->format('Y-m-d')) }}">
                                </td>
                                
                                <!-- 备注 -->
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="payrolls[{{ $employee->id }}][notes]" 
                                           value="{{ old("payrolls.{$employee->id}.notes", $payroll->notes ?? '') }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="7" class="fw-bold text-center" style="background-color: #f0f0f0;">合计</td>
                            <td class="text-end"><strong id="sum_base_salary">0.00</strong></td>
                            <td class="text-end"><strong id="sum_allowances">0.00</strong></td>
                            <td class="text-end"><strong id="sum_overtime_other">0.00</strong></td>
                            <td class="text-end"><strong id="sum_bonus">0.00</strong></td>
                            <td class="text-end"><strong id="sum_unutilised_pay_leave">0.00</strong></td>
                            <td class="text-end"><strong id="sum_unpaid_leave">0.00</strong></td>
                            <td class="text-end"><strong id="sum_employee_cpf">0.00</strong></td>
                            <td class="text-end"><strong id="sum_cdac_mbmf_sinda">0.00</strong></td>
                            <td class="text-end"><strong id="sum_deductions">0.00</strong></td>
                            <td class="text-end"><strong id="sum_tax">0.00</strong></td>
                            <td class="text-end"><strong id="sum_total_deduction">0.00</strong></td>
                            <td class="text-end"><strong id="sum_net_pay">0.00</strong></td>
                            <td class="text-end"><strong id="sum_advance_loan">0.00</strong></td>
                            <td class="text-end"><strong id="sum_final_pay">0.00</strong></td>
                            <td class="text-end"><strong id="sum_employer_cpf">0.00</strong></td>
                            <td class="text-end"><strong id="sum_sdl">0.00</strong></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
                </fieldset>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    body {
        background-color: #f5f5f5;
    }
    
    /* 表格边框样式 - 深黑色，允许叠加 */
    #payrollTable {
        border-collapse: collapse;
        border: 1px solid #000;
    }
    
    #payrollTable th,
    #payrollTable td {
        border: 1px solid #000 !important;
    }
    
    #payrollTable th {
        white-space: nowrap;
        font-size: 0.9rem;
        padding: 10px 6px;
        height: 45px;
        line-height: 1.2;
        vertical-align: middle;
        position: relative;
    }
    
    /* 列宽调整手柄 */
    .resize-handle {
        position: absolute;
        top: 0;
        right: -3px;
        width: 8px;
        height: 100%;
        cursor: col-resize;
        background: transparent;
        z-index: 100;
        transition: background-color 0.2s;
    }
    
    #payrollTable thead {
        position: relative;
    }
    
    #payrollTable th {
        overflow: visible !important;
        position: relative;
    }
    
    #payrollTable th:hover .resize-handle {
        background-color: rgba(13, 110, 253, 0.5);
    }
    
    .resize-handle:hover,
    .resizing .resize-handle {
        background-color: #0d6efd !important;
    }
    
    .resizing {
        user-select: none;
    }
    
    body.resizing {
        cursor: col-resize !important;
        user-select: none;
    }
    
    body.resizing * {
        cursor: col-resize !important;
    }
    
    #payrollTable td {
        padding: 6px;
        height: 40px;
        line-height: 1.2;
        vertical-align: middle;
    }
    
    .editable-cell input {
        width: 110px;
        border: 1px solid #ced4da;
        text-align: right;
        font-size: 0.9rem;
        padding: 4px 8px;
        height: 28px;
        line-height: 1.2;
        box-sizing: border-box;
    }
    
    .editable-cell input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        background-color: #fff;
        outline: none;
    }
    
    .calculated-cell {
        background-color: #f8f9fa;
        font-weight: bold;
        font-size: 0.95rem;
    }
    
    #payrollTable tbody tr:hover {
        background-color: #f0f0f0;
    }
    
    .sticky-top {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* 确保所有输入框不撑高行 */
    #payrollTable input.form-control,
    #payrollTable input.form-control-sm {
        height: 28px;
        padding: 4px 8px;
        line-height: 1.2;
        box-sizing: border-box;
        font-size: 0.85rem;
    }
    
    #payrollTable input[type="date"] {
        height: 28px;
        padding: 4px 8px;
        line-height: 1.2;
        box-sizing: border-box;
    }
    
    /* PC端优化：更大的输入框和更好的间距 */
    @media (min-width: 992px) {
        .editable-cell input {
            width: 120px;
        }
        
        #payrollTable th {
            padding: 10px 8px;
            font-size: 0.95rem;
            height: 45px;
        }
        
        #payrollTable td {
            padding: 6px;
            height: 40px;
        }
        
        #payrollTable input.form-control,
        #payrollTable input.form-control-sm {
            height: 28px;
            padding: 4px 8px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    // 计算单个员工的所有金额
    function calculateRow(userId) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (!row) return;

        // 获取收入项
        const baseSalary = parseFloat(row.querySelector('[data-field="base_salary"]')?.value || 0);
        const allowances = parseFloat(row.querySelector('[data-field="allowances"]')?.value || 0);
        const overtimeOther = parseFloat(row.querySelector('[data-field="overtime_other"]')?.value || 0);
        const bonus = parseFloat(row.querySelector('[data-field="bonus"]')?.value || 0);
        const unutilisedPayLeave = parseFloat(row.querySelector('[data-field="unutilised_pay_leave"]')?.value || 0);
        const unpaidLeave = parseFloat(row.querySelector('[data-field="unpaid_leave"]')?.value || 0);

        // 计算总收入
        const totalEarnings = baseSalary + allowances + overtimeOther + bonus + unutilisedPayLeave - unpaidLeave;

        // 获取扣除项
        const employeeCpf = parseFloat(row.querySelector('[data-field="employee_cpf"]')?.value || 0);
        const cdacMbmfSinda = parseFloat(row.querySelector('[data-field="cdac_mbmf_sinda"]')?.value || 0);
        const deductions = parseFloat(row.querySelector('[data-field="deductions"]')?.value || 0);
        const tax = parseFloat(row.querySelector('[data-field="tax"]')?.value || 0);

        // 计算总扣除
        const totalDeduction = employeeCpf + cdacMbmfSinda + deductions + tax;

        // 计算净工资
        const netPay = totalEarnings - totalDeduction;

        // 获取其他扣除
        const advanceLoan = parseFloat(row.querySelector('[data-field="advance_loan"]')?.value || 0);

        // 计算最终发放
        const finalPay = netPay - advanceLoan;

        // 更新计算字段
        const totalDeductionCell = row.querySelector('[data-calculation="total_deduction"]');
        const netPayCell = row.querySelector('[data-calculation="net_pay"]');
        const finalPayCell = row.querySelector('[data-calculation="final_pay"]');

        if (totalDeductionCell) {
            totalDeductionCell.innerHTML = '<strong>' + totalDeduction.toFixed(2) + '</strong>';
        }
        if (netPayCell) {
            netPayCell.innerHTML = '<strong>' + netPay.toFixed(2) + '</strong>';
        }
        if (finalPayCell) {
            finalPayCell.innerHTML = '<strong class="text-primary">' + finalPay.toFixed(2) + '</strong>';
        }
    }

    // 计算所有行的合计
    function calculateTotals() {
        const rows = document.querySelectorAll('#payrollTable tbody tr[data-user-id]');
        
        let sums = {
            base_salary: 0,
            allowances: 0,
            overtime_other: 0,
            bonus: 0,
            unutilised_pay_leave: 0,
            unpaid_leave: 0,
            employee_cpf: 0,
            cdac_mbmf_sinda: 0,
            deductions: 0,
            tax: 0,
            total_deduction: 0,
            net_pay: 0,
            advance_loan: 0,
            final_pay: 0,
            employer_cpf: 0,
            sdl: 0
        };

        rows.forEach(row => {
            const userId = row.getAttribute('data-user-id');
            calculateRow(userId);

            // 累加基础字段
            sums.base_salary += parseFloat(row.querySelector('[data-field="base_salary"]')?.value || 0);
            sums.allowances += parseFloat(row.querySelector('[data-field="allowances"]')?.value || 0);
            sums.overtime_other += parseFloat(row.querySelector('[data-field="overtime_other"]')?.value || 0);
            sums.bonus += parseFloat(row.querySelector('[data-field="bonus"]')?.value || 0);
            sums.unutilised_pay_leave += parseFloat(row.querySelector('[data-field="unutilised_pay_leave"]')?.value || 0);
            sums.unpaid_leave += parseFloat(row.querySelector('[data-field="unpaid_leave"]')?.value || 0);
            sums.employee_cpf += parseFloat(row.querySelector('[data-field="employee_cpf"]')?.value || 0);
            sums.cdac_mbmf_sinda += parseFloat(row.querySelector('[data-field="cdac_mbmf_sinda"]')?.value || 0);
            sums.deductions += parseFloat(row.querySelector('[data-field="deductions"]')?.value || 0);
            sums.tax += parseFloat(row.querySelector('[data-field="tax"]')?.value || 0);
            sums.advance_loan += parseFloat(row.querySelector('[data-field="advance_loan"]')?.value || 0);
            sums.employer_cpf += parseFloat(row.querySelector('[data-field="employer_cpf"]')?.value || 0);
            sums.sdl += parseFloat(row.querySelector('[data-field="sdl"]')?.value || 0);

            // 计算每行的总额
            const baseSalary = parseFloat(row.querySelector('[data-field="base_salary"]')?.value || 0);
            const allowances = parseFloat(row.querySelector('[data-field="allowances"]')?.value || 0);
            const overtimeOther = parseFloat(row.querySelector('[data-field="overtime_other"]')?.value || 0);
            const bonus = parseFloat(row.querySelector('[data-field="bonus"]')?.value || 0);
            const unutilisedPayLeave = parseFloat(row.querySelector('[data-field="unutilised_pay_leave"]')?.value || 0);
            const unpaidLeave = parseFloat(row.querySelector('[data-field="unpaid_leave"]')?.value || 0);
            const employeeCpf = parseFloat(row.querySelector('[data-field="employee_cpf"]')?.value || 0);
            const cdacMbmfSinda = parseFloat(row.querySelector('[data-field="cdac_mbmf_sinda"]')?.value || 0);
            const deductions = parseFloat(row.querySelector('[data-field="deductions"]')?.value || 0);
            const tax = parseFloat(row.querySelector('[data-field="tax"]')?.value || 0);
            const advanceLoan = parseFloat(row.querySelector('[data-field="advance_loan"]')?.value || 0);

            const totalEarnings = baseSalary + allowances + overtimeOther + bonus + unutilisedPayLeave - unpaidLeave;
            const totalDeduction = employeeCpf + cdacMbmfSinda + deductions + tax;
            const netPay = totalEarnings - totalDeduction;
            const finalPay = netPay - advanceLoan;

            sums.total_deduction += totalDeduction;
            sums.net_pay += netPay;
            sums.final_pay += finalPay;
        });

        // 更新合计显示
        document.getElementById('sum_base_salary').textContent = sums.base_salary.toFixed(2);
        document.getElementById('sum_allowances').textContent = sums.allowances.toFixed(2);
        document.getElementById('sum_overtime_other').textContent = sums.overtime_other.toFixed(2);
        document.getElementById('sum_bonus').textContent = sums.bonus.toFixed(2);
        document.getElementById('sum_unutilised_pay_leave').textContent = sums.unutilised_pay_leave.toFixed(2);
        document.getElementById('sum_unpaid_leave').textContent = sums.unpaid_leave.toFixed(2);
        document.getElementById('sum_employee_cpf').textContent = sums.employee_cpf.toFixed(2);
        document.getElementById('sum_cdac_mbmf_sinda').textContent = sums.cdac_mbmf_sinda.toFixed(2);
        document.getElementById('sum_deductions').textContent = sums.deductions.toFixed(2);
        document.getElementById('sum_tax').textContent = sums.tax.toFixed(2);
        document.getElementById('sum_total_deduction').textContent = sums.total_deduction.toFixed(2);
        document.getElementById('sum_net_pay').textContent = sums.net_pay.toFixed(2);
        document.getElementById('sum_advance_loan').textContent = sums.advance_loan.toFixed(2);
        document.getElementById('sum_final_pay').textContent = sums.final_pay.toFixed(2);
        document.getElementById('sum_employer_cpf').textContent = sums.employer_cpf.toFixed(2);
        document.getElementById('sum_sdl').textContent = sums.sdl.toFixed(2);
    }

    // 列宽调整功能
    function initColumnResize() {
        const table = document.getElementById('payrollTable');
        if (!table) return;
        
        // 获取所有表头（第一行和第二行）
        const allHeaders = table.querySelectorAll('thead th');
        const dataRow = table.querySelector('tbody tr');
        if (!dataRow) return;
        
        const totalCols = dataRow.cells.length;
        
        // 为每一列添加resize功能
        for (let colIndex = 0; colIndex < totalCols; colIndex++) {
            // 找到对应的表头 - 优先使用第一行有rowspan的，如果没有则用第二行
            let header = null;
            
            // 先查找第一行的th
            const firstRow = table.querySelector('thead tr:first-child');
            if (firstRow) {
                const firstRowCells = Array.from(firstRow.querySelectorAll('th'));
                let colCounter = 0;
                
                for (let cell of firstRowCells) {
                    const colspan = parseInt(cell.getAttribute('colspan') || 1);
                    const rowspan = parseInt(cell.getAttribute('rowspan') || 1);
                    
                    if (colCounter <= colIndex && colIndex < colCounter + colspan) {
                        if (rowspan > 1 || colIndex === colCounter) {
                            header = cell;
                            break;
                        }
                    }
                    colCounter += colspan;
                }
            }
            
            // 如果第一行没找到，尝试直接使用第二行对应索引的th
            if (!header) {
                const secondRow = table.querySelector('thead tr:nth-child(2)');
                if (secondRow) {
                    const secondRowCells = Array.from(secondRow.querySelectorAll('th'));
                    if (secondRowCells[colIndex]) {
                        header = secondRowCells[colIndex];
                    }
                }
            }
            
            if (!header) {
                console.warn('找不到列索引', colIndex, '对应的表头');
                continue;
            }
            
            // 确保header有position relative
            header.style.position = 'relative';
            
            // 创建resize手柄（如果还没有）
            let handle = header.querySelector('.resize-handle');
            if (!handle) {
                handle = document.createElement('div');
                handle.className = 'resize-handle';
                handle.title = '拖拽调整列宽';
                header.appendChild(handle);
            }
            
            // 加载保存的列宽
            const savedWidth = localStorage.getItem(`payroll_table_col_${colIndex}`);
            if (savedWidth) {
                const width = parseInt(savedWidth);
                setColumnWidth(table, colIndex, width);
            }
            
            // 鼠标按下事件
            handle.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const startX = e.clientX;
                const startWidth = header.offsetWidth;
                const currentColIndex = colIndex;
                
                document.body.classList.add('resizing');
                header.classList.add('resizing');
                
                // 创建临时遮罩层防止文本选择
                const overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;cursor:col-resize;';
                document.body.appendChild(overlay);
                
                // 鼠标移动事件
                function onMouseMove(e) {
                    const diff = e.clientX - startX;
                    const newWidth = Math.max(50, startWidth + diff);
                    setColumnWidth(table, currentColIndex, newWidth);
                }
                
                // 鼠标释放事件
                function onMouseUp() {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    document.body.classList.remove('resizing');
                    header.classList.remove('resizing');
                    if (overlay.parentNode) {
                        document.body.removeChild(overlay);
                    }
                    
                    // 保存列宽
                    const width = header.offsetWidth;
                    localStorage.setItem(`payroll_table_col_${currentColIndex}`, width.toString());
                }
                
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        }
    }
    
    // 设置列宽
    function setColumnWidth(table, columnIndex, width) {
        // 设置所有行中对应列的宽度
        const allRows = table.querySelectorAll('thead tr, tbody tr, tfoot tr');
        
        allRows.forEach(row => {
            const cell = row.cells[columnIndex];
            if (cell) {
                cell.style.width = width + 'px';
                cell.style.minWidth = width + 'px';
            }
        });
    }
    
    // 为所有可编辑单元格添加事件监听
    document.addEventListener('DOMContentLoaded', function() {
        // 初始化列宽调整
        initColumnResize();
        
        const editableCells = document.querySelectorAll('.editable-cell');
        
        editableCells.forEach(cell => {
            const input = cell.querySelector('input');
            if (input) {
                input.addEventListener('input', function() {
                    const userId = this.getAttribute('data-user-id') || this.closest('tr').getAttribute('data-user-id');
                    calculateRow(userId);
                    calculateTotals();
                });

                // 支持键盘导航
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const currentRow = this.closest('tr');
                        const nextRow = currentRow.nextElementSibling;
                        if (nextRow) {
                            const nextInput = nextRow.querySelector('input[data-field="' + this.getAttribute('data-field') + '"]');
                            if (nextInput) {
                                nextInput.focus();
                                nextInput.select();
                            }
                        }
                    } else if (e.key === 'ArrowRight' && this.selectionStart === this.value.length) {
                        const currentRow = this.closest('tr');
                        const nextInput = currentRow.querySelector(`input[data-field="${this.getAttribute('data-field')}"], input[data-field="${this.getAttribute('data-field')}"] ~ input`);
                        if (nextInput && nextInput !== this) {
                            e.preventDefault();
                            nextInput.focus();
                            nextInput.select();
                        }
                    } else if (e.key === 'ArrowLeft' && this.selectionStart === 0) {
                        const currentRow = this.closest('tr');
                        const prevInput = Array.from(currentRow.querySelectorAll('input')).reverse().find(inp => inp !== this && inp.offsetLeft < this.offsetLeft);
                        if (prevInput) {
                            e.preventDefault();
                            prevInput.focus();
                            prevInput.select();
                        }
                    }
                });
            }
        });

        // 初始计算
        calculateTotals();
    });

    // 清空当前页面的所有输入框（不涉及数据库）
    function clearTableInputs() {
        if (!confirm('确定要清空当前页面的工资数据吗？\n注意：这只会重置表格中的数字为0，不会删除数据库记录。')) {
            return;
        }

        // 获取所有带有 data-field 属性的输入框（即工资项，忽略员工基本信息）
        const inputs = document.querySelectorAll('#payrollTable input[data-field]');
        
        inputs.forEach(input => {
            input.value = 0;
            // 移除可能存在的 invalid 状态
            input.classList.remove('is-invalid');
        });

        // 重新计算所有数据
        calculateTotals();
        
        // 提示用户
        // alert('表格数据已重置。请记得点击 [保存所有] 以应用更改到数据库。');
    }

    // 保存所有工资记录
    function saveAllPayrolls() {
        if (confirm('确定要保存所有工资记录吗？')) {
            document.getElementById('payrollForm').submit();
        }
    }
</script>
@endsection

