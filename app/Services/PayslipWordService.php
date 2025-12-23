<?php

namespace App\Services;

use App\Models\Payroll;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PayslipWordService
{
    /**
     * 生成工资单Word文档
     *
     * @param Payroll $payroll
     * @param string $templatePath Word模板路径
     * @return string 生成的Word文件路径
     */
    public function generateWord(Payroll $payroll, string $templatePath = null): string
    {
        // 加载工资单关联数据
        $payroll->load(['user.company', 'preparer', 'approver']);
        
        // 如果没有提供模板路径，使用默认模板
        if (!$templatePath) {
            $templatePath = storage_path('app/templates/payslip_template.docx');
        }
        
        // 检查模板文件是否存在
        if (!file_exists($templatePath)) {
            throw new \Exception("Word模板文件不存在: {$templatePath}");
        }
        
        // 创建模板处理器
        $templateProcessor = new TemplateProcessor($templatePath);
        
        // 准备数据
        $data = $this->prepareData($payroll);
        
        // 替换模板变量
        foreach ($data as $key => $value) {
            try {
                $templateProcessor->setValue($key, $value ?? '');
            } catch (\Exception $e) {
                Log::warning("设置模板变量 {$key} 失败: " . $e->getMessage());
            }
        }
        
        // 保存生成的Word文档
        $outputDir = storage_path('app/temp');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        $filename = 'payslip_' . $payroll->id . '_' . time() . '.docx';
        $outputPath = $outputDir . '/' . $filename;
        
        $templateProcessor->saveAs($outputPath);
        
        return $outputPath;
    }
    
    /**
     * 将Word文档转换为PDF
     *
     * @param string $wordPath Word文件路径
     * @return string PDF文件路径
     */
    public function convertToPdf(string $wordPath): string
    {
        // 检查Word文件是否存在
        if (!file_exists($wordPath)) {
            throw new \Exception("Word文件不存在: {$wordPath}");
        }
        
        // 方法1：尝试使用CloudConvert API（如果配置了API密钥）
        if ($this->isCloudConvertAvailable()) {
            try {
                return $this->convertWithCloudConvert($wordPath);
            } catch (\Exception $e) {
                Log::warning("CloudConvert转换失败，尝试其他方法: " . $e->getMessage());
            }
        }
        
        // 方法2：尝试使用Microsoft Word COM对象（Windows only，需要安装Office）
        if (PHP_OS_FAMILY === 'Windows' && $this->isMicrosoftWordAvailable()) {
            try {
                return $this->convertWithMicrosoftWord($wordPath);
            } catch (\Exception $e) {
                Log::warning("Microsoft Word转换失败，尝试其他方法: " . $e->getMessage());
            }
        }
        
        // 方法3：尝试使用LibreOffice转换（如果可用）
        if ($this->isLibreOfficeAvailable()) {
            try {
                return $this->convertWithLibreOffice($wordPath);
            } catch (\Exception $e) {
                Log::warning("LibreOffice转换失败: " . $e->getMessage());
            }
        }
        
        // 如果所有方法都不可用，抛出异常
        throw new \Exception("无法将Word转换为PDF。请配置CloudConvert API密钥，或安装Microsoft Office/LibreOffice，或使用HTML方式生成PDF。");
    }
    
    /**
     * 检查Microsoft Word是否可用（Windows only）
     *
     * @return bool
     */
    protected function isMicrosoftWordAvailable(): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return false;
        }
        
        // 检查是否可以使用COM对象
        if (!extension_loaded('com_dotnet')) {
            return false;
        }
        
        // 尝试创建Word应用程序对象（不实际启动Word，只检查是否可用）
        try {
            $word = new \COM("Word.Application", null, CP_UTF8);
            if ($word) {
                $word->Quit(false);
                $word = null;
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return false;
    }
    
    /**
     * 使用Microsoft Word将Word转换为PDF（Windows only）
     *
     * @param string $wordPath
     * @return string PDF文件路径
     */
    protected function convertWithMicrosoftWord(string $wordPath): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            throw new \Exception("Microsoft Word转换仅支持Windows系统");
        }
        
        if (!extension_loaded('com_dotnet')) {
            throw new \Exception("PHP COM扩展未启用");
        }
        
        $pdfPath = str_replace('.docx', '.pdf', $wordPath);
        $wordPath = str_replace('/', '\\', realpath($wordPath));
        $pdfPath = str_replace('/', '\\', $pdfPath);
        
        try {
            // 创建Word应用程序对象
            $word = new \COM("Word.Application", null, CP_UTF8);
            $word->Visible = false;
            $word->DisplayAlerts = 0; // wdAlertsNone
            
            // 打开Word文档
            $doc = $word->Documents->Open($wordPath, false, true); // ReadOnly, ConfirmConversions
            
            // 另存为PDF (格式代码17 = wdFormatPDF)
            $doc->SaveAs2($pdfPath, 17); // wdFormatPDF = 17
            
            // 关闭文档和Word
            $doc->Close(false);
            $word->Quit(false);
            
            // 释放COM对象
            $doc = null;
            $word = null;
            
            // 检查PDF文件是否生成
            if (!file_exists($pdfPath)) {
                throw new \Exception("PDF文件未生成");
            }
            
            return $pdfPath;
        } catch (\Exception $e) {
            // 清理COM对象
            if (isset($word)) {
                try {
                    $word->Quit(false);
                } catch (\Exception $ex) {
                    // 忽略清理错误
                }
            }
            throw new \Exception("Microsoft Word转换失败: " . $e->getMessage());
        }
    }
    
    /**
     * 检查CloudConvert API是否可用
     *
     * @return bool
     */
    protected function isCloudConvertAvailable(): bool
    {
        $apiKey = config('services.cloudconvert.api_key') ?? env('CLOUDCONVERT_API_KEY');
        return !empty($apiKey);
    }
    
    /**
     * 使用CloudConvert API将Word转换为PDF
     *
     * @param string $wordPath
     * @return string PDF文件路径
     */
    protected function convertWithCloudConvert(string $wordPath): string
    {
        $apiKey = config('services.cloudconvert.api_key') ?? env('CLOUDCONVERT_API_KEY');
        
        if (empty($apiKey)) {
            throw new \Exception("CloudConvert API密钥未配置");
        }
        
        $pdfPath = str_replace('.docx', '.pdf', $wordPath);
        
        try {
            // 步骤1: 创建上传任务
            $uploadTaskResponse = $this->cloudConvertRequest('POST', '/v2/import/upload', [
                'filename' => basename($wordPath),
            ], $apiKey);
            
            $uploadUrl = $uploadTaskResponse['result']['form']['url'];
            $uploadFields = $uploadTaskResponse['result']['form']['parameters'];
            $importTaskId = $uploadTaskResponse['id'];
            
            // 步骤2: 上传文件到CloudConvert
            $fileContent = file_get_contents($wordPath);
            $this->cloudConvertUpload($uploadUrl, $uploadFields, $fileContent, basename($wordPath));
            
            // 等待上传任务完成
            $this->waitForTask($importTaskId, $apiKey);
            
            // 步骤3: 创建Job，包含转换和导出任务
            $jobResponse = $this->cloudConvertRequest('POST', '/v2/jobs', [
                'tasks' => [
                    'convert-task' => [
                        'operation' => 'convert',
                        'input' => $importTaskId,
                        'input_format' => 'docx',
                        'output_format' => 'pdf',
                    ],
                    'export-task' => [
                        'operation' => 'export/url',
                        'input' => 'convert-task',
                    ],
                ],
            ], $apiKey);
            
            $jobId = $jobResponse['id'];
            
            // 步骤4: 等待Job完成
            $this->waitForJob($jobId, $apiKey);
            
            // 步骤5: 获取导出任务的下载URL
            $exportTaskId = null;
            foreach ($jobResponse['tasks'] as $task) {
                if ($task['name'] === 'export-task' || $task['operation'] === 'export/url') {
                    $exportTaskId = $task['id'];
                    break;
                }
            }
            
            if (!$exportTaskId) {
                // 重新获取job信息以获取task IDs
                $jobInfo = $this->cloudConvertRequest('GET', "/v2/jobs/{$jobId}", [], $apiKey);
                foreach ($jobInfo['tasks'] as $task) {
                    if ($task['operation'] === 'export/url') {
                        $exportTaskId = $task['id'];
                        break;
                    }
                }
            }
            
            if (!$exportTaskId) {
                throw new \Exception("无法找到导出任务");
            }
            
            $exportTask = $this->cloudConvertRequest('GET', "/v2/tasks/{$exportTaskId}", [], $apiKey);
            $downloadUrl = $exportTask['result']['files'][0]['url'] ?? null;
            
            if (!$downloadUrl) {
                throw new \Exception("无法获取下载URL");
            }
            
            // 步骤6: 下载PDF文件到本地
            $pdfContent = file_get_contents($downloadUrl);
            file_put_contents($pdfPath, $pdfContent);
            
            if (!file_exists($pdfPath)) {
                throw new \Exception("PDF文件未生成");
            }
            
            return $pdfPath;
        } catch (\Exception $e) {
            throw new \Exception("CloudConvert转换失败: " . $e->getMessage());
        }
    }
    
    /**
     * 发送CloudConvert API请求
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param string $apiKey
     * @return array
     */
    protected function cloudConvertRequest(string $method, string $endpoint, array $data, string $apiKey): array
    {
        $baseUrl = config('services.cloudconvert.base_url', 'https://api.cloudconvert.com');
        $url = $baseUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("CloudConvert API请求失败: " . $error);
        }
        
        if ($httpCode >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? "HTTP错误: {$httpCode}";
            throw new \Exception("CloudConvert API错误: {$errorMessage}");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * 上传文件到CloudConvert
     *
     * @param string $uploadUrl
     * @param array $fields
     * @param string $fileContent
     * @param string $filename
     * @return void
     */
    protected function cloudConvertUpload(string $uploadUrl, array $fields, string $fileContent, string $filename): void
    {
        $boundary = uniqid();
        $delimiter = '-------------' . $boundary;
        
        $postData = '';
        foreach ($fields as $name => $value) {
            $postData .= "--" . $delimiter . "\r\n";
            $postData .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
            $postData .= $value . "\r\n";
        }
        
        $postData .= "--" . $delimiter . "\r\n";
        $postData .= 'Content-Disposition: form-data; name="file"; filename="' . $filename . '"' . "\r\n";
        $postData .= 'Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document' . "\r\n\r\n";
        $postData .= $fileContent . "\r\n";
        $postData .= "--" . $delimiter . "--\r\n";
        
        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: multipart/form-data; boundary=' . $delimiter,
                'Content-Length: ' . strlen($postData),
            ],
            CURLOPT_POSTFIELDS => $postData,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("文件上传失败: " . $error);
        }
        
        if ($httpCode >= 400) {
            throw new \Exception("文件上传失败: HTTP错误 {$httpCode}");
        }
    }
    
    /**
     * 等待任务完成
     *
     * @param string $taskId
     * @param string $apiKey
     * @param int $maxWaitTime 最大等待时间（秒）
     * @return void
     */
    protected function waitForTask(string $taskId, string $apiKey, int $maxWaitTime = 300): void
    {
        $startTime = time();
        
        while (true) {
            if (time() - $startTime > $maxWaitTime) {
                throw new \Exception("任务等待超时");
            }
            
            $task = $this->cloudConvertRequest('GET', "/v2/tasks/{$taskId}", [], $apiKey);
            
            $status = $task['status'] ?? 'unknown';
            
            if ($status === 'finished') {
                return;
            }
            
            if ($status === 'error') {
                $message = $task['message'] ?? '未知错误';
                throw new \Exception("任务失败: {$message}");
            }
            
            // 等待1秒后重试
            sleep(1);
        }
    }
    
    /**
     * 等待Job完成
     *
     * @param string $jobId
     * @param string $apiKey
     * @param int $maxWaitTime 最大等待时间（秒）
     * @return void
     */
    protected function waitForJob(string $jobId, string $apiKey, int $maxWaitTime = 300): void
    {
        $startTime = time();
        
        while (true) {
            if (time() - $startTime > $maxWaitTime) {
                throw new \Exception("Job等待超时");
            }
            
            $job = $this->cloudConvertRequest('GET', "/v2/jobs/{$jobId}", [], $apiKey);
            
            $status = $job['status'] ?? 'unknown';
            
            if ($status === 'finished') {
                return;
            }
            
            if ($status === 'error') {
                $message = $job['message'] ?? '未知错误';
                throw new \Exception("Job失败: {$message}");
            }
            
            // 等待1秒后重试
            sleep(1);
        }
    }
    
    /**
     * 检查LibreOffice是否可用
     *
     * @return bool
     */
    protected function isLibreOfficeAvailable(): bool
    {
        // Windows路径
        $paths = [
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return true;
            }
        }
        
        // Linux/Mac路径
        $command = 'which soffice';
        $output = shell_exec($command);
        return !empty($output);
    }
    
    /**
     * 使用LibreOffice将Word转换为PDF
     *
     * @param string $wordPath
     * @return string PDF文件路径
     */
    protected function convertWithLibreOffice(string $wordPath): string
    {
        $outputDir = dirname($wordPath);
        $pdfPath = str_replace('.docx', '.pdf', $wordPath);
        
        // Windows路径
        $sofficePaths = [
            '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"',
            '"C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe"',
        ];
        
        $sofficePath = null;
        foreach ($sofficePaths as $path) {
            $cleanPath = trim($path, '"');
            if (file_exists($cleanPath)) {
                $sofficePath = $path;
                break;
            }
        }
        
        // Linux/Mac
        if (!$sofficePath) {
            $sofficePath = 'soffice';
        }
        
        // 构建转换命令
        $command = sprintf(
            '%s --headless --convert-to pdf --outdir "%s" "%s"',
            $sofficePath,
            $outputDir,
            $wordPath
        );
        
        // 执行命令
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($pdfPath)) {
            throw new \Exception("Word转PDF失败: " . implode("\n", $output));
        }
        
        return $pdfPath;
    }
    
    /**
     * 准备模板数据
     *
     * @param Payroll $payroll
     * @return array
     */
    protected function prepareData(Payroll $payroll): array
    {
        $monthName = strtoupper(Carbon::create($payroll->year, $payroll->month, 1)->format('M Y'));
        
        return [
            // 页眉信息
            'PAGE_INFO' => 'Page 1 of 1',
            'PAYSLIP_TITLE' => 'Payslip for ' . $monthName,
            'UEN' => $payroll->user->company->code ?? '',
            'COMPANY_NAME' => $payroll->user->company->name ?? '',
            
            // 员工信息
            'EMPLOYEE_COMPANY_NAME' => $payroll->user->company->name ?? '',
            'EMPLOYEE_POSITION' => $payroll->user->position ?? '',
            'EMPLOYEE_NAME' => $payroll->user->name ?? '',
            'EMPLOYEE_NRIC_FIN' => $payroll->user->nric_fin ?? '',
            'BASIC_SALARY_DETAIL' => number_format($payroll->base_salary, 2, '.', ','),
            'COMMENCEMENT_DATE' => $payroll->user->commencement_date ? $payroll->user->commencement_date->format('d/m/Y') : '',
            'LAST_DATE' => $payroll->user->last_date ? $payroll->user->last_date->format('d/m/Y') : '',
            
            // 收入项
            'EARNINGS_BASIC_SALARY_SGD' => number_format($payroll->base_salary, 2, '.', ','),
            'EARNINGS_BASIC_SALARY_YTD' => number_format($payroll->base_salary, 2, '.', ','),
            'EARNINGS_ALLOWANCE_SGD' => number_format($payroll->allowances, 2, '.', ','),
            'EARNINGS_ALLOWANCE_YTD' => number_format($payroll->allowances, 2, '.', ','),
            'EARNINGS_OVERTIME_SGD' => number_format($payroll->overtime_other, 2, '.', ','),
            'EARNINGS_OVERTIME_YTD' => number_format($payroll->overtime_other, 2, '.', ','),
            'EARNINGS_BONUS_SGD' => number_format($payroll->bonus, 2, '.', ','),
            'EARNINGS_BONUS_YTD' => number_format($payroll->bonus, 2, '.', ','),
            'EARNINGS_UNUTILISED_LEAVE_SGD' => number_format($payroll->unutilised_pay_leave, 2, '.', ','),
            'EARNINGS_UNUTILISED_LEAVE_YTD' => number_format($payroll->unutilised_pay_leave, 2, '.', ','),
            'EARNINGS_UNPAID_LEAVE_SGD' => '(' . number_format($payroll->unpaid_leave, 2, '.', ',') . ')',
            'EARNINGS_UNPAID_LEAVE_YTD' => '(' . number_format($payroll->unpaid_leave, 2, '.', ',') . ')',
            'TOTAL_EARNINGS_SGD' => number_format($payroll->total_earnings, 2, '.', ','),
            'TOTAL_EARNINGS_YTD' => number_format($payroll->total_earnings, 2, '.', ','),
            
            // 扣除项
            'DEDUCTION_EMPLOYEE_CPF_SGD' => '(' . number_format($payroll->employee_cpf, 2, '.', ',') . ')',
            'DEDUCTION_EMPLOYEE_CPF_YTD' => '(' . number_format($payroll->employee_cpf, 2, '.', ',') . ')',
            'DEDUCTION_CDAC_SGD' => '(' . number_format($payroll->cdac_mbmf_sinda, 2, '.', ',') . ')',
            'DEDUCTION_CDAC_YTD' => '(' . number_format($payroll->cdac_mbmf_sinda, 2, '.', ',') . ')',
            'TOTAL_DEDUCTION_SGD' => '(' . number_format($payroll->total_deduction, 2, '.', ',') . ')',
            'TOTAL_DEDUCTION_YTD' => '(' . number_format($payroll->total_deduction, 2, '.', ',') . ')',
            
            // 净工资
            'NET_PAY_SGD' => number_format($payroll->net_pay, 2, '.', ','),
            'NET_PAY_YTD' => number_format($payroll->net_pay, 2, '.', ','),
            
            // 其他扣除
            'OTHER_DEDUCTION_ADVANCE_LOAN_SGD' => '(- ' . number_format(abs($payroll->advance_loan), 2, '.', ',') . ')',
            'OTHER_DEDUCTION_ADVANCE_LOAN_YTD' => '(- ' . number_format(abs($payroll->advance_loan), 2, '.', ',') . ')',
            'NET_PAY_AFTER_OTHER_DEDUCTION_SGD' => number_format($payroll->net_pay_after_other_deduction, 2, '.', ','),
            'NET_PAY_AFTER_OTHER_DEDUCTION_YTD' => number_format($payroll->net_pay_after_other_deduction, 2, '.', ','),
            
            // 雇主贡献
            'EMPLOYER_CPF_SGD' => number_format($payroll->employer_cpf, 2, '.', ','),
            'EMPLOYER_CPF_YTD' => number_format($payroll->employer_cpf, 2, '.', ','),
            'EMPLOYER_SDL_SGD' => number_format($payroll->sdl, 2, '.', ','),
            'EMPLOYER_SDL_YTD' => number_format($payroll->sdl, 2, '.', ','),
            
            // 银行信息
            'BANK_NAME' => $payroll->bank_name ?? '',
            'BANK_ACCOUNT_NUMBER' => $payroll->bank_account_number ?? '',
            'CREDIT_DATE' => $payroll->credit_date ? $payroll->credit_date->format('d/m/Y') : '',
            'BANK_CODE' => '',
            'BRANCH_CODE' => '',
            
            // 签名
            'PREPARED_BY' => $payroll->preparer->name ?? '',
            'APPROVED_BY' => $payroll->approver->name ?? '',
        ];
    }
    
    /**
     * 生成完整的PDF（Word模板 -> Word -> PDF）
     *
     * @param Payroll $payroll
     * @param string $templatePath
     * @return string PDF文件路径
     */
    public function generatePdf(Payroll $payroll, string $templatePath = null): string
    {
        // 生成Word文档
        $wordPath = $this->generateWord($payroll, $templatePath);
        
        try {
            // 转换为PDF
            $pdfPath = $this->convertToPdf($wordPath);
            
            // 可选：删除临时Word文件
            // unlink($wordPath);
            
            return $pdfPath;
        } catch (\Exception $e) {
            // 如果转换失败，清理Word文件
            if (file_exists($wordPath)) {
                unlink($wordPath);
            }
            throw $e;
        }
    }
}

