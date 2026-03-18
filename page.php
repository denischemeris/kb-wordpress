<?php
/**
 * The template for displaying all pages
 *
 * @package KB_Theme
 */

get_header();

if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();
        ?>
        <div class="container py-5">
            <div class="row">
                <div class="col-12">
                    <h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
        <?php
    endwhile;
endif;

get_footer();
