# CACTUS GROUP 工资单生成器 (mPDF版本)

## 安装步骤

### 1. 安装 mPDF 库

```bash
# 如果使用独立的 composer-mpdf.json
composer install --no-dev -o

# 或者直接安装 mPDF
composer require mpdf/mpdf
```

### 2. 运行生成脚本

```bash
php generate_payslip.php
```

## 功能特点

- ✅ 1:1 还原 CACTUS GROUP 工资单格式
- ✅ 使用 mPDF 库生成高质量 PDF
- ✅ 每位员工独立一页
- ✅ 严格还原表格边框（外层无边框，内层实线边框）
- ✅ 正确的数字格式（扣款项用括号包裹）
- ✅ 页脚签名区域

## 数据结构

员工数据存储在 `$employees` 数组中，包含：
- 基本信息：姓名、职位、NRIC/FIN、基本工资等
- 收入项：基本工资、津贴、加班、奖金等
- 扣除项：员工CPF、CDAC/MBMF/SINDA基金等
- 其他扣除：预付款/贷款
- 雇主贡献：雇主CPF、SDL
- 银行信息：银行名称、账号、入账日期等
- 签名信息：准备人、审批人

## 自定义数据

编辑 `generate_payslip.php` 文件中的 `$employees` 数组，添加或修改员工数据。

## 输出格式

- 默认在浏览器中显示 PDF
- 如需保存文件，修改最后一行：
  ```php
  $mpdf->Output('payslips_' . date('Y-m-d') . '.pdf', 'F');
  ```

## 注意事项

1. 确保 PHP 版本 >= 7.4
2. 确保已安装 Composer
3. mPDF 需要临时目录写入权限
4. 如果遇到字体问题，确保 Arial 字体可用

