#!/bin/bash

# Queue Worker Setup Script for Live Server
# This script sets up supervisor and queue workers for Laravel

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}🚀 Laravel Queue Worker Setup${NC}"
echo ""

# Get project path
read -p "Enter full path to your Laravel project (e.g., /var/www/html/ecommerce_backend_with-admin): " PROJECT_PATH

if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}❌ Project path does not exist!${NC}"
    exit 1
fi

# Get web server user
read -p "Enter web server user (default: www-data): " WEB_USER
WEB_USER=${WEB_USER:-www-data}

# Get number of workers
read -p "Enter number of worker processes (default: 2): " NUM_WORKERS
NUM_WORKERS=${NUM_WORKERS:-2}

echo ""
echo -e "${YELLOW}📋 Configuration Summary:${NC}"
echo "Project Path: $PROJECT_PATH"
echo "Web User: $WEB_USER"
echo "Number of Workers: $NUM_WORKERS"
echo ""
read -p "Continue with this configuration? (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "Setup cancelled."
    exit 0
fi

# Step 1: Install Supervisor
echo ""
echo -e "${YELLOW}📦 Installing Supervisor...${NC}"
if command -v apt-get &> /dev/null; then
    sudo apt-get update
    sudo apt-get install -y supervisor
elif command -v yum &> /dev/null; then
    sudo yum install -y supervisor
    sudo systemctl enable supervisord
else
    echo -e "${RED}❌ Could not detect package manager. Please install supervisor manually.${NC}"
    exit 1
fi

# Step 2: Setup Database Queue
echo ""
echo -e "${YELLOW}🗄️  Setting up database queue...${NC}"
cd $PROJECT_PATH

# Update .env
if grep -q "QUEUE_CONNECTION=sync" .env; then
    sed -i 's/QUEUE_CONNECTION=sync/QUEUE_CONNECTION=database/' .env
    echo -e "${GREEN}✅ Updated QUEUE_CONNECTION to database${NC}"
else
    echo "QUEUE_CONNECTION already configured"
fi

# Create queue tables
php artisan queue:table
php artisan queue:failed-table
php artisan migrate --force

echo -e "${GREEN}✅ Database queue tables created${NC}"

# Step 3: Create Supervisor Configuration
echo ""
echo -e "${YELLOW}⚙️  Creating supervisor configuration...${NC}"

SUPERVISOR_CONF="/etc/supervisor/conf.d/laravel-worker.conf"

sudo tee $SUPERVISOR_CONF > /dev/null <<EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$WEB_USER
numprocs=$NUM_WORKERS
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/worker.log
stopwaitsecs=3600
EOF

echo -e "${GREEN}✅ Supervisor configuration created at $SUPERVISOR_CONF${NC}"

# Step 4: Set Permissions
echo ""
echo -e "${YELLOW}🔐 Setting permissions...${NC}"
sudo chown -R $WEB_USER:$WEB_USER $PROJECT_PATH/storage
sudo chmod -R 775 $PROJECT_PATH/storage

# Step 5: Start Supervisor
echo ""
echo -e "${YELLOW}🚀 Starting queue workers...${NC}"
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Step 6: Verify
echo ""
echo -e "${YELLOW}🔍 Verifying worker status...${NC}"
sleep 2
sudo supervisorctl status laravel-worker:*

echo ""
echo -e "${GREEN}✅ Queue worker setup completed successfully!${NC}"
echo ""
echo -e "${YELLOW}📝 Useful Commands:${NC}"
echo "  Check status:    sudo supervisorctl status"
echo "  Restart workers: sudo supervisorctl restart laravel-worker:*"
echo "  View logs:       tail -f $PROJECT_PATH/storage/logs/worker.log"
echo "  Failed jobs:     php artisan queue:failed"
echo ""
echo -e "${GREEN}🎉 Your queue workers are now running!${NC}"
