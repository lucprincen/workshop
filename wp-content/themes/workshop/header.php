<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    
    <link rel="stylesheet" href="<?= get_stylesheet_directory_uri();?>/css/reset.css"/>
    <link rel="stylesheet" href="<?= get_stylesheet_directory_uri();?>/css/patterns.css"/>
    <?php if( !is_page( 'patterns' ) ):?>
    <link rel="stylesheet" href="<?= get_stylesheet_directory_uri();?>/css/mobile.css"/>
    <link rel="stylesheet" href="<?= get_stylesheet_directory_uri();?>/css/scale-up.css"/>
    <?php endif;?>

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <header>

        <a href="/" class="logo">
            <figure>
            </figure>
        </a>

        <nav class="main-navigation">
			<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                <!-- menu icon here-->
                <?php esc_html_e( 'Menu', 'workshop' ); ?>
            </button>
			<?php
			wp_nav_menu([
				'theme_location' => 'primary',
				'menu_id'        => 'primary',
            ]);?>
		</nav>

    </header>