
var SectionBuilder = new function(){

	var _columns;
	var _sections;
	var _postId;
	var _htmlOutput;

	/****************************************/
	/***	Public callable functions
	/****************************************/

	/**
	 * Initting this builder:
	 * 
	 * @return void
	 */
	this.init = function(){

		var self = this;

		self._columns = new Array();
		self._sections = new Array();
		self._htmlOutput = '';
		self._postId = ChefSections.postId;


		//yoast support:
		if( typeof( YoastSEO ) != 'undefined' )
			self.initYoastSupport();

		//events:
		self.setEvents();

		//column and section arrays:
		self.setBuilder();
		self.setColumns();
		self.setSections();
		self.setTabs();

	}

	/**
	 * Refresh columns and sections
	 * 
	 * @return void
	 */
	this.refresh = function(){

		var self = this;

		self.setColumns();
		self.setSections();
		self.setTabs();
		self.setTabsClickable();
        self.setAddTab();

        self.setSectionsSortable();
        

		//update the eventual output:
		self.updateHtmlOutput();

	}

	/**
	 * Events for the builder
	 *
	 * @return void
	 */
	this.setEvents = function(){

		var self = this;

		self.setSectionTypeSelect();
		self.setAddSectionButton();
		self.setAddSectionDraggbles();
		self.setSectionsSortable();
        self.setTabsClickable();
        self.setAddTab();
		//self.setScrollLockForLightbox();

	}

	/**
	 * Set the width and stickyness of the builder-ui
	 *
	 * @return void
	 */
	this.setBuilder = function(){

		//set width:
		var _w = $('#main-section-container').innerWidth();
		var _builder = $('#section-builder-ui');
		var _container = $('#main-section-container');
		var _offset = _builder.offset().top;

		_builder.css({
			width: _w+'px'
		});



		//set the builder as sticky:
		$( window ).on( 'scroll', function(){

			var _scrollPos = $( window ).scrollTop();
			_scrollPos += $( '#wpadminbar' ).outerHeight();


			if( _scrollPos > _offset && _builder.hasClass( 'sticky' ) == false ){
				
				var _padding = _builder.outerHeight() + 30;
				_builder.addClass( 'sticky' );
				_container.css({
					'padding-top' : _padding+'px'
				});
			}else if( _scrollPos < _offset && _builder.hasClass( 'sticky' ) == true ){
				_builder.removeClass( 'sticky' );
				_container.css({
					'padding-top' : '0px'
				});
			}

		});

		$('#updatePost').on( 'click tap', function(){
			$('#section-builder-ui .spinner').addClass( 'show' );
			$('#publish').trigger( 'click' );
		});
	}
	

	/****************************************/
	/***	Refreshers
	/****************************************/


	/**
	 * Set all columns
	 *
	 * @return void
	 */
	this.setColumns = function(){

		var self = this;

		if( self._columns.length > 0 ){

			for( var i = 0; self._columns.length > i; i++ ){
				self._columns[ i ].destroy();

			}

		}


		self._columns = [];

		jQuery('.column' ).each( function( index, obj ){
			var col = new Column( { el: obj } );
			self._columns.push( col );
		});
	}

	/**
	 * Set all sections
	 *
	 * @return void
	 */
	this.setSections = function(){

		var self = this;

		if( self._sections.length > 1 ){

			for( var i = 0; self._sections.length > i; i++ ){
				self._sections[ i ].destroy();
			}
		}

		self._sections = [];

		jQuery('.section-wrapper').each( function( index, obj ){

			if( $( obj ).hasClass( 'section-container' ) == false ){
			
				var sec = new Section( { el: obj } );
			
			}else{

				var sec = new Container({ el: obj });
			}

			self._sections.push( sec );

		});

		if( self._sections.length > 0 ){
			$('.no-section-msg.msg').addClass('hidden');

		}else{
			$('.no-section-msg.msg').removeClass('hidden');
		}

		self.setSectionOrder();


	}


	/****************************************/
	/***	Events
	/****************************************/

	/**
	 * Making sections sortable
	 *
	 * @returns void
	 */
	this.setSectionsSortable = function(){

		var self = this;
		$('.section-sortables, .tab-nav').sortable({
			handle: '.pin',
			connectWith: '.section-sortables',
			placeholder: 'section-placeholder',
			update: function (event, ui) {
				self.setSectionOrder();
			}
        });
        
        //handle tabbed sortables:
        $('.tab-nav').not('.section-sortables').sortable({
            handle: '.pin',
            placeholder: 'section-placeholder',
            update: function (event, ui) {
                self.setSectionOrder();
            }
        });
	}

	/**
	 * Set section order
	 *
	 * @return void
	 */
	this.setSectionOrder = function(){

		var i = 1;

		//regular sections:
		jQuery('#main-section-container > .section-wrapper').each( function(){
			var field = jQuery( this ).find( '.section-position' );
			field.val( i );
			i++;
		});

		
		var i = 1;

		//handle grouped container sections:
		jQuery('#main-section-container .section-wrapper .grouped-sections .section-wrapper').each( function(){
			var field = jQuery( this ).find( '.section-position' );
			field.val( i );
			i++;
		});

		
		var i = 1;
		
		//handle tab container sections:
		jQuery( '#main-section-container .section-wrapper .tabbed-sections .tab').each( function(){
			var _id = $( this ).data( 'id' );
			var _container = $( this ).parent().data('container_id');

			var _sec = jQuery( '#tabContentFor'+_container+' .section-'+_id );
			_sec.find( '.section-position' ).val( i );
			i++;
		});
	}


	/**
	 * Changing the template type:
	 * 
	 * @param  Event e
	 * @return void
	 */
	this.setSectionTypeSelect = function(){

		var self = this;

		$('.type-select').on( 'change', function( e ){

			if( $( this ).hasClass( 'field-apply_to') == false ){

				if( $( e.target ).val() === 'blueprint' ){
		
					$('.field-apply_to').parent().removeClass( 'not-visible' );
			
				}else{
			
					$('.field-apply_to').parent().addClass( 'not-visible' );
			
				}
			}
		});
	}

	/**
	 * Add sections by clicking the button 
	 * 
	 * @return html
	 */
	this.setAddSectionButton = function(){

		var self = this;

		//add on click:
		$('.add-section-btn').on( 'click on', function( e ){

			e.preventDefault();


			//create the placeholder:
			var _html = '<div id="section-container" class="add-section-btn ui-draggable-handle"></div>';
            $('#main-section-container').append( _html );
            
            
			var _placeholder = $('#main-section-container .add-section-btn.ui-draggable-handle' );
            _placeholder.addClass('placeholder-block');
			_placeholder.html( '<span class="spinner"></span> Adding section...' );

            
            //gather data:
            var _data = $(this).data();
			var data = {
                'action': _data['action'],
                'post_id': _data['post_id'],
            }
            

			if( data.type == 'search' ){
					
				self.launchSearchWindow( data, _placeholder, function( _newData ){
					self.updateSections( _newData, _placeholder );
				})

			}else{
				self.updateSections( data, _placeholder );
			}
		});
	}

	/**
	 * Set the draggable buttons
	 *
	 * @return void
	 */
	this.setAddSectionDraggbles = function(){

		var self = this;

		jQuery('.add-section-btn').draggable({
			connectToSortable: '.section-sortables',
			helper: 'clone',
			revert: 'invalid',
			start: function( event, ui ){
				$('#main-section-container').addClass( 'dragging' ); 
			},
			stop: function( event, ui ){

				$('#main-section-container').removeClass( 'dragging' );
				var _placeholder = $('#main-section-container .add-section-btn.ui-draggable-handle' );
				
				//check if the placeholder exists:
				if( _placeholder.length > 0 ){

					_placeholder.addClass('placeholder-block');
					_placeholder.html( '<span class="spinner"></span> Adding section...' );

					//set the data
					var data = _placeholder.data();


					//set container_id, if applicable:
					var dropzone = _placeholder.parent();
					if( typeof( dropzone.data('container_id') ) != 'undefined' )
						data['container_id'] = dropzone.data( 'container_id' );


					//delete extra information, not needed:
                    delete data['sortableItem'];
                    
					if( data.type == 'search' ){
						
						self.launchSearchWindow( data, _placeholder, function( _newData ){

							delete _newData['sortableItem'];
							
							self.updateSections( _newData, _placeholder );
						});

					}else{
						self.updateSections( data, _placeholder );
					}
				}

			}
		});
	}

	/**
	 * Update sections through AJAX
	 * 
	 * @param  JSON data
	 * @param  DOM Element _placeholder
	 * 
	 * @return void
	 */
	this.updateSections = function( data, _placeholder ){
        
		//remove the spinner:
		$('#section-builder-ui .spinner').addClass( 'show' );
		
		var self = this;
		$.post( ajaxurl, data, function( response, text, xhr ){

			try{

                response = JSON.parse( response );
                
                self.checkAjaxResponse( response, xhr );

                if (typeof (response.html) == 'undefined')
                    throw "Bad response";


				if( response.tab != null && response.tab != 'null' && response.tab != '' ){

					var _target = $( '#tabContentFor'+data['container_id'] );
					$('#tabsFor'+data['container_id'] ).find('.tab').removeClass( 'active' ); 
					_placeholder.replaceWith( response.tab );
					_target.append( response.html );

					self.setTabs();
				}else{

					_placeholder.replaceWith( response.html );

				}

				//order items:
				self.setSectionOrder();

				//register new section here:
				self.refresh();

				//refresh the fields
				refreshFields();

				//remove the spinner:
				$('#section-builder-ui .spinner').removeClass( 'show' );
				

			}catch( e ){

				console.log( response );
				console.log( e );

			}

			
		});
	}


	/**
	 * Launch a search window
	 * 
	 * @param  JSON   data
	 * @param  Function callback
	 * 
	 * @return void
	 */
	this.launchSearchWindow = function( data, _placeholder, callback ){

		var self = this;
	
		//add HTML
		_placeholder.append( self.createSearchWindow( data ) );

		jQuery('#closeSearch').on( 'click tap', function(){
			jQuery( '#tempSearch' ).remove();
			_placeholder.remove();
		})

		//init chosen:
		jQuery('#tempSearchSelect').on('chosen:ready', function(event, data){
			
			jQuery( '#tempSearchSelect' ).trigger('chosen:open').trigger('chosen:activate');
			/*jQuery( '#tempSearch .active-result').on( 'click tap', function(){
				jQuery( '#tempSearchSelect' ).trigger('change');
			});*/

		}).chosen({
			placeholder_text_single: 'Select your preference',
			disable_search_threshold: 0
		});


		//set the callback:
		jQuery( '#tempSearchSelect' ).on( 'change', function(){

			var _templateId = jQuery( this ).val();
			var _newData = data;
			var _key = 'template_id';
			if( data.content == 'SectionContainers' )
				_key = 'container_slug';

			_newData[ _key ] = _templateId;

			jQuery( '#tempSearch' ).remove();
			callback( _newData );
		});

		
	}

	/**
	 * Generate HTML for the search window
	 * 
	 * @param  JSON data         

	 * @return void
	 */
	this.createSearchWindow = function( data ){

		var _options = window[ data.content ];

		//generate HTML:
		var _html = '<div id="tempSearch">';

		_html += '<div id="closeSearch"><span class="dashicons dashicons-no"></span></div>';
		
		if( typeof( data.label ) != 'undefined' )
			_html += '<strong>'+data.label+'</strong>';

		_html += '<select id="tempSearchSelect" data-placeholder="Please select...">';
			_html += '<option value="none"> </option>';
			for( var i = 0; i < _options.length; i++ ){
				_html += '<option value="'+_options[i]['id']+'">';
				_html += _options[i]['name'];
				_html += '</option>';
			}

		_html += '</select>';
		_html += '</div>';

		return _html;
	}

	/****************************************/
	/***	Tab functions
	/****************************************/

	this.setTabs = function(){

		var self = this;

		$('.tab-nav').each( function(){

			var _container = $( this ).data( 'container_id' );
			var _active = $( this ).find( '.active' ).data('id');

			$( '#tabContentFor'+_container+ ' > .section-wrapper' ).removeClass( 'active' );
			$( '#tabContentFor'+_container ).find( '.section-'+_active ).addClass( 'active' );

		});
	}

	this.setTabsClickable = function(){

		var self = this;

		$('.tab-nav .tab').on( 'click tap', function(){
			$( this ).parent().find('.tab').removeClass( 'active' );
			$( this ).addClass( 'active' );

			self.setTabs();	
		});
    }
    
    this.setAddTab = function(){
        
        var self = this;

        $('.add-single-tab').off('click tap').on( 'click tap', function(){

            var _el = $( this );
            var _post_id = _el.data('post_id');
            var _container_id = _el.data('container_id');
            var _column = _el.data('column');

            var placeholder = '<div class="add-section-btn ui-draggable ui-draggable-handle placeholder-block" style="position: relative; width: 130px; right: auto; height: 38px; bottom: auto; left: 0px; top: 0px; z-index: 1000;"><span class="spinner"></span> Adding section...</div>';
            $(this).parent().find('.tab-nav').append( placeholder );
            
            var data = {
                action: "createSection",
                container_id: _container_id,
                post_id: _post_id,
                columns: { 1: _column }
            }

            self.updateSections(data, $(this).parent().find('.tab-nav .placeholder-block') );
        });
    }

	//this.setScrollLockForLightbox = function(){
		



	/****************************************/
	/***	Yoast Functions
	/****************************************/

	/**
	 * Start the yoast support
	 * 
	 * @return void
	 */
	this.initYoastSupport = function(){

		var self = this;

		//fallback for plugin-loader bug in Yoast SEO
		if( $('#YoastSEO-plugin-loading' ).length <= 0 )
			$('#main-section-container').append( '<span style="display:none" id="YoastSEO-plugin-loading"></span>' );


		YoastSEO.app.registerPlugin( 'chefSections', {status: 'loading'} );
		YoastSEO.app.pluginReady( 'chefSections' );

		//register the content modification:
		YoastSEO.app.registerModification( 'content', function( _data ){

			//return the htmlOutput
			if( self._htmlOutput !== '' && self._htmlOutput !== null )
				return self._htmlOutput;

			return _data;

		}, 'chefSections', 5 );


		self.updateHtmlOutput();
	}

	/**
	 * Update the HTML output for Yoast
	 * 
	 * @return void
	 */
	this.updateHtmlOutput = function(){

		var self = this;
		
		var data = {
			action: 'getHtmlOutput',
			post_id: self._postId
		}


		$.post( ajaxurl, data, function( response, text, xhr ){

            try{

                self.checkAjaxResponse(response, xhr );


                self._htmlOutput = response;

                //reload the plugin:
                if( typeof( YoastSEO ) != 'undefined' )
                    YoastSEO.app.pluginReloaded( 'chefSections' );
            
            }catch( e ){
                console.log( response );
                console.log( e );
            }

		});
	}

    this.checkAjaxResponse = function( response, xhr ){

        if (typeof (xhr) !== 'undefined' && typeof (xhr.status) !== 'undefined') {
            if (parseInt(xhr.status) !== 200) {
                throw ('Status: ' + xhr.status);
            }
        }

        if( typeof( response.error ) !== 'undefined' && response.error == true ){
            
            if( typeof( response.message ) !== 'undefined' )
               throw( response.message );

            throw( 'General ajax error' );
        }
    }


}	


//init sections builder
jQuery( window ).load( function( $ ){

	if( jQuery('#main-section-container').length > 0 )
		SectionBuilder.init();
	
});
