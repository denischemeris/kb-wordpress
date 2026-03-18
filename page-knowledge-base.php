<?php
/**
 * Шаблон страницы базы знаний
 *
 * Template Name: Knowledge Base
 */

get_header();

// Проверка авторизации
if (!is_user_logged_in()) {
    ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="alert alert-warning text-center">
                    <h4>Доступ ограничен</h4>
                    <p class="mb-3">Для просмотра базы знаний необходимо авторизоваться.</p>
                    <a href="<?php echo wp_login_url($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">Войти</a>
                </div>
            </div>
        </div>
    </div>
    <?php
    get_footer();
    exit;
}

$user = wp_get_current_user();
?>

<div class="container py-5 kb-protected-content">
    <!-- Заголовок -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5">
                <i class="bi bi-book"></i> База знаний
            </h1>
            <p class="text-muted">
                Добро пожаловать, <?php echo esc_html($user->display_name); ?>!
                <?php if ($user->user_email) : ?>
                    <span class="kb-watermark" style="position:fixed; bottom:10px; right:10px; opacity:0.15; font-size:12px;"><?php echo esc_html($user->user_email); ?></span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Поиск -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form role="search" method="get" class="kb-search" action="/">
                <div class="input-group input-group-lg">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Поиск по базе знаний..." 
                           name="s"
                           value="<?php echo get_search_query(); ?>">
                    <input type="hidden" name="post_type" value="epkb_post_type_1">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Найти
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Категории -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">
                <i class="bi bi-folder"></i> Категории
            </h3>
        </div>
    </div>

    <div class="row mb-5">
        <?php
        $categories = get_terms([
            'taxonomy' => 'epkb_post_type_1_category',
            'hide_empty' => true,
            'parent' => 0,
        ]);

        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php echo esc_url(get_term_link($category)); ?>" 
                                   class="text-decoration-none">
                                    <i class="bi bi-folder-fill text-primary"></i>
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            </h5>
                            <?php if ($category->description) : ?>
                                <p class="card-text text-muted">
                                    <?php echo esc_html($category->description); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php
                            // Подкатегории
                            $subcategories = get_terms([
                                'taxonomy' => 'epkb_post_type_1_category',
                                'hide_empty' => true,
                                'parent' => $category->term_id,
                            ]);
                            
                            if (!empty($subcategories) && !is_wp_error($subcategories)) {
                                echo '<ul class="list-unstyled mb-0">';
                                foreach ($subcategories as $subcat) {
                                    echo '<li>';
                                    echo '<a href="' . esc_url(get_term_link($subcat)) . '" class="text-decoration-none">';
                                    echo '  <i class="bi bi-folder text-muted"></i> ';
                                    echo esc_html($subcat->name);
                                    echo '</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                            ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <small class="text-muted">
                                <?php echo $category->count; ?> статей
                            </small>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Категории пока не созданы. Обратитесь к администратору.
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <!-- Последние статьи -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">
                <i class="bi bi-clock-history"></i> Последние статьи
            </h3>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="list-group">
                <?php
                $articles = new WP_Query([
                    'post_type' => 'epkb_post_type_1',
                    'posts_per_page' => 10,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                ]);

                if ($articles->have_posts()) {
                    while ($articles->have_posts()) {
                        $articles->the_post();
                        ?>
                        <a href="<?php the_permalink(); ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="bi bi-file-text text-primary"></i>
                                    <?php the_title(); ?>
                                </h6>
                                <small class="text-muted">
                                    <?php echo get_the_date(); ?>
                                </small>
                            </div>
                            <?php
                            $categories = get_the_terms(get_the_ID(), 'epkb_post_type_1_category');
                            if (!empty($categories) && !is_wp_error($categories)) {
                                echo '<small class="text-muted">';
                                echo implode(', ', wp_list_pluck($categories, 'name'));
                                echo '</small>';
                            }
                            ?>
                        </a>
                        <?php
                    }
                    wp_reset_postdata();
                } else {
                    ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Статей пока нет.
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

<!-- Защита от копирования -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Запрет правого клика
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Запрет горячих клавиш
    document.addEventListener('keydown', function(e) {
        // Ctrl+C, Ctrl+U, Ctrl+S, Ctrl+P, Ctrl+A, F12, PrintScreen
        if (e.ctrlKey && ['c', 'u', 's', 'p', 'a'].includes(e.key.toLowerCase())) {
            e.preventDefault();
            return false;
        }
        if (e.key === 'F12' || e.key === 'PrintScreen') {
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
});
</script>

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

/* Водяной знак */
.kb-watermark {
    position: fixed;
    bottom: 10px;
    right: 10px;
    opacity: 0.15;
    font-size: 12px;
    color: #000;
    pointer-events: none;
    z-index: 9999;
}
</style>
