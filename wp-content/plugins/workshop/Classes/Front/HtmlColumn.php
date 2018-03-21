<?php

	namespace Workshop\Front;
	
	use ChefSections\Columns\DefaultColumn;
	use Cuisine\Wrappers\Field;
	use Cuisine\Utilities\Url;
	
	
	class HtmlColumn extends DefaultColumn{
	
		/**
		 * The type of column
		 * 
		 * @var String
		 */
		public $type = 'html';
	
	
	
		/*=============================================================*/
		/**             Backend                                        */
		/*=============================================================*/
	
		
	
		/**
		 * Create the preview for this column
		 * 
		 * @return string (html,echoed)
		 */
		public function buildPreview(){
	
			echo '<h3>'.esc_html('<HTML>').'</h3>';	
		}
	
	
		/**
		 * Build the contents of the lightbox for this column
		 * 
		 * @return string ( html, echoed )
		 */
		public function buildLightbox(){
	
			//get all fields for this column
			$fields = $this->getFields();
	
			echo '<div class="main-content">';
			
				foreach( $fields as $field ){
				
					$field->render();
	
					//if a field has a JS-template, we need to render it:
					if( method_exists( $field, 'renderTemplate' ) ){
						echo $field->renderTemplate();
					}
	
				}
	
			echo '</div>';
			echo '<div class="side-content">';
				
				//optional: side fields
	
				$this->saveButton();
	
			echo '</div>';
		}
	
	
		/**
		 * Get the fields for this column
		 * 
		 * @return Array
		 */
		public function getFields(){

			$content = $this->getField( 'html' );

			//remove backslashes when doing ajax updates:
			if( defined( 'DOING_AJAX' ) )
				$content = stripcslashes( $content );
	
			$fields = array(

				Field::textarea(
					'html',
					__( 'Code', 'workshop' ),
					array(
						'defaultValue' => $content,
						'rows' => 20
					)
				)
			);
			
			
			return $fields;
	
		}	
	}