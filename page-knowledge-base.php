<?php
/**
 * Шаблон страницы базы знаний
 *
 * Template Name: Knowledge Base
 */

// Проверка авторизации — если не авторизован, редирект на стандартный логин WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
    exit;
}

get_header();

$user = wp_get_current_user();
?>

<div class="container py-5">
    <!-- Заголовок -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5">
                <i class="bi bi-book"></i> База знаний
            </h1>
            <p class="text-muted">
                Добро пожаловать, <?php echo esc_html($user->display_name); ?>!
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
