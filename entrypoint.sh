#!/bin/sh

echo "⏳ Waiting for DB..."
sleep 10

echo "📦 Loading schema..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < /var/www/html/schema.sql

echo "🚀 Starting Apache..."
apache2-foreground
