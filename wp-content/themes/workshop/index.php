<?php
/**
 * The main template file
 *
 * @package workshop
 */

get_header();
?>

<main>

    <?php if( have_posts() ) while( have_posts() ): the_post();?>
    <article>
        <h1><?php the_title();?></h1>
        <?php the_content();?>
    </article>
    <?php endwhile;?>

</main>
<?php get_footer();?>
