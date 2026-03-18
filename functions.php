<?php
/**
 * Theme Name: KB Theme
 * Theme URI: https://github.com/denischemeris/kb-wordpress
 * Author: Denis Chemeris
 * Description: Кастомная тема для базы знаний с защитой от копирования
 * Version: 1.0.0
 */

// ============================================================================
// 1. СОЗДАНИЕ РОЛИ "student" ПРИ АКТИВАЦИИ ТЕМЫ
// ============================================================================
add_action('after_switch_theme', function() {
    // Создаём роль student если не существует
    if (!get_role('student')) {
        add_role('student', 'Ученик', [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => false,
        ]);
    }
    
    // Создаём роль editor_kb если не существует
    if (!get_role('editor_kb')) {
        add_role('editor_kb', 'Редактор БЗ', [
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'upload_files' => true,
            'edit_kb_articles' => true,
            'edit_others_kb_articles' => true,
            'publish_kb_articles' => true,
        ]);
    }
    
    // Флешим rewrite rules
    flush_rewrite_rules();
});

// ============================================================================
// 2. ЗАЩИТА ОТ КОПИРОВАНИЯ (только для авторизованных пользователей)
// ============================================================================
add_action('wp_head', function() {
    if (is_user_logged_in() && current_user_can('student')) {
        ?>
        <style>
            /* Запрет выделения текста */
            .kb-protected-content {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
            
            /* Размытие при потере фокуса */
            body.blurred .kb-protected-content {
                filter: blur(10px);
                transition: filter 0.1s ease;
            }
            
            /* Водяной знак с email */
            .kb-watermark {
                position: fixed;
                bottom: 10px;
                right: 10px;
                opacity: 0.15;
                font-size: 12px;
                color: #000;
                pointer-events: none;
                z-index: 9999;
                transform: rotate(-15deg);
            }
            
            /* Скрытие видео от скачивания */
            video {
                pointer-events: auto;
            }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Запрет правого клика
            document.addEventListener('contextmenu', function(e) {
                if (document.querySelector('.kb-protected-content')) {
                    e.preventDefault();
                }
            });
            
            // Запрет горячих клавиш
            document.addEventListener('keydown', function(e) {
                // Ctrl+C, Ctrl+U, Ctrl+S, Ctrl+P, Ctrl+A
                if (e.ctrlKey && ['c', 'u', 's', 'p', 'a'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                    return false;
                }
                // F12
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }
                // PrintScreen
                if (e.key === 'PrintScreen') {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Размытие при потере фокуса
            window.addEventListener('blur', function() {
                document.body.classList.add('blurred');
            });
            
            window.addEventListener('focus', function() {
                document.body.classList.remove('blurred');
            });
            
            // Добавляем водяной знак
            const watermark = document.createElement('div');
            watermark.className = 'kb-watermark';
            watermark.textContent = '<?php echo esc_js(wp_get_current_user()->user_email); ?>';
            document.body.appendChild(watermark);
        });
        </script>
        <?php
    }
});

// ============================================================================
// 3. ВОДЯНЫЕ ЗНАКИ НА КОНТЕНТ
// ============================================================================
add_filter('the_content', function($content) {
    if (is_singular('kb_article') && is_user_logged_in()) {
        $user = wp_get_current_user();
        $content = '<div class="kb-protected-content" data-user-email="' 
                 . esc_attr($user->user_email) . '">' . $content . '</div>';
    }
    return $content;
});

// ============================================================================
// 4. ОГРАНИЧЕНИЕ ДОСТУПА К СТАТЬЯМ
// ============================================================================
add_action('template_redirect', function() {
    if (is_singular('kb_article')) {
        if (!is_user_logged_in()) {
            wp_redirect('/register/');
            exit;
        }
        
        // Проверка роли (опционально)
        $user = wp_get_current_user();
        $allowed_roles = ['student', 'editor_kb', 'administrator'];
        $has_role = array_intersect($allowed_roles, $user->roles);
        
        if (empty($has_role)) {
            wp_die('У вас нет доступа к этой статье', 'Доступ запрещён', ['response' => 403]);
        }
    }
});

// ============================================================================
// 5. КАСТОМНЫЕ ПЕРМАЛИНКИ
// ============================================================================
add_action('init', function() {
    add_rewrite_rule('^knowledge-base/?$', 'index.php?post_type=kb_article', 'top');
});

// ============================================================================
// 6. ПОДДЕРЖКА MINIATURE И ДРУГИХ ФИЧ
// ============================================================================
add_action('after_setup_theme', function() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list']);
});

// ============================================================================
// 7. ПОДКЛЮЧЕНИЕ СТИЛЕЙ И СКРИПТОВ
// ============================================================================
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('kb-theme-style', get_stylesheet_uri(), [], '1.0.0');
    
    // Bootstrap CSS (опционально)
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');
    
    // Bootstrap JS
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], '5.3.0', true);
});

