<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PayslipWordService;

class EmployeePayrollController extends Controller
{
    /**
     * 显示员工的所有工资单
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        $query = $user->payrolls();
        
        // 对于当月工资单，只有HR完成月度核算（prepared_by不为空）后才可见
        // 非当月的工资单正常显示（历史数据通常都已核算完成）
        $query->where(function($q) use ($currentYear, $currentMonth) {
            // 非当月的工资单，直接显示
            $q->where(function($subQ) use ($currentYear, $currentMonth) {
                $subQ->where('year', '!=', $currentYear)
                     ->orWhere('month', '!=', $currentMonth);
            })
            // 当月的工资单，必须prepared_by不为空（表示HR已核算）
            ->orWhere(function($subQ) use ($currentYear, $currentMonth) {
                $subQ->where('year', $currentYear)
                     ->where('month', $currentMonth)
                     ->whereNotNull('prepared_by');
            });
        });
        
        // 年份筛选
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        // 月份筛选
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        
        // 状态筛选
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $payrolls = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);
        
        // 获取所有有工资单的年份，用于筛选（只统计已核算的）
        $years = $user->payrolls()
            ->whereNotNull('prepared_by')
            ->distinct()
            ->pluck('year')
            ->sort()
            ->reverse()
            ->values();
        
        return view('portal.payrolls.index', compact('payrolls', 'years'));
    }

    /**
     * 显示工资单详情
     */
    public function show(Payroll $payroll)
    {
        // 确保只能查看自己的工资单
        if ($payroll->user_id !== Auth::id()) {
            abort(403, '无权查看此工资单');
        }
        
        // 检查当月工资单是否已核算
        $currentYear = date('Y');
        $currentMonth = date('n');
        if ($payroll->year == $currentYear && $payroll->month == $currentMonth) {
            if (is_null($payroll->prepared_by)) {
                abort(403, '该月工资单尚未完成核算，暂不可查看');
            }
        }
        
        $payroll->load('user');
        return view('portal.payrolls.show', compact('payroll'));
    }

    /**
     * 下载工资单PDF
     */
    public function download(Payroll $payroll)
    {
        // 确保只能下载自己的工资单
        if ($payroll->user_id !== Auth::id()) {
            abort(403, '无权下载此工资单');
        }
        
        // 检查当月工资单是否已核算
        $currentYear = date('Y');
        $currentMonth = date('n');
        if ($payroll->year == $currentYear && $payroll->month == $currentMonth) {
            if (is_null($payroll->prepared_by)) {
                abort(403, '该月工资单尚未完成核算，暂不可下载');
            }
        }
        
        $payroll->load(['user.company', 'preparer', 'approver']);
        
        // 尝试使用Word模板生成PDF
        $templatePath = storage_path('app/templates/payslip_template.docx');
        
        if (file_exists($templatePath)) {
            try {
                $wordService = new PayslipWordService();
                $pdfPath = $wordService->generatePdf($payroll, $templatePath);
                
                if (file_exists($pdfPath)) {
                    $filename = 'Payslip_' . $payroll->year . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $payroll->user->name) . '.pdf';
                    
                    // 返回PDF下载
                    return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
                }
            } catch (\Exception $e) {
                // 如果Word模板方式失败，回退到HTML方式
                \Log::warning('Word模板生成PDF失败，使用HTML备用方案: ' . $e->getMessage());
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
}
