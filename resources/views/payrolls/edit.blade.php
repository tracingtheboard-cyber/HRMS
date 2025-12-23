@extends('layouts.app')

@section('title', '编辑薪资记录')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-pencil"></i> 编辑薪资记录</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('payrolls.update', $payroll) }}" method="POST" id="payrollForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="user_id" class="form-label">员工 <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $payroll->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="year" class="form-label">年份 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('year') is-invalid @enderror" 
                                   id="year" name="year" 
                                   value="{{ old('year', $payroll->year) }}" 
                                   min="2000" max="2100" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="month" class="form-label">月份 <span class="text-danger">*</span></label>
                            <select class="form-select @error('month') is-invalid @enderror" id="month" name="month" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('month', $payroll->month) == $i ? 'selected' : '' }}>
                                        {{ $i }}月
                                    </option>
                                @endfor
                            </select>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="bi bi-cash-coin"></i> 收入项 (Earnings)</h5>

                    <div class="mb-3">
                        <label for="base_salary" class="form-label">基本工资 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('base_salary') is-invalid @enderror" 
                                   id="base_salary" name="base_salary" 
                                   value="{{ old('base_salary', $payroll->base_salary) }}" 
                                   min="0" required>
                            @error('base_salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="allowances" class="form-label">津贴 (Allowance)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('allowances') is-invalid @enderror" 
                                   id="allowances" name="allowances" 
                                   value="{{ old('allowances', $payroll->allowances ?? 0) }}" 
                                   min="0">
                            @error('allowances')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="overtime_other" class="form-label">加班/其他 (Over Time / Other)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('overtime_other') is-invalid @enderror" 
                                   id="overtime_other" name="overtime_other" 
                                   value="{{ old('overtime_other', $payroll->overtime_other ?? 0) }}" 
                                   min="0">
                            @error('overtime_other')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bonus" class="form-label">奖金 (Bonus)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('bonus') is-invalid @enderror" 
                                   id="bonus" name="bonus" 
                                   value="{{ old('bonus', $payroll->bonus ?? 0) }}" 
                                   min="0">
                            @error('bonus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="unutilised_pay_leave" class="form-label">未使用的带薪假期 (Unutilised Pay Leave)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('unutilised_pay_leave') is-invalid @enderror" 
                                   id="unutilised_pay_leave" name="unutilised_pay_leave" 
                                   value="{{ old('unutilised_pay_leave', $payroll->unutilised_pay_leave ?? 0) }}" 
                                   min="0">
                            @error('unutilised_pay_leave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="unpaid_leave" class="form-label">无薪假期 (Unpaid Leave) - 将从总收入中扣除</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('unpaid_leave') is-invalid @enderror" 
                                   id="unpaid_leave" name="unpaid_leave" 
                                   value="{{ old('unpaid_leave', $payroll->unpaid_leave ?? 0) }}" 
                                   min="0">
                            @error('unpaid_leave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info mb-4">
                        <strong>总收入 (Total Earnings)：</strong> <span id="totalEarnings">¥{{ number_format($payroll->total_earnings ?? 0, 2) }}</span>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="bi bi-dash-circle"></i> 扣除项 (Deductions)</h5>

                    <div class="mb-3">
                        <label for="employee_cpf" class="form-label">员工CPF (Employee CPF)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('employee_cpf') is-invalid @enderror" 
                                   id="employee_cpf" name="employee_cpf" 
                                   value="{{ old('employee_cpf', $payroll->employee_cpf ?? 0) }}" 
                                   min="0">
                            @error('employee_cpf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cdac_mbmf_sinda" class="form-label">CDAC / MBMF / SINDA Fund</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('cdac_mbmf_sinda') is-invalid @enderror" 
                                   id="cdac_mbmf_sinda" name="cdac_mbmf_sinda" 
                                   value="{{ old('cdac_mbmf_sinda', $payroll->cdac_mbmf_sinda ?? 0) }}" 
                                   min="0">
                            @error('cdac_mbmf_sinda')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deductions" class="form-label">其他扣除 (Other Deductions)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('deductions') is-invalid @enderror" 
                                   id="deductions" name="deductions" 
                                   value="{{ old('deductions', $payroll->deductions ?? 0) }}" 
                                   min="0">
                            @error('deductions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tax" class="form-label">税费 (Tax)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('tax') is-invalid @enderror" 
                                   id="tax" name="tax" 
                                   value="{{ old('tax', $payroll->tax ?? 0) }}" 
                                   min="0">
                            @error('tax')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-warning mb-4">
                        <strong>总扣除 (Total Deduction)：</strong> <span id="totalDeduction">¥{{ number_format($payroll->total_deduction ?? 0, 2) }}</span>
                        <br>
                        <strong>净工资 (Net Pay)：</strong> <span id="netPay">¥{{ number_format($payroll->net_pay ?? 0, 2) }}</span>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="bi bi-currency-exchange"></i> 其他扣除 (Other Deduction)</h5>

                    <div class="mb-3">
                        <label for="advance_loan" class="form-label">预付款/贷款 (Advance / Loan)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('advance_loan') is-invalid @enderror" 
                                   id="advance_loan" name="advance_loan" 
                                   value="{{ old('advance_loan', $payroll->advance_loan ?? 0) }}" 
                                   min="0">
                            @error('advance_loan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-success mb-4">
                        <strong>其他扣除后的净工资 (Net Pay after other deduction)：</strong> <span id="netPayAfterOther">¥{{ number_format($payroll->net_pay_after_other_deduction ?? 0, 2) }}</span>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="bi bi-building"></i> 雇主贡献 (Employer Contributions)</h5>

                    <div class="mb-3">
                        <label for="employer_cpf" class="form-label">雇主CPF (Employer CPF)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('employer_cpf') is-invalid @enderror" 
                                   id="employer_cpf" name="employer_cpf" 
                                   value="{{ old('employer_cpf', $payroll->employer_cpf ?? 0) }}" 
                                   min="0">
                            @error('employer_cpf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="sdl" class="form-label">SDL (Skills Development Levy)</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" class="form-control @error('sdl') is-invalid @enderror" 
                                   id="sdl" name="sdl" 
                                   value="{{ old('sdl', $payroll->sdl ?? 0) }}" 
                                   min="0">
                            @error('sdl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="bi bi-bank"></i> 银行信息 (Bank Credit Information)</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_name" class="form-label">银行名称 (Bank Name)</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                   id="bank_name" name="bank_name" 
                                   value="{{ old('bank_name', $payroll->bank_name) }}" 
                                   placeholder="例如：DBS BANK LTD.">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bank_account_number" class="form-label">银行账号 (Account Number)</label>
                            <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror" 
                                   id="bank_account_number" name="bank_account_number" 
                                   value="{{ old('bank_account_number', $payroll->bank_account_number) }}" 
                                   placeholder="例如：207908967">
                            @error('bank_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="credit_date" class="form-label">入账日期 (Credit Date)</label>
                        <input type="date" class="form-control @error('credit_date') is-invalid @enderror" 
                               id="credit_date" name="credit_date" 
                               value="{{ old('credit_date', $payroll->credit_date ? $payroll->credit_date->format('Y-m-d') : '') }}">
                        @error('credit_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">备注</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $payroll->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 返回
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 更新记录
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
    function calculatePayroll() {
        // 收入项
        const baseSalary = parseFloat(document.getElementById('base_salary').value) || 0;
        const allowances = parseFloat(document.getElementById('allowances').value) || 0;
        const overtimeOther = parseFloat(document.getElementById('overtime_other').value) || 0;
        const bonus = parseFloat(document.getElementById('bonus').value) || 0;
        const unutilisedPayLeave = parseFloat(document.getElementById('unutilised_pay_leave').value) || 0;
        const unpaidLeave = parseFloat(document.getElementById('unpaid_leave').value) || 0;
        
        // 总收入
        const totalEarnings = baseSalary + allowances + overtimeOther + bonus + unutilisedPayLeave - unpaidLeave;
        document.getElementById('totalEarnings').textContent = '¥' + totalEarnings.toFixed(2);
        
        // 扣除项
        const employeeCpf = parseFloat(document.getElementById('employee_cpf').value) || 0;
        const cdacMbmfSinda = parseFloat(document.getElementById('cdac_mbmf_sinda').value) || 0;
        const deductions = parseFloat(document.getElementById('deductions').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        
        // 总扣除
        const totalDeduction = employeeCpf + cdacMbmfSinda + deductions + tax;
        document.getElementById('totalDeduction').textContent = '¥' + totalDeduction.toFixed(2);
        
        // 净工资
        const netPay = totalEarnings - totalDeduction;
        document.getElementById('netPay').textContent = '¥' + netPay.toFixed(2);
        
        // 其他扣除
        const advanceLoan = parseFloat(document.getElementById('advance_loan').value) || 0;
        const netPayAfterOther = netPay - advanceLoan;
        document.getElementById('netPayAfterOther').textContent = '¥' + netPayAfterOther.toFixed(2);
    }

    // 为所有相关字段添加事件监听器
    const fields = ['base_salary', 'allowances', 'overtime_other', 'bonus', 'unutilised_pay_leave', 'unpaid_leave',
                    'employee_cpf', 'cdac_mbmf_sinda', 'deductions', 'tax', 'advance_loan'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', calculatePayroll);
        }
    });

    // 初始计算
    calculatePayroll();
</script>
@endsection
