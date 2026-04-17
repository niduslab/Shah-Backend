#!/bin/bash

# Laravel Deployment Script with Queue Worker Restart
# Usage: ./deploy-with-queue.sh

set -e

echo "🚀 Starting deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
PROJECT_PATH="/var/www/html/ecommerce_backend_with-admin"
BRANCH="main"

echo -e "${YELLOW}📦 Pulling latest code...${NC}"
cd $PROJECT_PATH
git pull origin $BRANCH

echo -e "${YELLOW}📚 Installing dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${YELLOW}🗄️  Running migrations...${NC}"
php artisan migrate --force

echo -e "${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${YELLOW}🔄 Restarting queue workers...${NC}"
if command -v supervisorctl &> /dev/null
then
    sudo supervisorctl restart laravel-worker:*
    echo -e "${GREEN}✅ Queue workers restarted${NC}"
else
    echo -e "${RED}⚠️  Supervisor not found. Please restart queue workers manually.${NC}"
fi

echo -e "${YELLOW}🔍 Checking queue worker status...${NC}"
if command -v supervisorctl &> /dev/null
then
    sudo supervisorctl status laravel-worker:*
fi

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"

# Optional: Show recent logs
echo -e "${YELLOW}📋 Recent worker logs:${NC}"
tail -n 20 $PROJECT_PATH/storage/logs/worker.log 2>/dev/null || echo "No worker logs found"

echo ""
echo -e "${GREEN}🎉 All done! Your application is now live with the latest changes.${NC}"
