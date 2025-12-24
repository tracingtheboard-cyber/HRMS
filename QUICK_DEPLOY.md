# 🚀 快速部署到 Railway.app（推荐）

Railway.app 对 Laravel 支持最好，无需 Docker 配置，最简单！

## 3步部署

### 步骤 1: 准备代码

```bash
# 确保代码已提交并推送到 GitHub
git add .
git commit -m "准备部署"
git push origin main
```

### 步骤 2: 在 Railway 创建项目

1. 访问 **https://railway.app**
2. 使用 **GitHub 账号**登录
3. 点击 **"New Project"**
4. 选择 **"Deploy from GitHub repo"**
5. 选择你的 HRMS 仓库
6. Railway 会自动检测 Laravel 项目 ✅

### 步骤 3: 添加数据库并配置

1. **添加 MySQL 数据库**
   - 在项目页面点击 **"+ New"**
   - 选择 **"Database" → "Add MySQL"**
   - Railway 会自动注入数据库环境变量（无需手动配置！）

2. **配置环境变量**
   - 点击你的 Web Service
   - 进入 **"Variables"** 标签页
   - 添加以下变量：

```
APP_NAME=HRMS
APP_ENV=production
APP_KEY=base64:你的APP_KEY值
APP_DEBUG=false
APP_URL=https://你的域名.up.railway.app

# 数据库变量 Railway 会自动添加，无需手动配置
# DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD 都是自动的

MAIL_MAILER=log
SESSION_DRIVER=file
```

3. **生成访问域名**
   - 在 **"Variables"** 页面点击 **"Generate Domain"**
   - 会得到一个类似 `hrms-production.up.railway.app` 的地址

4. **运行数据库迁移**
   - 安装 Railway CLI: `npm i -g @railway/cli`
   - 登录: `railway login`
   - 链接项目: `railway link`
   - 运行迁移:
     ```bash
     railway run php artisan migrate --force
     railway run php artisan storage:link
     railway run php artisan db:seed --class=TestUserSeeder
     railway run php artisan db:seed --class=TestEmployeeDataSeeder
     ```

## 🎉 完成！

你的应用地址：`https://你的域名.up.railway.app`

## 测试账号

- **管理员:** admin@example.com / password
- **HR:** hr@example.com / password
- **员工:** employee@example.com / password

## 💡 提示

- Railway 免费版有 $5 信用额度，足够测试使用
- 如果需要更多功能，可以考虑付费计划
- 所有配置都可以在 Railway 网页界面完成，无需命令行

## 🆘 遇到问题？

1. 查看 Railway 的部署日志
2. 检查环境变量是否正确
3. 确保 APP_KEY 已配置
4. 数据库连接问题：检查 Railway 是否已添加 MySQL 服务

