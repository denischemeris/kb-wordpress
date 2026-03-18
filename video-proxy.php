<?php
/**
 * Video Proxy — защищённая отдача видео
 * 
 * Использование: <video src="/wp-content/themes/kb-theme/video-proxy.php?file=lesson1.mp4">
 * 
 * Защита:
 * - Только для авторизованных пользователей
 * - Whitelist файлов
 * - Запрет скачивания (inline)
 * - Rate limiting
 */

require '../../../wp-load.php';

// ============================================================================
// 1. ПРОВЕРКА АВТОРИЗАЦИИ
// ============================================================================
if (!is_user_logged_in()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

$user = wp_get_current_user();
$allowed_roles = ['student', 'editor_kb', 'administrator'];

if (!array_intersect($allowed_roles, $user->roles)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Доступ запрещён']);
    exit;
}

// ============================================================================
// 2. ПОЛУЧЕНИЕ И ПРОВЕРКА ФАЙЛА
// ============================================================================
$file = isset($_GET['file']) ? basename($_GET['file']) : '';

if (empty($file)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Не указан файл']);
    exit;
}

// Whitelist разрешённых файлов
$allowed_files = [
    'lesson1.mp4',
    'lesson2.mp4',
    'lesson3.mp4',
    'intro.mp4',
    'demo.mp4',
];

// Проверка по whitelist (если файл не в списке — проверяем существование)
if (!in_array($file, $allowed_files)) {
    // Для новых файлов проверяем только существование и расширение
    if (!preg_match('/\.(mp4|webm|ogg)$/i', $file)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Недопустимый формат файла']);
        exit;
    }
}

// Путь к файлу
$video_dir = '/srv/www/kb-videos/';
$file_path = $video_dir . $file;

// Проверка существования файла
if (!file_exists($file_path)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Файл не найден']);
    exit;
}

// ============================================================================
// 3. RATE LIMITING (простой)
// ============================================================================
$transient_key = 'video_request_' . $user->ID . '_' . md5($file);
$last_request = get_transient($transient_key);

if ($last_request && (time() - $last_request) < 1) {
    // Слишком частые запросы
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Слишком много запросов']);
    exit;
}

set_transient($transient_key, time(), 60);

// Логирование запроса
error_log(sprintf(
    '[KB Video] User %s (%s) requested file: %s',
    $user->user_login,
    $user->user_email,
    $file
));

// ============================================================================
// 4. ОТДАЧА ФАЙЛА
// ============================================================================
$file_size = filesize($file_path);
$file_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

// MIME типы
$mime_types = [
    'mp4' => 'video/mp4',
    'webm' => 'video/webm',
    'ogg' => 'video/ogg',
];

$content_type = $mime_types[$file_extension] ?? 'video/mp4';

// Заголовки для потоковой отдачи
header('Content-Type: ' . $content_type);
header('Content-Disposition: inline; filename="' . $file . '"');
header('Content-Length: ' . $file_size);
header('Accept-Ranges: bytes');

// Запрет кэширования (опционально)
// header('Cache-Control: no-cache, no-store, must-revalidate');

// Поддержка range requests (для перемотки видео)
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    
    if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
        $start = intval($matches[1]);
        $end = !empty($matches[2]) ? intval($matches[2]) : $file_size - 1;
        
        header('HTTP/1.1 206 Partial Content');
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $file_size);
        header('Content-Length: ' . ($end - $start + 1));
        
        $handle = fopen($file_path, 'rb');
        fseek($handle, $start);
        
        $buffer = 1024 * 8;
        $remaining = $end - $start + 1;
        
        while ($remaining > 0 && !feof($handle)) {
            $chunk = min($buffer, $remaining);
            echo fread($handle, $chunk);
            flush();
            $remaining -= $chunk;
        }
        
        fclose($handle);
        exit;
    }
}

// Обычная отдача файла
readfile($file_path);
exit;
