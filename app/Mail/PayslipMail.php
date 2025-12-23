<?php

namespace App\Mail;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PayslipWordService;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payroll;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Payroll  $payroll
     * @return void
     */
    public function __construct(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $payroll = $this->payroll;
        $payroll->load(['user.company', 'preparer', 'approver']);
        
        $filename = 'Payslip_' . $payroll->year . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $payroll->user->name) . '.pdf';
        $monthName = \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('M Y');
        
        // 尝试使用Word模板生成PDF
        $templatePath = storage_path('app/templates/payslip_template.docx');
        $pdfContent = null;
        $pdfPath = null;
        
        if (file_exists($templatePath)) {
            try {
                $wordService = new PayslipWordService();
                $pdfPath = $wordService->generatePdf($payroll, $templatePath);
                
                if (file_exists($pdfPath)) {
                    $pdfContent = file_get_contents($pdfPath);
                    // 清理临时文件
                    File::delete($pdfPath);
                }
            } catch (\Exception $e) {
                // 如果Word模板方式失败，回退到HTML方式
                \Log::warning('Word模板生成PDF失败（邮件），使用HTML备用方案: ' . $e->getMessage());
            }
        }
        
        // 备用方案：使用HTML视图生成PDF
        if (!$pdfContent) {
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
            
            $pdfContent = $pdf->output();
        }
        
        // 获取自定义邮件内容（可从配置或环境变量读取）
        $customMessage = config('mail.payslip_message', '');
        
        // 替换占位符
        if (!empty($customMessage)) {
            $customMessage = str_replace(
                ['{name}', '{year}', '{month}', '{company}'],
                [
                    $payroll->user->name,
                    $payroll->year,
                    $payroll->month,
                    $payroll->user->company->name ?? ''
                ],
                $customMessage
            );
        }
        
        return $this->subject('工资单 - Payslip for ' . strtoupper($monthName))
                    ->view('emails.payslip', compact('payroll', 'customMessage'))
                    ->attachData($pdfContent, $filename, [
                        'mime' => 'application/pdf',
                    ]);
    }
}
