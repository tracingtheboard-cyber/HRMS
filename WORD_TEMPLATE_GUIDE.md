# Word模板生成工资单PDF使用指南

## 概述

系统现在支持使用Word模板来生成工资单PDF。这种方式可以更精确地控制格式，并且更容易修改布局。

## 快速开始

### 1. 创建Word模板文件

1. 在Word中创建一个新的 `.docx` 文档
2. 根据您的工资单格式设计模板布局
3. 在需要填充数据的位置使用 `${变量名}` 作为占位符
4. 将模板保存为 `storage/app/templates/payslip_template.docx`

### 2. 可用的模板变量

所有变量都使用 `${变量名}` 格式，注意大小写必须完全匹配。

#### 页眉信息
- `${PAGE_INFO}` - 页码信息（如 "Page 1 of 1"）
- `${PAYSLIP_TITLE}` - 工资单标题（如 "Payslip for JAN 2025"）
- `${UEN}` - 公司UEN号
- `${COMPANY_NAME}` - 公司名称

#### 员工信息
- `${EMPLOYEE_COMPANY_NAME}` - 员工公司名称
- `${EMPLOYEE_POSITION}` - 职位
- `${EMPLOYEE_NAME}` - 员工姓名（支持中文）
- `${EMPLOYEE_NRIC_FIN}` - NRIC/FIN号码
- `${BASIC_SALARY_DETAIL}` - 基本工资（就业详情）
- `${COMMENCEMENT_DATE}` - 入职日期（格式：d/m/Y，如 02/01/2019）
- `${LAST_DATE}` - 最后日期（格式：d/m/Y，如果为空则不显示）

#### 收入项 (EARNINGS)
- `${EARNINGS_BASIC_SALARY_SGD}` - 基本工资 (SGD)
- `${EARNINGS_BASIC_SALARY_YTD}` - 基本工资 (YTD)
- `${EARNINGS_ALLOWANCE_SGD}` - 津贴 (SGD)
- `${EARNINGS_ALLOWANCE_YTD}` - 津贴 (YTD)
- `${EARNINGS_OVERTIME_SGD}` - 加班/其他 (SGD)
- `${EARNINGS_OVERTIME_YTD}` - 加班/其他 (YTD)
- `${EARNINGS_BONUS_SGD}` - 奖金 (SGD)
- `${EARNINGS_BONUS_YTD}` - 奖金 (YTD)
- `${EARNINGS_UNUTILISED_LEAVE_SGD}` - 未使用带薪假期 (SGD)
- `${EARNINGS_UNUTILISED_LEAVE_YTD}` - 未使用带薪假期 (YTD)
- `${EARNINGS_UNPAID_LEAVE_SGD}` - 无薪假期 (SGD)（已包含括号，如 "(0.00)"）
- `${EARNINGS_UNPAID_LEAVE_YTD}` - 无薪假期 (YTD)（已包含括号）
- `${TOTAL_EARNINGS_SGD}` - 总收入 (SGD)
- `${TOTAL_EARNINGS_YTD}` - 总收入 (YTD)

#### 扣除项 (DEDUCTIONS)
- `${DEDUCTION_EMPLOYEE_CPF_SGD}` - 员工CPF (SGD)（已包含括号）
- `${DEDUCTION_EMPLOYEE_CPF_YTD}` - 员工CPF (YTD)（已包含括号）
- `${DEDUCTION_CDAC_SGD}` - CDAC/MBMF/SINDA基金 (SGD)（已包含括号）
- `${DEDUCTION_CDAC_YTD}` - CDAC/MBMF/SINDA基金 (YTD)（已包含括号）
- `${TOTAL_DEDUCTION_SGD}` - 总扣除 (SGD)（已包含括号）
- `${TOTAL_DEDUCTION_YTD}` - 总扣除 (YTD)（已包含括号）

#### 净工资 (NET PAY)
- `${NET_PAY_SGD}` - 净工资 (SGD)
- `${NET_PAY_YTD}` - 净工资 (YTD)

#### 其他扣除 (OTHER DEDUCTION)
- `${OTHER_DEDUCTION_ADVANCE_LOAN_SGD}` - 预支/贷款 (SGD)（已包含括号和负号，如 "(- 50.50)"）
- `${OTHER_DEDUCTION_ADVANCE_LOAN_YTD}` - 预支/贷款 (YTD)（已包含括号和负号）
- `${NET_PAY_AFTER_OTHER_DEDUCTION_SGD}` - 其他扣除后净工资 (SGD)
- `${NET_PAY_AFTER_OTHER_DEDUCTION_YTD}` - 其他扣除后净工资 (YTD)

#### 雇主贡献 (EMPLOYER CONTRIBUTIONS)
- `${EMPLOYER_CPF_SGD}` - 雇主CPF (SGD)
- `${EMPLOYER_CPF_YTD}` - 雇主CPF (YTD)
- `${EMPLOYER_SDL_SGD}` - SDL (SGD)
- `${EMPLOYER_SDL_YTD}` - SDL (YTD)

#### 银行信息
- `${BANK_NAME}` - 银行名称
- `${BANK_ACCOUNT_NUMBER}` - 账号
- `${CREDIT_DATE}` - 到账日期（格式：d/m/Y）
- `${BANK_CODE}` - 银行代码（通常为空）
- `${BRANCH_CODE}` - 分行代码（通常为空）

#### 签名
- `${PREPARED_BY}` - 准备人姓名（支持中文）
- `${APPROVED_BY}` - 审批人姓名（支持中文）

