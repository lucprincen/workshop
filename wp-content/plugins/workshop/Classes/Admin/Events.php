<?php

	namespace Workshop\Admin;

	use \Cuisine\Utilities\Url;
    use \Workshop\Contracts\EventListener;
    use \Workshop\Facades\Example;

	class Events extends EventListener{

		/**
		 * Listen for admin events
		 * 
		 * @return void
		 */
		public function listen(){

			add_action( 'admin_init', function(){
				
                //do something
                
			});

		}
	}
