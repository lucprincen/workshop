<?php
namespace Cuisine\Builders;

use Cuisine\Utilities\Session;
use Cuisine\Utilities\User;

class SettingsPageBuilder {


	/**
	 * SettingPAge instance data.
	 *
	 * @var Array
	 */
	protected $data;


	/**
	 * The current user instance.
	 *
	 * @var \Cuisine\Utilities\User
	 */
	protected $user;


	/**
	 * The settings page view, in raw html
	 *
	 * @var html
	 */
	protected $view;


	/**
	 * Whether or not check for user capability.
	 *
	 * @var bool
	 */
	protected $check = false;

	/**
	 * The capability to check.
	 *
	 * @var string
	 */
	protected $capability;


	/**
	 * What render type is this settings page?
	 *
	 * @var string
	 */
	protected $renderType;


	/**
	 * Build a settings page instance.
	 *
	 * @param \Cuisine\Validation\Validation $validator
	 * @param \Cuisine\User\User $user
	 */
	public function __construct(){

		$this->data = array();

	}


	/**
	 * Set a new settings page.
	 *
	 * @param string $title The settings page title.
	 * @param string $slug The settings page slug name.
	 * @param array $options SettingPage extra options.
	 * @param \Cuisine\View\SettingPageView
	 * @return object
	 */
	public function make( $title, $slug, array $options = array(), $view = null ){

	  	$this->data['title'] = $title;
	  	$this->data['form-title'] = $this->getOptionName();
	    $this->data['slug'] = $slug;
	    $this->data['options'] = $this->parseOptions($options);
	    $this->data['icon'] = $this->data['options']['icon'];
	    $this->data['position'] = $this->data['options']['position'];

	    $this->capability = $this->data['options']['capability'];
	    $this->renderType = 'fields';

	    if ( !is_null( $view ) )
	        $this->view = $view;


	    return $this;
	}


	/**
	 * Build the set settings page.
	 *
	 * @param array $fields A list of fields to display.
	 * @return \Cuisine\Builders\SettingPageBuilder
	 */
	public function set( $contents = array() ){

		//if it's an array, contents contains fields
	    if( is_array( $contents ) ){

		    if( get_class( $contents[0] ) == 'Cuisine\Builders\SettingsTabBuilder' )
		    	$this->renderType = 'tabs';

		    $this->data['objects'] = $contents;
		    $this->data['render'] = array( &$this, 'render' );

		//else it contains a view:
		}else{

			$this->renderType = 'class';
			$this->data['objects'] = array();
			$this->data['render'] = $contents;

		}

		if( isset( $_POST[ $this->data['form-title']  ] ) ){
			$this->save();
		}


	   	add_action( 'admin_menu', array( &$this, 'display' ) );

	    return $this;
	}


	/**
	 * Restrict access to a specific user capability.
	 *
	 * @param string $capability
	 * @return void
	 */
	public function can($capability){
	    $this->capability = $capability;
	    $this->check = true;

	}


	/**
	 * The wrapper display method.
	 *
	 * @return void
	 */
	public function display(){

	    if( $this->check && !$this->user->can( $this->capability ) ) return;

	    if( $this->data['options']['parent'] == false ){

	    	add_menu_page(
	    		$this->data['title'],
	    		$this->data['options']['menu_title'],
	    		$this->capability,
	    		$this->data['slug'],
	    		$this->data['render'],
	    		$this->data['icon'],
	    		$this->data['position']
	    	);

	    }else if( $this->data['options']['parent'] === 'options' ){

	    	add_options_page(
	    		$this->data['title'],
	    		$this->data['options']['menu_title'],
	    		$this->capability,
	    		$this->data['slug'],
	    		$this->data['render']
	    	);


	    }else{

	    	$parentSlug = $this->data['options']['parent'];

	    	if( substr( $parentSlug, -4 ) !== '.php' )
	    		$parentSlug = 'edit.php?post_type='.$parentSlug;

	    	add_submenu_page(
	    		$parentSlug,
	    		$this->data['title'],
	    		$this->data['options']['menu_title'],
	    		$this->data['options']['capability'],
	    		$this->data['slug'],
	    		$this->data['render']
	    	);
	    }

	}


	/**
	 * Call by "add_meta_box", build the HTML code.
	 *
	 * @param \WP_Post $post The WP_Post object.
	 * @param array $datas The settings page $args and associated fields.
	 * @throws SettingPAgeException
	 * @return void
	 */
	public function render() {

		if( $this->renderType == 'fields' )
			$this->setDefaultValue();

		echo '<div class="wrap">';

			echo '<h2>'.$this->data['title'].'</h2>';
	   		echo '<br/><br/>';

	   		echo '<form method="post">';

	   		echo '<input type="hidden" name="'.$this->data['form-title'].'" value="true"/>';

	    	// Add nonce fields
	    	wp_nonce_field( Session::nonceAction, Session::nonceName );

	    	if( $this->renderType == 'tabs' )
	    		$this->renderTabTitles();


	    	echo '<div class="settings-wrapper type-'.$this->renderType.'">';
		    	foreach( $this->data['objects'] as $object ){
		    		$object->render();
	    		}
	    	echo '</div>';


	    	if( $this->renderType == 'fields' ){

	    		//render the javascript-templates seperate, to prevent doubles
	    		$rendered = array();

	    		foreach( $this->data['objects'] as $field ){

	    			if( method_exists( $field, 'renderTemplate' ) && !in_array( $field->name, $rendered ) ){

	    				echo $field->renderTemplate();
	    				$rendered[] = $field->name;

	    			}
	    		}
	    	}


	    	echo '<div class="button-wrapper">';

	    		echo '<input type="submit" class="button button-primary button-large" value="'.__( 'Save settings', 'cuisine' ).'">';

	    	echo '</div>';
	    	echo '</form>';

	    echo '</div>';
	}