### 3. 模板示例

在Word模板中，您可以这样使用变量：

```
Payslip for ${PAYSLIP_TITLE}

UEN: ${UEN}
Company Name: ${COMPANY_NAME}

Employee Information:
Name: ${EMPLOYEE_NAME}
Position: ${EMPLOYEE_POSITION}
NRIC / FIN: ${EMPLOYEE_NRIC_FIN}

EARNINGS:
Basic Salary: ${EARNINGS_BASIC_SALARY_SGD} | ${EARNINGS_BASIC_SALARY_YTD}
Allowance: ${EARNINGS_ALLOWANCE_SGD} | ${EARNINGS_ALLOWANCE_YTD}
...

Total Earnings: ${TOTAL_EARNINGS_SGD} | ${TOTAL_EARNINGS_YTD}
```

### 4. 数值格式说明

- 所有数值已格式化为两位小数
- 包含千位分隔符（逗号），如：2,500.00
- 扣除项已包含括号，如：(500.00)
- 负数项已包含负号和括号，如：(- 50.50)

### 5. Word转PDF转换

系统按优先级尝试以下方式将Word转换为PDF：

#### 5.1 CloudConvert API（推荐）⭐

CloudConvert是一个云端文件转换服务，**无需在服务器上安装任何软件**。

**优点：**
- 无需安装任何软件
- 支持所有平台
- 转换质量高
- 可靠稳定

**配置方法：**
1. 访问 https://cloudconvert.com/ 注册账号
2. 在Dashboard中创建API密钥：https://cloudconvert.com/dashboard/api/v2/keys
3. 在 `.env` 文件中添加：
   ```env
   CLOUDCONVERT_API_KEY=your_api_key_here
   ```
4. 可选：指定API端点（默认为 https://api.cloudconvert.com）
   ```env
   CLOUDCONVERT_BASE_URL=https://api.cloudconvert.com
   ```

**注意：** CloudConvert是付费服务（有免费额度），需要注册账号并获取API密钥。详情请查看 https://cloudconvert.com/pricing

#### 5.2 Microsoft Office（Windows only）

如果服务器是Windows系统且已安装Microsoft Office，系统会自动使用。

**优点：**
- 转换质量高
- 如果已安装Office，无需额外配置

**缺点：**
- 仅支持Windows
- 需要购买并安装Microsoft Office

#### 5.3 LibreOffice（免费）

#### Windows系统安装LibreOffice

1. 访问 https://www.libreoffice.org/download/download/
2. 下载Windows版本的LibreOffice
3. 运行安装程序，使用默认安装路径
4. 系统会自动检测安装路径

#### Linux/Mac系统安装LibreOffice

- **Ubuntu/Debian**: 
  ```bash
  sudo apt-get install libreoffice
  ```
- **Mac**: 
  ```bash
  brew install --cask libreoffice
  ```

#### 验证LibreOffice安装

安装后，系统会自动检测LibreOffice是否可用。如果检测失败，可以手动检查：

- Windows: 检查 `C:\Program Files\LibreOffice\program\soffice.exe` 是否存在
- Linux/Mac: 运行 `which soffice` 命令

### 6. 模板测试

创建模板后，可以通过以下方式测试：

1. 访问系统中的工资单详情页面
2. 点击"下载工资单PDF"按钮
3. 如果模板文件存在且格式正确，系统会使用Word模板生成PDF
4. 如果模板不存在或转换失败，系统会自动使用HTML备用方案

### 7. 故障排查

#### 问题：提示"Word模板文件不存在"

**解决方案**: 
- 确保模板文件已保存为 `storage/app/templates/payslip_template.docx`
- 检查文件路径是否正确
- 确保文件权限允许读取

#### 问题：提示"LibreOffice未安装"

**解决方案**:
- 安装LibreOffice（参考上面的安装说明）
- 或者让系统使用HTML备用方案（移除模板文件即可）

#### 问题：生成的PDF格式不正确

**解决方案**:
- 检查Word模板中的变量名是否正确（注意大小写）
- 确保变量格式为 `${变量名}`，不要有额外的空格
- 检查模板文件是否损坏，尝试重新保存

#### 问题：中文字符显示不正确

**解决方案**:
- 确保Word模板使用支持中文的字体（如Microsoft YaHei、SimSun等）
- 在Word中设置字体时，确保选择了支持Unicode的字体

### 8. 备用方案

如果Word模板方式不可用，系统会自动使用HTML视图生成PDF。这种方式：
- 不需要LibreOffice
- 使用现有的 `resources/views/portal/payrolls/pdf.blade.php` 视图
- 格式可能与Word模板略有不同

要禁用Word模板方式，只需删除或重命名 `storage/app/templates/payslip_template.docx` 文件。

### 9. 邮件附件

当工资单标记为"已发放"时，系统会自动发送PDF到员工邮箱。如果Word模板可用，邮件附件会使用Word模板生成的PDF；否则使用HTML生成的PDF。

## 技术细节

- 使用 `PhpOffice\PhpWord\TemplateProcessor` 处理Word模板
- 使用LibreOffice命令行工具进行Word到PDF的转换
- 转换失败时自动回退到HTML/dompdf方式
- 所有临时文件会在使用后自动清理

## 支持

如有问题，请检查：
1. Laravel日志文件：`storage/logs/laravel.log`
2. 系统是否已安装LibreOffice
3. 模板文件路径和格式是否正确

