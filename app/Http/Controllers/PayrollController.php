<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\PayslipMail;
use App\Services\PayslipWordService;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyId = $this->getActiveCompanyId();
        
        if (!$companyId) {
            return redirect()->route('companies.index')
                ->with('error', '请先选择要管理的公司');
        }
        
        // 只显示当前选择公司的薪资记录
        $payrolls = Payroll::with('user')
            ->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);

        return view('payrolls.index', compact('payrolls'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companyId = $this->getActiveCompanyId();
        
        if (!$companyId) {
            return redirect()->route('companies.index')
                ->with('error', '请先选择要管理的公司');
        }
        
        // 只显示当前选择公司的用户
        $users = User::where('company_id', $companyId)->get();
        return view('payrolls.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            // 收入项
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0|default:0',
            'overtime_other' => 'nullable|numeric|min:0|default:0',
            'bonus' => 'nullable|numeric|min:0|default:0',
            'unutilised_pay_leave' => 'nullable|numeric|min:0|default:0',
            'unpaid_leave' => 'nullable|numeric|min:0|default:0',
            // 扣除项
            'employee_cpf' => 'nullable|numeric|min:0|default:0',
            'cdac_mbmf_sinda' => 'nullable|numeric|min:0|default:0',
            'deductions' => 'nullable|numeric|min:0|default:0',
            'tax' => 'nullable|numeric|min:0|default:0',
            // 其他扣除
            'advance_loan' => 'nullable|numeric|min:0|default:0',
            // 雇主贡献
            'employer_cpf' => 'nullable|numeric|min:0|default:0',
            'sdl' => 'nullable|numeric|min:0|default:0',
            // 银行信息
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'credit_date' => 'nullable|date',
            // 其他
            'notes' => 'nullable|string|max:1000',
        ]);

        // 检查用户是否属于当前选择公司（管理员可以为任何公司的员工创建）
        $companyId = $this->getActiveCompanyId();
        $targetUser = User::findOrFail($validated['user_id']);
        if (!$user->isAdmin() && ($targetUser->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return back()->withErrors(['user_id' => '您只能为当前选择公司的员工创建薪资记录'])
                ->withInput();
        }

        // 检查该员工该月份是否已有记录
        $exists = Payroll::where('user_id', $validated['user_id'])
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['month' => '该员工该月份的薪资记录已存在'])
                ->withInput();
        }

        // 计算总收入
        $validated['total_earnings'] = ($validated['base_salary'] ?? 0)
            + ($validated['allowances'] ?? 0)
            + ($validated['overtime_other'] ?? 0)
            + ($validated['bonus'] ?? 0)
            + ($validated['unutilised_pay_leave'] ?? 0)
            - ($validated['unpaid_leave'] ?? 0);

        // 计算总扣除
        $validated['total_deduction'] = ($validated['employee_cpf'] ?? 0)
            + ($validated['cdac_mbmf_sinda'] ?? 0)
            + ($validated['deductions'] ?? 0)
            + ($validated['tax'] ?? 0);

        // 计算净工资
        $validated['net_pay'] = $validated['total_earnings'] - $validated['total_deduction'];

        // 计算其他扣除后的净工资（实际发放金额）
        $validated['net_pay_after_other_deduction'] = $validated['net_pay'] - ($validated['advance_loan'] ?? 0);

        // total_amount 等于最终发放金额
        $validated['total_amount'] = $validated['net_pay_after_other_deduction'];
        
        // 设置准备人
        $validated['prepared_by'] = Auth::id();
        
        $validated['status'] = 'pending';

        Payroll::create($validated);

        return redirect()->route('payrolls.index')
            ->with('success', '薪资记录已创建');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payroll  $payroll
     * @return \Illuminate\Http\Response
     */
    public function show(Payroll $payroll)
    {
        $payroll->load(['user.company', 'preparer', 'approver']);
        $user = Auth::user();
        
        // 检查权限：管理员可以查看任何公司的薪资记录，其他用户只能查看当前选择公司的
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($payroll->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('payrolls.index')
                ->with('error', '您无权查看该薪资记录');
        }
        
        return view('payrolls.show', compact('payroll'));
    }

    /**
     * 下载工资单PDF
     */
    public function download(Payroll $payroll)
    {
        $user = Auth::user();
        
        // 检查权限：管理员可以下载任何公司的薪资记录，其他用户只能下载当前选择公司的
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($payroll->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            abort(403, '无权下载此工资单');
        }
        
        $payroll->load(['user.company', 'preparer', 'approver']);
        
        // 尝试使用Word模板生成
        $templatePath = storage_path('app/templates/payslip_template.docx');
        
        if (file_exists($templatePath)) {
            try {
                $wordService = new PayslipWordService();
                
                // 1. 先生成 Word 文档
                $wordPath = $wordService->generateWord($payroll, $templatePath);
                
                try {
                    // 2. 尝试转换为 PDF
                    $pdfPath = $wordService->convertToPdf($wordPath);
                    
                    if (file_exists($pdfPath)) {
                        $filename = 'Payslip_' . $payroll->year . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $payroll->user->name) . '.pdf';
                        // 转换成功，下载 PDF
                        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
                    }
                } catch (\Exception $e) {
                    // 3. 转换 PDF 失败 (没有安装 Office/LibreOffice 或未配置 API)
                    // 降级方案：直接下载 Word 文档，并提示用户
                    \Log::warning('Word转PDF失败，降级为下载Word文档: ' . $e->getMessage());
                    
                    $filename = 'Payslip_' . $payroll->year . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $payroll->user->name) . '.docx';
                    
                    return response()->download($wordPath, $filename)->deleteFileAfterSend(true);
                }
                
            } catch (\Exception $e) {
                // Word 生成本身失败
                \Log::warning('Word模板生成失败，使用HTML备用方案: ' . $e->getMessage());
            }
        }
        
        // 备用方案：使用HTML视图生成PDF
        $pdf = Pdf::loadView('portal.payrolls.pdf', compact('payroll'));
        $pdf->setOption('enable-unicode', true);
        $pdf->setOption('default-font', 'DejaVu Sans');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 60);
        $pdf->setOption('margin-right', 60);
        $pdf->getDomPDF()->setHttpContext(stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]));
        
        // 设置文件名（使用ASCII字符以确保兼容性）
        $filename = 'Payslip_' . $payroll->year . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $payroll->user->name) . '.pdf';
        
        // 返回PDF下载
        return $pdf->download($filename);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payroll  $payroll
     * @return \Illuminate\Http\Response
     */
    public function edit(Payroll $payroll)
    {
        $payroll->load('user');
        $user = Auth::user();
        
        // 检查权限：管理员可以编辑任何公司的薪资记录，其他用户只能编辑当前选择公司的
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($payroll->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('payrolls.index')
                ->with('error', '您无权编辑该薪资记录');
        }
        
        // 只能编辑未发放的薪资
        if ($payroll->status === 'paid') {
            return redirect()->route('payrolls.index')
                ->with('error', '已发放的薪资不能编辑');
        }

        // 只显示当前选择公司的用户
        $companyId = $this->getActiveCompanyId();
        $users = User::where('company_id', $companyId)->get();
        return view('payrolls.edit', compact('payroll', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payroll  $payroll
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payroll $payroll)
    {
        $payroll->load('user');
        $user = Auth::user();
        
        // 检查权限：管理员可以更新任何公司的薪资记录，其他用户只能更新同一公司的
        if (!$user->isAdmin() && $payroll->user->company_id !== $user->company_id) {
            return redirect()->route('payrolls.index')
                ->with('error', '您无权编辑该薪资记录');
        }
        
        // 只能更新未发放的薪资
        if ($payroll->status === 'paid') {
            return redirect()->route('payrolls.index')
                ->with('error', '已发放的薪资不能编辑');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            // 收入项
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0|default:0',
            'overtime_other' => 'nullable|numeric|min:0|default:0',
            'bonus' => 'nullable|numeric|min:0|default:0',
            'unutilised_pay_leave' => 'nullable|numeric|min:0|default:0',
            'unpaid_leave' => 'nullable|numeric|min:0|default:0',
            // 扣除项
            'employee_cpf' => 'nullable|numeric|min:0|default:0',
            'cdac_mbmf_sinda' => 'nullable|numeric|min:0|default:0',
            'deductions' => 'nullable|numeric|min:0|default:0',
            'tax' => 'nullable|numeric|min:0|default:0',
            // 其他扣除
            'advance_loan' => 'nullable|numeric|min:0|default:0',
            // 雇主贡献
            'employer_cpf' => 'nullable|numeric|min:0|default:0',
            'sdl' => 'nullable|numeric|min:0|default:0',
            // 银行信息
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'credit_date' => 'nullable|date',
            // 其他
            'notes' => 'nullable|string|max:1000',
        ]);

        // 检查用户是否属于当前选择公司（管理员可以为任何公司的员工创建）
        $companyId = $this->getActiveCompanyId();
        $targetUser = User::findOrFail($validated['user_id']);
        if (!$user->isAdmin() && ($targetUser->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return back()->withErrors(['user_id' => '您只能为当前选择公司的员工创建薪资记录'])
                ->withInput();
        }

        // 检查该员工该月份是否已有其他记录
        $exists = Payroll::where('user_id', $validated['user_id'])
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->where('id', '!=', $payroll->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['month' => '该员工该月份的薪资记录已存在'])
                ->withInput();
        }

        // 计算总收入
        $validated['total_earnings'] = ($validated['base_salary'] ?? 0)
            + ($validated['allowances'] ?? 0)
            + ($validated['overtime_other'] ?? 0)
            + ($validated['bonus'] ?? 0)
            + ($validated['unutilised_pay_leave'] ?? 0)
            - ($validated['unpaid_leave'] ?? 0);

        // 计算总扣除
        $validated['total_deduction'] = ($validated['employee_cpf'] ?? 0)
            + ($validated['cdac_mbmf_sinda'] ?? 0)
            + ($validated['deductions'] ?? 0)
            + ($validated['tax'] ?? 0);

        // 计算净工资
        $validated['net_pay'] = $validated['total_earnings'] - $validated['total_deduction'];

        // 计算其他扣除后的净工资（实际发放金额）
        $validated['net_pay_after_other_deduction'] = $validated['net_pay'] - ($validated['advance_loan'] ?? 0);

        // total_amount 等于最终发放金额
        $validated['total_amount'] = $validated['net_pay_after_other_deduction'];
        
        // 如果prepared_by为空，设置为当前用户
        if (empty($payroll->prepared_by)) {
            $validated['prepared_by'] = Auth::id();
        }

        $payroll->update($validated);

        return redirect()->route('payrolls.index')
            ->with('success', '薪资记录已更新');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payroll  $payroll
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payroll $payroll)
    {
        $payroll->load('user');
        $user = Auth::user();
        
        // 检查权限：管理员可以删除任何公司的薪资记录，其他用户只能删除同一公司的
        if (!$user->isAdmin() && $payroll->user->company_id !== $user->company_id) {
            return redirect()->route('payrolls.index')
                ->with('error', '您无权删除该薪资记录');
        }
        
        // 只能删除未发放的薪资
        if ($payroll->status === 'paid') {
            return redirect()->route('payrolls.index')
                ->with('error', '已发放的薪资不能删除');
        }

        $payroll->delete();

        return redirect()->route('payrolls.index')
            ->with('success', '薪资记录已删除');
    }

    /**
     * 标记薪资为已发放
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payroll  $payroll
     * @return \Illuminate\Http\Response
     */
    public function markAsPaid(Request $request, Payroll $payroll)
    {
        $payroll->load('user');
        $user = Auth::user();
        
        // 检查权限：管理员可以标记任何公司的薪资记录，其他用户只能标记当前选择公司的
        $companyId = $this->getActiveCompanyId();
        if (!$user->isAdmin() && ($payroll->user->company_id !== $companyId || !$this->hasAccessToCompany($companyId))) {
            return redirect()->route('payrolls.index')
                ->with('error', '您无权操作该薪资记录');
        }

        if ($payroll->status === 'paid') {
            return redirect()->route('payrolls.index')
                ->with('error', '该薪资已标记为已发放');
        }

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // 刷新payroll数据以获取最新状态
        $payroll->refresh();

        // 发送工资单邮件到员工邮箱
        try {
            $payroll->load(['user.company', 'preparer', 'approver']);
            Mail::to($payroll->user->email)->send(new PayslipMail($payroll));
            $message = '薪资已标记为已发放，工资单已发送到员工邮箱';
        } catch (\Exception $e) {
            // 如果邮件发送失败，仍然标记为已发放，但记录错误
            \Log::error('发送工资单邮件失败: ' . $e->getMessage());
            $message = '薪资已标记为已发放，但邮件发送失败: ' . $e->getMessage();
        }

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', $message);
    }

    /**
     * 显示月度工资核算页面（类似Excel的表格界面）
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monthlyCalculation(Request $request)
    {
        $companyId = $this->getActiveCompanyId();
        
        if (!$companyId) {
            return redirect()->route('companies.index')
                ->with('error', '请先选择要管理的公司');
        }

        // 获取年份和月份，默认当前年月
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));

        // 获取所有员工
        $employees = User::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        // 获取已有的工资记录
        $existingPayrolls = Payroll::whereIn('user_id', $employees->pluck('id'))
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->keyBy('user_id');

        // 检查当月是否已锁定
        $isLocked = Payroll::whereIn('user_id', $employees->pluck('id'))
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', 'locked')
            ->exists();

        return view('payrolls.monthly-calculation', compact('employees', 'existingPayrolls', 'year', 'month', 'isLocked'));
    }

    /**
     * 批量保存月度工资记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function batchStore(Request $request)
    {
        $companyId = $this->getActiveCompanyId();
        
        if (!$companyId) {
            return redirect()->route('companies.index')
                ->with('error', '请先选择要管理的公司');
        }

        $year = $request->input('year');
        $month = $request->input('month');
        $payrolls = $request->input('payrolls', []);
        $employees = $request->input('employees', []);

        $user = Auth::user();
        $successCount = 0;
        $errorCount = 0;

        // 先更新员工信息
        foreach ($employees as $userId => $employeeData) {
            try {
                $targetUser = User::find($userId);
                if (!$targetUser || $targetUser->company_id !== $companyId) {
                    continue;
                }

                // 更新员工信息字段
                $updateData = [];
                if (isset($employeeData['commencement_date']) && !empty($employeeData['commencement_date'])) {
                    // 处理日期格式 DD.MM.YY 转换为 YYYY-MM-DD
                    $date = $employeeData['commencement_date'];
                    if (preg_match('/(\d{2})\.(\d{2})\.(\d{2})/', $date, $matches)) {
                        $day = $matches[1];
                        $dateMonth = $matches[2];
                        $dateYear = '20' . $matches[3]; // 假设是20XX年
                        $updateData['commencement_date'] = "$dateYear-$dateMonth-$day";
                    }
                }
                if (isset($employeeData['last_date']) && !empty($employeeData['last_date'])) {
                    $updateData['last_date'] = $employeeData['last_date'];
                }
                if (isset($employeeData['sex']) && in_array($employeeData['sex'], ['M', 'F'])) {
                    $updateData['sex'] = $employeeData['sex'];
                }
                if (isset($employeeData['position'])) {
                    $updateData['position'] = $employeeData['position'];
                }
                if (isset($employeeData['nric_fin'])) {
                    $updateData['nric_fin'] = $employeeData['nric_fin'];
                }

                if (!empty($updateData)) {
                    $targetUser->update($updateData);
                }
            } catch (\Exception $e) {
                // 静默处理错误，继续处理其他数据
            }
        }

        foreach ($payrolls as $userId => $data) {
            try {
                // 验证用户是否属于当前公司
                $targetUser = User::find($userId);
                if (!$targetUser || $targetUser->company_id !== $companyId) {
                    $errorCount++;
                    continue;
                }

                // 检查是否已存在该月的记录
                $existingPayroll = Payroll::where('user_id', $userId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                // 计算各项金额
                $baseSalary = floatval($data['base_salary'] ?? 0);
                $allowances = floatval($data['allowances'] ?? 0);
                $overtimeOther = floatval($data['overtime_other'] ?? 0);
                $bonus = floatval($data['bonus'] ?? 0);
                $unutilisedPayLeave = floatval($data['unutilised_pay_leave'] ?? 0);
                $unpaidLeave = floatval($data['unpaid_leave'] ?? 0);

                $employeeCpf = floatval($data['employee_cpf'] ?? 0);
                $cdacMbmfSinda = floatval($data['cdac_mbmf_sinda'] ?? 0);
                $deductions = floatval($data['deductions'] ?? 0);
                $tax = floatval($data['tax'] ?? 0);
                $advanceLoan = floatval($data['advance_loan'] ?? 0);

                $employerCpf = floatval($data['employer_cpf'] ?? 0);
                $sdl = floatval($data['sdl'] ?? 0);

                // 计算总收入
                $totalEarnings = $baseSalary + $allowances + $overtimeOther + $bonus + $unutilisedPayLeave - $unpaidLeave;

                // 计算总扣除
                $totalDeduction = $employeeCpf + $cdacMbmfSinda + $deductions + $tax;

                // 计算净工资
                $netPay = $totalEarnings - $totalDeduction;

                // 计算其他扣除后的净工资
                $netPayAfterOtherDeduction = $netPay - $advanceLoan;

                $payrollData = [
                    'user_id' => $userId,
                    'year' => $year,
                    'month' => $month,
                    'base_salary' => $baseSalary,
                    'allowances' => $allowances,
                    'overtime_other' => $overtimeOther,
                    'bonus' => $bonus,
                    'unutilised_pay_leave' => $unutilisedPayLeave,
                    'unpaid_leave' => $unpaidLeave,
                    'total_earnings' => $totalEarnings,
                    'employee_cpf' => $employeeCpf,
                    'cdac_mbmf_sinda' => $cdacMbmfSinda,
                    'deductions' => $deductions,
                    'tax' => $tax,
                    'total_deduction' => $totalDeduction,
                    'net_pay' => $netPay,
                    'advance_loan' => $advanceLoan,
                    'net_pay_after_other_deduction' => $netPayAfterOtherDeduction,
                    'employer_cpf' => $employerCpf,
                    'sdl' => $sdl,
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_account_number' => $data['bank_account_number'] ?? null,
                    'credit_date' => !empty($data['credit_date']) ? $data['credit_date'] : null,
                    'notes' => $data['notes'] ?? null,
                    'prepared_by' => $user->id,
                    'status' => $existingPayroll && $existingPayroll->status === 'paid' ? 'paid' : 'pending',
                ];

                if ($existingPayroll) {
                    // 如果已存在，更新记录
                    $existingPayroll->update($payrollData);
                } else {
                    // 创建新记录
                    Payroll::create($payrollData);
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error('批量保存工资记录失败', [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($errorCount > 0) {
            return back()->with('warning', "成功保存 {$successCount} 条记录，{$errorCount} 条记录保存失败");
        }

        return back()->with('success', "成功保存 {$successCount} 条工资记录");
    }

    /**
     * 显示Word模板上传页面
     *
     * @return \Illuminate\Http\Response
     */
    public function showTemplateUpload()
    {
        $user = Auth::user();
        
        // 只有HR和管理员可以上传模板
        if (!$user->isHR() && !$user->isAdmin()) {
            abort(403, '无权访问此页面');
        }

        $templatePath = storage_path('app/templates/payslip_template.docx');
        $templateExists = file_exists($templatePath);
        $templateInfo = null;
        
        if ($templateExists) {
            $templateInfo = [
                'size' => filesize($templatePath),
                'modified' => filemtime($templatePath),
                'path' => $templatePath,
            ];
        }

        return view('payrolls.template-upload', compact('templateExists', 'templateInfo'));
    }

    /**
     * 处理Word模板上传
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadTemplate(Request $request)
    {
        $user = Auth::user();
        
        // 只有HR和管理员可以上传模板
        if (!$user->isHR() && !$user->isAdmin()) {
            abort(403, '无权上传模板');
        }

        $validated = $request->validate([
            'template' => 'required|file|mimes:docx|max:10240', // 最大10MB，只允许.docx文件
        ]);

        try {
            // 确保模板目录存在
            $templateDir = storage_path('app/templates');
            if (!file_exists($templateDir)) {
                mkdir($templateDir, 0755, true);
            }

            // 保存文件
            $templatePath = $templateDir . '/payslip_template.docx';
            
            // 如果已存在模板，先备份（可选）
            if (file_exists($templatePath)) {
                $backupPath = $templateDir . '/payslip_template_backup_' . date('YmdHis') . '.docx';
                copy($templatePath, $backupPath);
            }

            // 移动上传的文件
            $file = $request->file('template');
            $file->move($templateDir, 'payslip_template.docx');

            return redirect()->route('payrolls.template-upload')
                ->with('success', 'Word模板上传成功！系统将使用此模板生成工资单PDF。');
        } catch (\Exception $e) {
            \Log::error('上传Word模板失败: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', '上传失败：' . $e->getMessage());
        }
    }

    /**
     * 删除Word模板
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteTemplate()
    {
        $user = Auth::user();
        
        // 只有HR和管理员可以删除模板
        if (!$user->isHR() && !$user->isAdmin()) {
            abort(403, '无权删除模板');
        }

        $templatePath = storage_path('app/templates/payslip_template.docx');
        
        if (file_exists($templatePath)) {
            try {
                unlink($templatePath);
                return redirect()->route('payrolls.template-upload')
                    ->with('success', 'Word模板已删除。系统将使用HTML方式生成PDF。');
            } catch (\Exception $e) {
                \Log::error('删除Word模板失败: ' . $e->getMessage());
                return back()->with('error', '删除失败：' . $e->getMessage());
            }
        }

        return back()->with('error', '模板文件不存在');
    }

    /**
     * 将上个月的工资数据滚存到当月
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function rollOver(Request $request) 
    {
        $companyId = $this->getActiveCompanyId();
        if (!$companyId) {
            return back()->with('error', '请先选择公司');
        }

        $year = $request->input('year');
        $month = $request->input('month');

        // 计算上个月
        $prevYear = $year;
        $prevMonth = $month - 1;
        if ($prevMonth == 0) {
            $prevMonth = 12;
            $prevYear--;
        }

        // 检查当月是否已锁定
        $isLocked = Payroll::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->where('year', $year)
        ->where('month', $month)
        ->where('status', 'locked')
        ->exists();

        if ($isLocked) {
            return back()->with('error', '当月工资已锁定，无法重新计算');
        }

        // 获取上个月的工资记录
        $prevPayrolls = Payroll::with('user')
            ->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('year', $prevYear)
            ->where('month', $prevMonth)
            ->get();

        if ($prevPayrolls->isEmpty()) {
            return back()->with('warning', '上个月没有工资记录，无法滚存');
        }

        $count = 0;
        foreach ($prevPayrolls as $prev) {
            // 检查当月是否已有记录
            $exists = Payroll::where('user_id', $prev->user_id)
                ->where('year', $year)
                ->where('month', $month)
                ->exists();

            if (!$exists) {
                // 创建新记录，只复制基础工资和固定信息，重置变动项
                Payroll::create([
                    'user_id' => $prev->user_id,
                    'year' => $year,
                    'month' => $month,
                    'base_salary' => $prev->base_salary,
                    'allowances' => $prev->allowances, // 假设津贴是固定的
                    'employer_cpf' => $prev->employer_cpf, // 假设CPF计算方式类似，后续可能需要重新计算逻辑
                    'sdl' => $prev->sdl,
                    'bank_name' => $prev->bank_name,
                    'bank_account_number' => $prev->bank_account_number,
                    'prepared_by' => Auth::id(),
                    'status' => 'pending',
                    // 以下变动项重置为0
                    'overtime_other' => 0,
                    'bonus' => 0,
                    'unutilised_pay_leave' => 0,
                    'unpaid_leave' => 0,
                    'total_earnings' => $prev->base_salary + $prev->allowances, // 初始估算
                    'deductions' => 0,
                    'tax' => 0,
                    'advance_loan' => 0,
                    // 其他字段根据需要初始化
                ]);
                $count++;
            }
        }

        return back()->with('success', "成功从上月滚存 {$count} 条记录");
    }

    /**
     * 锁定当月工资
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lockMonth(Request $request)
    {
        $companyId = $this->getActiveCompanyId();
        if (!$companyId) {
            return back()->with('error', '请先选择公司');
        }

        $year = $request->input('year');
        $month = $request->input('month');

        // 更新状态为 locked
        $affected = Payroll::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->where('year', $year)
        ->where('month', $month)
        ->where('status', '!=', 'locked')
        ->update(['status' => 'locked']);

        return back()->with('success', "成功锁定 {$affected} 条工资记录");
    }

    /**
     * 清空当月工资记录
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function clearMonth(Request $request)
    {
        $companyId = $this->getActiveCompanyId();
        if (!$companyId) {
            return back()->with('error', '请先选择公司');
        }

        $year = $request->input('year');
        $month = $request->input('month');

        // 检查当月是否已锁定
        $isLocked = Payroll::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->where('year', $year)
        ->where('month', $month)
        ->where('status', 'locked')
        ->exists();

        if ($isLocked) {
            return back()->with('error', '当月工资已锁定，无法清空');
        }

        // 删除非 paid 的记录
        $deleted = Payroll::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->where('year', $year)
        ->where('month', $month)
        ->where('status', '!=', 'paid') // 防止误删已支付记录
        ->delete();

        return back()->with('success', "成功清空 {$deleted} 条工资记录");
    }
}

