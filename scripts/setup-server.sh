#!/bin/bash
# Скрипт настройки сервера для базы знаний
# Запуск: bash setup-server.sh

set -e

echo "🚀 Настройка сервера для базы знаний WordPress..."

# ============================================================================
# 1. СОЗДАНИЕ ПАПКИ ДЛЯ ВИДЕО
# ============================================================================
echo "📁 Создание папки для видео..."

VIDEO_DIR="/srv/www/kb-videos"
if [ ! -d "$VIDEO_DIR" ]; then
    mkdir -p "$VIDEO_DIR"
    echo "✅ Папка создана: $VIDEO_DIR"
else
    echo "ℹ️  Папка уже существует"
fi

chown www-data:www-data "$VIDEO_DIR"
chmod 750 "$VIDEO_DIR"
echo "✅ Права установлены"

# ============================================================================
# 2. УСТАНОВКА ПЛАГИНОВ
# ============================================================================
echo "🔌 Установка плагинов..."

cd /srv/www/wordpress

# Обновление WordPress
echo "🔄 Обновление WordPress..."
wp core update-db --allow-root

# Установка плагинов
PLUGINS=(
    "echo-knowledge-base"
    "ultimate-member"
    "members"
    "password-protected"
)

for plugin in "${PLUGINS[@]}"; do
    if ! wp plugin is-installed "$plugin" --allow-root; then
        echo "📦 Установка $plugin..."
        wp plugin install "$plugin" --activate --allow-root
    else
        echo "ℹ️  $plugin уже установлен"
        wp plugin activate "$plugin" --allow-root
    fi
done

echo "✅ Плагины установлены"

# ============================================================================
# 3. УСТАНОВКА ТЕМЫ
# ============================================================================
echo "🎨 Установка темы..."

THEME_DIR="/srv/www/wordpress/wp-content/themes/kb-theme"

if [ ! -d "$THEME_DIR" ]; then
    mkdir -p "$THEME_DIR"
    cd /srv/www/wordpress/wp-content/themes/kb-theme
    git init
    git remote add origin https://github.com/denischemeris/kb-wordpress.git
    git fetch origin
    git checkout -t origin/main
    echo "✅ Тема установлена из Git"
else
    echo "ℹ️  Тема уже установлена"
fi

cd /srv/www/wordpress
wp theme activate kb-theme --allow-root
echo "✅ Тема активирована"

# ============================================================================
# 4. СОЗДАНИЕ ПОЛЬЗОВАТЕЛЕЙ (ТЕСТОВЫЕ)
# ============================================================================
echo "👤 Создание тестовых пользователей..."

# Student
if ! wp user get test_student --allow-root 2>/dev/null; then
    wp user create test_student student@test.ru \
        --user_pass=Student123! \
        --role=student \
        --display_name="Тестовый студент" \
        --allow-root
    echo "✅ Пользователь test_student создан"
else
    echo "ℹ️  test_student уже существует"
fi

# Editor
if ! wp user get test_editor --allow-root 2>/dev/null; then
    wp user create test_editor editor@test.ru \
        --user_pass=Editor123! \
        --role=editor_kb \
        --display_name="Тестовый редактор" \
        --allow-root
    echo "✅ Пользователь test_editor создан"
else
    echo "ℹ️  test_editor уже существует"
fi

# ============================================================================
# 5. НАСТРОЙКА NGINX (ОПЦИОНАЛЬНО)
# ============================================================================
echo "🌐 Настройка nginx для видео..."

NGINX_CONF="/etc/nginx/conf.d/kb-videos.conf"

if [ ! -f "$NGINX_CONF" ]; then
    cat > "$NGINX_CONF" << 'EOF'
location /protected-videos/ {
    internal;
    alias /srv/www/kb-videos/;
    
    # Разрешить только определённые типы
    types {
        video/mp4 mp4;
        video/webm webm;
        video/ogg ogg;
    }
    
    # Запрет скачивания
    add_header Content-Disposition inline;
    
    # Кэширование
    add_header Cache-Control "public, max-age=3600";
}
EOF
    echo "✅ Конфиг nginx создан"
    
    # Проверка и перезагрузка nginx
    nginx -t && systemctl reload nginx
    echo "✅ nginx перезапущен"
else
    echo "ℹ️  Конфиг nginx уже существует"
fi

# ============================================================================
# 6. ПРОВЕРКА
# ============================================================================
echo "🔍 Проверка..."

echo ""
echo "============================================"
echo "✅ НАСТРОЙКА ЗАВЕРШЕНА!"
echo "============================================"
echo ""
echo "📍 Путь к видео: $VIDEO_DIR"
echo "📍 Путь к сайту: /srv/www/wordpress"
echo "📍 Путь к теме: $THEME_DIR"
echo ""
echo "👤 Тестовые пользователи:"
echo "   - student / Student123! (student@test.ru)"
echo "   - editor / Editor123! (editor@test.ru)"
echo ""
echo "🌐 Следующие шаги:"
echo "   1. Откройте http://${SERVER_IP:-109.73.201.197}/wp-admin"
echo "   2. Активируйте плагины в админке"
echo "   3. Создайте категории базы знаний"
echo "   4. Добавьте первую статью"
echo ""
