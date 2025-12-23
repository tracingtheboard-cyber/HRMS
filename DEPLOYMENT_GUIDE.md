# 部署指南 - HRMS 系统

本指南将帮助您将 HRMS 系统部署到云端，让朋友可以测试。

## 快速部署选项

### 选项 1: Render.com（推荐，简单快速）

**优点：** 免费套餐、自动部署、简单配置

**步骤：**

1. **注册 Render 账号**
   - 访问 https://render.com
   - 使用 GitHub/GitLab 账号登录

2. **推送代码到 Git 仓库**
   ```bash
   # 如果还没有 Git 仓库
   git init
   git add .
   git commit -m "Initial commit"
   
   # 推送到 GitHub/GitLab
   git remote add origin <your-repo-url>
   git push -u origin main
   ```

3. **在 Render 创建 Web Service**
   - 点击 "New +" → "Web Service"
   - 连接您的 Git 仓库
   - 配置如下：
     - **Name:** hrms
     - **Environment:** PHP
     - **Build Command:** `composer install --no-dev --optimize-autoloader`
     - **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`
     - **Plan:** Free

4. **配置环境变量**
   在 Render 的 Environment 选项卡中添加：
   ```
   APP_NAME=HRMS
   APP_ENV=production
   APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxx  # 运行 php artisan key:generate 生成
   APP_DEBUG=false
   APP_URL=https://your-app-name.onrender.com
   
   DB_CONNECTION=mysql
   DB_HOST=your-db-host
   DB_PORT=3306
   DB_DATABASE=your-db-name
   DB_USERNAME=your-db-user
   DB_PASSWORD=your-db-password
   
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   MAIL_FROM_NAME="${APP_NAME}"
   
   CLOUDCONVERT_API_KEY=your-cloudconvert-key  # 可选
   ```

5. **创建 MySQL 数据库**
   - 在 Render 创建 "PostgreSQL" 或使用外部 MySQL 服务（如 PlanetScale、Aiven）

6. **运行数据库迁移**
   - 在 Render 的 Shell 中运行：
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=TestUserSeeder
   ```

### 选项 2: Railway.app

**优点：** 免费试用、支持 MySQL、简单

**步骤：**

1. 访问 https://railway.app
2. 使用 GitHub 登录
3. 点击 "New Project" → "Deploy from GitHub repo"
4. 添加 MySQL 数据库
5. 配置环境变量（同上）
6. 自动部署

### 选项 3: Fly.io

**优点：** 免费套餐、全球 CDN

**步骤：**

1. 安装 Fly CLI: `curl -L https://fly.io/install.sh | sh`
2. 登录: `fly auth login`
3. 初始化: `fly launch`
4. 配置数据库和环境变量
5. 部署: `fly deploy`

## 部署前准备清单

### 1. 创建 .env.example 文件

已为您创建 `env.example` 文件，包含所有必要的环境变量。

### 2. 确保代码已提交到 Git

```bash
git add .
git commit -m "Prepare for deployment"
git push
```

### 3. 生成 APP_KEY

```bash
php artisan key:generate
```

复制生成的 `APP_KEY` 值到部署平台的环境变量。

### 4. 配置存储链接

确保部署后运行：
```bash
php artisan storage:link
```

### 5. 优化配置（生产环境）

```bash
# 缓存配置
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 数据库迁移和种子数据

部署后，需要运行：

```bash
php artisan migrate --force
php artisan db:seed --class=TestUserSeeder
php artisan db:seed --class=TestEmployeeDataSeeder
```

## 测试账号

系统会自动创建测试账号（通过 TestUserSeeder）：
- **管理员:** admin@example.com / password
- **HR:** hr@example.com / password
- **员工:** employee@example.com / password

## 常见问题

### 1. 存储文件无法访问

确保运行了 `php artisan storage:link`，并且 `storage/app/public` 目录有写入权限。

### 2. PDF 生成失败

检查是否正确安装了 dompdf 和相关依赖。某些平台可能需要额外的系统库。

### 3. 邮件发送失败

确保配置了正确的 SMTP 设置，或使用 Mailtrap 进行测试。

### 4. 中文显示问题

确保服务器支持 UTF-8 编码，并在 `.env` 中设置了正确的字符集。

## 安全建议

1. ✅ 设置 `APP_DEBUG=false`（生产环境）
2. ✅ 使用强密码
3. ✅ 定期更新依赖包
4. ✅ 使用 HTTPS（大多数平台自动提供）
5. ✅ 限制数据库访问权限

## 需要帮助？

如果遇到问题，请检查：
- Render/Railway/Fly.io 的日志
- Laravel 日志: `storage/logs/laravel.log`
- 环境变量是否正确配置


