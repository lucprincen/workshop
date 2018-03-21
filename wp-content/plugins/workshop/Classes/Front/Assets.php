<?php

	namespace Workshop\Front;

	use Cuisine\Utilities\Url;
	use Cuisine\Wrappers\Script;
	use Cuisine\Wrappers\Sass;
	use Workshop\Contracts\AssetLoader;

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
			add_action( 'init', function(){

				//javascript files loaded in the frond-end:
				//$url = Url::plugin( 'Workshop', true ).'Assets/js/';

				// id - url (without .js) - autoload
				//Script::register( 'Workshop-script', $url.'Frontend', false );

				//sass files loaded in the front-end:
				//$url = 'Workshop/Assets/sass/';
				
				// id - url (without .scss ) - force-overwrite
				//Sass::register( 'template', $url.'_template', false );
			
			});
		}
	}
