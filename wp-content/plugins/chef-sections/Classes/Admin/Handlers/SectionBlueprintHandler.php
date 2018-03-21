<?php

	namespace ChefSections\Admin\Handlers;

	use ChefSections\SectionTypes\Reference;
	use ChefSections\Collections\SectionCollection;
	use ChefSections\Helpers\Section as SectionHelper;
	use ChefSections\Collections\InContainerCollection;
	use ChefSections\Helpers\SectionUi as SectionUiHelper;
	use ChefSections\Collections\SectionBlueprintCollection;

	class SectionBlueprintHandler extends BaseHandler{


		/**
		 * Collection holding all sections in current Section Flow
		 * 
		 * @var ChefSections\Collections\SectionCollection
		 */
		protected $pageSections;
		

		/**
		 * The current template ID
		 * 
		 * @var int | null
		 */
		protected $templateId = null;


		/**
		 * Set the collection for this manager
		 *
		 * @return void
		 */
		public function setCollection()
		{
			$this->collection = new SectionBlueprintCollection();
			$this->pageSections = new SectionCollection( $this->postId );
		}


		/**
		 * Add the template:
		 * 
		 * @param int $templateId
		 *
		 * @return string (html)
		 */
		public function addSectionBlueprint( $templateId = null )
		{
			$this->templateId = $this->getTemplateId( $templateId );

			if( is_null( $this->templateId ) )
				return false;

			$editable = get_post_meta( $this->templateId, 'editable', true );
			$args = $this->getArguments();
			
			//add a blueprint reference if this isn't editable:
			if( $editable == 'false' || $editable == false || is_null( $editable ) ){

				$section = $this->addBlueprintReference( $args );

			//else, copy this blueprint into a new content section:
			}else{

				$section = $this->addBlueprintCopy( $args );
			}

			//set response:
			$response = [];
			$sectionUi = SectionUiHelper::getClass( $section );
			$response['html'] = $sectionUi->get();
			$response['tab'] = $sectionUi->getTab();

			return $this->response( $response );
	
		}


		/**
		 * Copy a blueprint into a fresh section
		 * 
		 * @return BaseSection
		 */
		public function addBlueprintCopy( $args ){

			$originalId = $args['original_id'];
			unset( $args['template_id'] );
			unset( $args['original_id'] );

			//save this section:
			$_sections = $this->pageSections->toArray()->all();
			$_sections[ $args['id'] ] = $args;

			//support for containers:
			if( $args['type'] == 'container' ){
				$_sections = $this->saveContaineredSections( 
					$originalId, 
					$args['id'],
					$_sections
				);
			}

			update_post_meta( $this->postId, 'sections', $_sections );

			//create the new Reference object, and build it
			return SectionHelper::getClass( $args );
		}



		/**
		 * Add a reference section
		 *
		 * @return BaseSection
		 */
		public function addBlueprintReference( $args )
		{

			//refill the arguments with the parent data:
			$args['type'] = 'reference';
	
			//save this section:
			$_sections = $this->pageSections->toArray()->all();
			$_sections[ $args['id'] ] = $args;
			update_post_meta( $this->postId, 'sections', $_sections );

			//create the new Reference object, and build it
			return new Reference( $args );
		}



		/**
		 * Returns an array of the basic arguments
		 * 
		 * @return Array
		 */
		public function getArguments( $parent = null )
		{
			//up the highest ID
			$this->pageSections->setHighestId( 1 );

			//find the parent template:
			if( is_null( $parent ) ){
				$referenceSections = new SectionCollection( $this->templateId );
				$parent = $referenceSections->toArray()->first();
			}
			
			//set the section specifics:
			$specifics = array(
				'id'				=> $this->pageSections->getHighestId(),
				'position'			=> ( count( $this->pageSections->all() ) + 1 ),
				'post_id'			=> $this->postId,
				'container_id'		=> ( isset( $_POST['container_id'] ) ? $_POST['container_id'] : null ),
				'template_id'		=> $this->templateId,
				'original_id'		=> $parent['id']
			);

			//set container support
			if( $parent['type'] == 'container' ){
				$specifics['slug'] = $parent['slug']; 
				$specifics['type'] = 'container';
				$specifics['view'] = $parent['view'];
			}

			//don't allow the next flow to overwrite these specific values:
			$dontOverwrite = array_keys( $specifics );

			//get default args
			$args = wp_parse_args( $specifics, SectionHelper::defaultArgs() );

			//populate the rest of the arguments:
			foreach( $parent as $key => $value ){

				if( !in_array( $key, $dontOverwrite ) ){
					$args[ $key ] = $value;
				}

			}

			//copy columns to new instance:
			$this->saveColumns( $args, $parent );

			return $args;
		}


		/**
		 * Save the containered sections
		 * 
		 * @param  Int    $originalId
		 * @param  Int 	  $newId
		 * @param  Array  $sections
		 * 
		 * @return Array
		 */
		public function saveContaineredSections( $originalId, $newId, $_sections )
		{
		
			$inContainer = ( new InContainerCollection( $this->templateId, $originalId ) );

			if( !$inContainer->isEmpty() ){
				foreach( $inContainer->toArray()->all() as $containered ){
					
					//pass along the containered section as the original
					$args = $this->getArguments( $containered );
					unset( $args['template_id'] );
					unset( $args['original_id'] );

					//set the new container id:
					$args['container_id'] = $newId;

					//set the new section arguments
					$_sections[ $args['id'] ] = $args;
				}
			}
			
			return $_sections;	
		}

		/**
		 * Save the new columns for this section
		 * 
		 * @param  Array $args  
		 * @param  Array $parent 
		 * 
		 * @return void
		 */
		public function saveColumns( $args, $parent )
		{
			if( !empty( $args['columns'] ) ){

				foreach( $args['columns'] as $id => $type ){

					$originalKey = '_column_props_'.$parent['id'].'_'.$id;
					$newKey = '_column_props_'.$args['id'].'_'.$id;

					$original = get_post_meta( $args['template_id'], $originalKey, true );
					update_post_meta( $this->postId, $newKey, $original );
				}
			}
		}

		

		/**
		 * Returns the provided template ID
		 * 
		 * @param  int $templateId (optional)
		 * 
		 * @return int
		 */
		public function getTemplateId( $templateId = null )
		{
			//check for a template-id via POST
			if( is_null( $templateId ) && isset( $_POST['template_id'] ) )
				$templateId = $_POST['template_id'];

			//no template id? return null
			return $templateId;

		}

	}