<div class="article-<?= esc_attr( get_post_type() );?>">
    <?php if( has_post_thumbnail() ):?>
        <a href="<?= get_permalink();?>" class="thumbnail">
            <?php the_post_thumbnail( 'full' );?>
        </a>
    <?php endif;?>

    <h3><a href="<?= get_permalink();?>"><?= get_the_title();?></a></h3>
    <p><?= get_the_excerpt();?></p>

    <a href="<?= get_permalink();?>" title="Lees meer over <?= get_the_title();?>" class="btn">
        Lees meer
    </a>
</div>