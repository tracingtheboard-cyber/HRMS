#!/bin/bash
# Railway 部署后自动执行的脚本
# 这个脚本会在 Railway 部署时自动运行

echo "🚀 开始部署 HRMS 系统..."

# 运行数据库迁移
echo "📊 运行数据库迁移..."
php artisan migrate --force

# 创建存储链接
echo "🔗 创建存储链接..."
php artisan storage:link

# 运行种子数据
echo "🌱 填充测试数据..."
php artisan db:seed --class=TestUserSeeder --force
php artisan db:seed --class=TestEmployeeDataSeeder --force

# 缓存配置（生产环境优化）
echo "⚡ 优化配置..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ 部署完成！"

