<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo home_url(); ?>">
            <?php bloginfo( 'name' ); ?>
        </a>
        <div class="navbar-nav ms-auto">
            <?php if ( is_user_logged_in() ) : ?>
                <a class="nav-link" href="<?php echo home_url('/kb/'); ?>">База знаний</a>
                <a class="nav-link" href="<?php echo wp_logout_url(home_url('/')); ?>">Выйти</a>
            <?php else : ?>
                <a class="nav-link" href="<?php echo home_url('/register/'); ?>">Регистрация</a>
                <a class="nav-link" href="<?php echo home_url('/login/'); ?>">Вход</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
