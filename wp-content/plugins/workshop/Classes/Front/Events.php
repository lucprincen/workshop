<?php

	namespace Workshop\Front;

	use \Cuisine\Utilities\Url;
	use \Cuisine\Wrappers\Route;
    use \Cuisine\Wrappers\PostType;
    use \ChefSections\Wrappers\Walker;
	use \Workshop\Contracts\EventListener;

	class Events extends EventListener{


		/**
		 * Listen to front-end events
		 * 
		 * @return void
		 */
		public function listen(){

			add_filter( 'the_content', function( $content ){
                if( get_post_type() == 'page' && Walker::hasSections() ){
                    return Walker::walk();
                }

                return $content;
            });

            add_filter( 'chef_sections_display_section_wrapper', '__return_false' );

            //custom columns:
			add_filter( 'chef_sections_column_types', function( $types ){

				$types['html'] = array(
					'name'		=> 'Html',
					'class'		=> 'Workshop\Front\HtmlColumn',
					'template'	=> Url::path( 'plugin', 'workshop/Templates/HtmlColumn.php' )
                );

                return $types;

            });
		}
	}
