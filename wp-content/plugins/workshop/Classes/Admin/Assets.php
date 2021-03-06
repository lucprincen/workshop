<?php

	namespace Workshop\Admin;

	use \Cuisine\Utilities\Url;
	use \Workshop\Contracts\AssetLoader;

	class Assets extends AssetLoader{

		/**
		 * Enqueue scripts & Styles
		 * 
		 * @return void
		 */
		public function load(){

			/**
			 * Below are just some examples
			 */
			add_action( 'admin_menu', function(){

				//$url = Url::plugin( 'Workshop', true ).'Assets';

				//enqueue a script
				//wp_enqueue_script( 'Workshop_admin', $url.'/js/Admin.js' );

				//enqueue a stylesheet:
				//wp_enqueue_style( 'Workshop_style', $url, '/css/admin.css' );
				
			});
		}
	}