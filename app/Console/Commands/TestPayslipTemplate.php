<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use App\Services\PayslipWordService;
use Exception;

class TestPayslipTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payslip:test-template {payroll_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试Word模板生成工资单功能';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $payrollId = $this->argument('payroll_id');
        
        // 如果没有提供ID，尝试获取第一个工资单
        if (!$payrollId) {
            $payroll = Payroll::with(['user.company', 'preparer', 'approver'])->first();
            if (!$payroll) {
                $this->error('未找到工资单记录，请先创建工资单数据');
                return 1;
            }
        } else {
            $payroll = Payroll::with(['user.company', 'preparer', 'approver'])->find($payrollId);
            if (!$payroll) {
                $this->error("未找到ID为 {$payrollId} 的工资单记录");
                return 1;
            }
        }
        
        $this->info("使用工资单ID: {$payroll->id}");
        $this->info("员工: {$payroll->user->name}");
        $this->info("月份: {$payroll->year}年{$payroll->month}月");
        $this->newLine();
        
        // 检查模板文件
        $templatePath = storage_path('app/templates/payslip_template.docx');
        if (!file_exists($templatePath)) {
            $this->error("模板文件不存在: {$templatePath}");
            $this->info("请将Word模板文件放置在: storage/app/templates/payslip_template.docx");
            return 1;
        }
        
        $this->info("✓ 模板文件存在: {$templatePath}");
        $this->info("文件大小: " . number_format(filesize($templatePath) / 1024, 2) . " KB");
        $this->newLine();
        
        // 测试生成Word文档
        try {
            $this->info("正在生成Word文档...");
            $wordService = new PayslipWordService();
            $wordPath = $wordService->generateWord($payroll, $templatePath);
            
            if (file_exists($wordPath)) {
                $this->info("✓ Word文档生成成功: {$wordPath}");
                $this->info("文件大小: " . number_format(filesize($wordPath) / 1024, 2) . " KB");
                $this->newLine();
                
                // 检查LibreOffice
                $this->info("检查LibreOffice...");
                $reflection = new \ReflectionClass($wordService);
                $method = $reflection->getMethod('isLibreOfficeAvailable');
                $method->setAccessible(true);
                $isAvailable = $method->invoke($wordService);
                
                if ($isAvailable) {
                    $this->info("✓ LibreOffice已安装");
                    $this->newLine();
                    
                    // 尝试转换为PDF
                    $this->info("正在转换为PDF...");
                    try {
                        $pdfPath = $wordService->convertToPdf($wordPath);
                        
                        if (file_exists($pdfPath)) {
                            $this->info("✓ PDF转换成功: {$pdfPath}");
                            $this->info("文件大小: " . number_format(filesize($pdfPath) / 1024, 2) . " KB");
                            $this->newLine();
                            $this->info("测试完成！所有功能正常。");
                            $this->info("生成的Word文件: {$wordPath}");
                            $this->info("生成的PDF文件: {$pdfPath}");
                            
                            // 询问是否清理文件
                            if ($this->confirm('是否删除测试生成的文件？', true)) {
                                @unlink($wordPath);
                                @unlink($pdfPath);
                                $this->info("测试文件已删除");
                            }
                            
                            return 0;
                        } else {
                            $this->error("PDF文件未生成");
                            return 1;
                        }
                    } catch (Exception $e) {
                        $this->error("PDF转换失败: " . $e->getMessage());
                        $this->warn("Word文档已生成，但PDF转换失败。您可以手动打开Word文件检查模板变量是否正确替换。");
                        return 1;
                    }
                } else {
                    $this->warn("LibreOffice未安装，无法转换为PDF");
                    $this->info("您可以手动打开Word文件检查模板变量是否正确替换: {$wordPath}");
                    $this->info("安装LibreOffice后，PDF转换功能将自动可用。");
                    return 0;
                }
            } else {
                $this->error("Word文档未生成");
                return 1;
            }
        } catch (Exception $e) {
            $this->error("生成Word文档失败: " . $e->getMessage());
            $this->error("错误详情: " . $e->getTraceAsString());
            return 1;
        }
    }
}


