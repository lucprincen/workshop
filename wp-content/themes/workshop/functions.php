<?php

    use Cuisine\View\Nav;
	use Cuisine\Wrappers\Script;
	use Cuisine\Utilities\Url;
    use Cuisine\View\Image;
    
    
    add_action( 'init', function(){
        
        //menu's:
        Nav::register(['Primary', 'Footer']);

        //scripts:
		Script::register( 'jquery', Url::wp( 'jquery/jquery' ), true );
		//Script::register( 'theme', Url::theme( 'js' ).'/script', true );

        //featured image support:
		Image::addSupport();

    });
    

    /**
     * Simple script wrapper to make the template file a bit more readable
     */
    if( !function_exists( 'the_workshop_scripts' ) ){
        function the_workshop_scripts(){          
            Script::set();
        }
    }