// ============================================================================
// 8. СКРЫТИЕ БАЗЫ ЗНАНИЙ СО СТАРТОВОЙ СТРАНИЦЫ
// ============================================================================

// Убираем ссылку на БЗ из меню для неавторизованных
add_filter('wp_nav_menu_items', function($items, $args) {
    // Если не авторизован — убираем ссылку на knowledge-base из всех меню
    if (!is_user_logged_in()) {
        $items = preg_replace('/href=["\']\/knowledge-base\/?[\"\']/i', 'href="/login/?redirect=knowledge-base"', $items);
    }
    return $items;
}, 10, 2);

// Редирект с главной на логин для неавторизованных при попытке доступа к БЗ
add_action('template_redirect', function() {
    if (is_page('knowledge-base') && !is_user_logged_in()) {
        wp_redirect('/login/');
        exit;
    }
});

// Проверка доступа к статьям БЗ (epkb_post_type_1)
add_action('template_redirect', function() {
    if (is_singular('epkb_post_type_1') && !is_user_logged_in()) {
        wp_redirect('/login/');
        exit;
    }
});

// ============================================================================
// 9. КАСТОМНЫЕ ШОРТКОДЫ
// ============================================================================

// Шорткод для формы входа
add_shortcode('wp_login_form', function() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        return '<div class="alert alert-success">Вы уже вошли как <strong>' 
             . esc_html($user->display_name) . '</strong>. '
             . '<a href="' . wp_logout_url(home_url('/')) . '">Выйти</a></div>';
    }
    
    $redirect = isset($_GET['redirect']) ? esc_url($_GET['redirect']) : home_url('/knowledge-base/');
    
    ob_start();
    ?>
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Вход в систему</h3>
                    
                    <?php if (isset($_GET['error'])) : ?>
                        <div class="alert alert-danger">
                            Неверный логин или пароль
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo esc_url(wp_login_url()); ?>">
                        <div class="mb-3">
                            <label for="user_login" class="form-label">Логин или Email</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="user_login" 
                                   name="log" 
                                   required 
                                   placeholder="Введите логин">
                        </div>
                        
                        <div class="mb-3">
                            <label for="user_pass" class="form-label">Пароль</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="user_pass" 
                                   name="pwd" 
                                   required 
                                   placeholder="Введите пароль">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect); ?>">
                            <label class="form-check-label">
                                <input type="checkbox" name="rememberme" value="forever"> Запомнить меня
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" name="wp-submit">Войти</button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <a href="<?php echo home_url('/register/'); ?>" class="text-decoration-none">
                            Нет аккаунта? Зарегистрируйтесь
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

// Шорткод для видео с защитой
add_shortcode('kb_video', function($atts) {
    $atts = shortcode_atts([
        'file' => '',
        'width' => '100%',
    ], $atts);
    
    if (empty($atts['file'])) {
        return '<p class="text-danger">Ошибка: не указан файл видео</p>';
    }
    
    $video_url = get_template_directory_uri() . '/video-proxy.php?file=' . urlencode($atts['file']);
    
    return '
    <div class="kb-video-wrapper mb-4" style="max-width: ' . esc_attr($atts['width']) . '">
        <video controls width="100%" preload="metadata" controlsList="nodownload">
            <source src="' . esc_url($video_url) . '" type="video/mp4">
            Ваш браузер не поддерживает видео.
        </video>
    </div>';
});

// Шорткод для практики
add_shortcode('kb_practice', function($atts) {
    $atts = shortcode_atts([
        'title' => 'Практическое задание',
    ], $atts);
    
    return '
    <div class="kb-practice alert alert-info mt-4">
        <h4><i class="bi bi-tools"></i> ' . esc_html($atts['title']) . '</h4>
        <div class="kb-practice-content">';
});

add_shortcode('kb_practice_end', function() {
    return '</div></div>';
});