	/**
	 * Render the tab titles:
	 *
	 * @return string
	 */
	public function renderTabTitles(){

		$i = 0;

		echo '<div class="tab-wrapper">';

		foreach( $this->data['objects'] as $tab ){

			$class = 'tab';
			if( $i == 0 )
				$class .= ' current';

			echo '<span class="'.$class.'" data-slug="'.$tab->getSlug().'">';
				echo $tab->getTitle();
			echo '</span>';

			$i++;
		}

		echo '</div>';

	}


	/**
	 * The wrapper install method. Save container values.
	 *
	 * @param int $postId The post ID value.
	 * @return void
	 */
	public function save(){


	    $nonceName = (isset($_POST[Session::nonceName])) ? $_POST[Session::nonceName] : Session::nonceName;
	    if (!wp_verify_nonce($nonceName, Session::nonceAction)) return;

	    // Check user capability.
	    if ( $this->check )
	        if ( !$this->user->can( $this->capability ) ) return;


	    $fields = array();

	    // Loop through the registered fields.
	    if( $this->renderType == 'fields' ){

	    	$fields = $this->data['objects'];

	    }else{
	    	//get all fields in the tabs:
	    	foreach( $this->data['objects'] as $tab ){
	    		$fields = array_merge( $fields, $tab->getFields() );
	    	}
	    }

	    $fields = apply_filters( 'cuisine_before_settings_field_save', $fields, $this );

	    $this->register( $fields );

	}


	/**
	 * Register the settings page and its fields into the DB.
	 *
	 * @param int $postId
	 * @param array $fields
	 * @return void
	 */
	protected function register( $fields ) {

	    $save = array();

	    foreach( $fields as $field ){

	    	$key = $field->name;

	    	//change the value for editors, as the $_POST
	    	//variable for that field is different
	    	if( $field->type == 'editor' )
	    		$key = $field->id;

	       	$value = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';

	       	if( $field->type == 'repeater' || $field->type == 'flex' ){
                $value = $field->getFieldValues();
            }

	       	$save[ $field->name ] = $value;

	    }

	    $save = apply_filters( 'cuisine_settings_page_data_to_save', $save, $this, $fields );
	    do_action( 'cuisine_before_settings_page_update', $this );

	    update_option( $this->data['slug'], $save );

	    do_action( 'cuisine_after_settings_page_update', $this );
	}


	/**
	 * Check settings page options: context, priority.
	 *
	 * @param array $options The settings page options.
	 * @return array
	 */
	protected function parseOptions(array $options) {

	    return wp_parse_args( $options, array(

	        'menu_title'   	=> $this->data['title'],
	        'parent'		=> false,
	        'capability'	=> 'manage_options',
	        'icon'			=> false,
	        'position'		=> null,

	    ));

	}


	/**
	 * return the name of these options
	 *
	 * @return string
	 */
	protected function getOptionName(){
		return 'settings-'.sanitize_title( $this->data['title'] );
	}


	/**
	 * Set the default 'value' property for all fields.
	 *
	 * @return void
	 */
	protected function setDefaultValue() {

		$values = get_option( $this->data['slug'], array() );

	    foreach ( $this->data['objects'] as $field ){

	    	$key = $field->name;

	        // Check if saved value
	        if( isset( $values[ $key ] ) ){
	        	$value = $values[ $key ];
	        	$field->properties['defaultValue'] = $value;
	        }

	    }
	}


	/**
	 * Get this settingspage url
	 * 
	 * @return String
	 */
	public function getUrl()
	{
		switch( $this->data['options']['parent'] ){

			case false:
				return admin_url( $this->getSlug().'.php' );
				break;
			case 'options':
				return admin_url( 'options-general.php?page='.$this->getSlug() );
				break;

			default:
				
				$parentSlug = $this->data['options']['parent'];

	    		if( substr( $parentSlug, -4 ) !== '.php' )
	    			$parentSlug = 'edit.php?post_type='.$parentSlug;

	    		$url = add_query_arg( 'page', $this->getSlug(), $parentSlug );
				return admin_url( $url );
				break;

		}
	}

	/**
	 * Returns this settings page's slug
	 * 
	 * @return String
	 */
	public function getSlug()
	{
		return $this->data['slug'];
	}
}

