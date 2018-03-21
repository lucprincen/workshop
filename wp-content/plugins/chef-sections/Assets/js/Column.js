


	var Column = Backbone.View.extend({

		hasLightbox: true,

		columnId: '',
		fullId: '',
		sectionId: '',
		postId: '',

		/**
		 * Events for this View
		 * @return object
		 */
		events: {
			'click .edit-btn': 'launchLightbox',
			'click .lightbox-modal-close': 'closeLightbox',
			'click #save-column': 'saveColumn',
			'change .column-controls .type-select': 'changeType'
			//'keyup': 'saveAndClose'
		},


		/**
		 * Events for this View
		 * @return this
		 */
		initialize: function(){

			var self = this;

			self.fullId = self.$el.data('id');
			self.columnId = self.$el.data( 'column_id' );
			self.sectionId = self.$el.data( 'section_id' );
			self.postId = self.$el.data( 'post_id' );
			
			self.setChosen();

			return this;
		},


		/**
		 * Refresh the column html
		 *
		 * @return void
		 */
		refresh: function(){

			var self = this;

			self.$( '.loader' ).addClass( 'show' );

			var data = {

				action: 'refreshColumn',
				column_id: self.columnId,
				post_id: self.postId,
				section_id: self.sectionId,
				type: self.$( '.column-controls .type-select' ).val()
			}

			jQuery.post( ajaxurl, data, function( response ){

                try{

                    self.checkAjaxResponse( response );
                    self.$el.replaceWith(response);

                    SectionBuilder.refresh();
                    refreshFields();

                }catch( e ){

                    console.log(response);
                    console.log( e );
                }

			});

		},


		/**
		 * Launch this columns lightbox
		 * @param  event e
		 * @return void
		 */
		launchLightbox: function( e ){

			var self = this;
			e.preventDefault();

			


			if( self.$('.edit-btn' ).hasClass( 'no-lightbox' ) ){

				self.mediaLightbox();

			}else{

				self.$('.lightbox').addClass('active');

			}

		},

		/**
		 * Show a media lightbox
		 *
		 * @return void
		 */
		mediaLightbox: function(){

			var self = this;

			var options = {
				title:'Uploaden',
				button:'Opslaan',
				//media_type:'image',
				multiple:false,
				self: self,
			}


			var properties = {};
			var fullId = self.fullId;

			Media.uploader( options, function( attachment, options ){
				var properties = {

					id: attachment.id,
					thumb:  ( attachment.sizes.thumbnail !== undefined ) ?
							  attachment.sizes.thumbnail.url : 'false',
					medium: ( attachment.sizes.medium !== undefined ) ?
							  attachment.sizes.medium.url : 'false',
					large:  ( attachment.sizes.large !== undefined ) ?
							  attachment.sizes.large.url : 'false',
					full:   ( attachment.sizes.full !== undefined ) ?
							  attachment.sizes.full.url : 'false',

					orientation: attachment.sizes.full.orientation,
					position: self.$('.column-position').val()

				}
				options.self.saveProperties( properties );
			});
		},

		/**
		 * Close this columns lightbox
		 * @param  event e
		 * @return void
		 */
		closeLightbox: function( e ){

			var self = this;

			if( e !== undefined )
				e.preventDefault();

			self.$('.lightbox').removeClass( 'active' );

		},


		/**
		 * Save this columns contents
		 * @param  event e
		 * @return bool
		 */
		saveColumn: function( e ){

			var self = this;
			
			if( typeof( e ) != 'undefined' )
				e.preventDefault();

			var properties = {};
			var inputs = self.$('.lightbox .field-wrapper .field, .lightbox .field-wrapper .subfield:checked');

			for( var i = 0; i <= inputs.length; i++ ){

				var input = jQuery( inputs[ i ] );

				//multi dimensional inputs we'll handle later
				if( input.hasClass( 'multi') == false ){

					if( input.val() !== undefined && input.attr( 'name' ) !== undefined && input.attr('disabled') == undefined ){

						var value = input.val();
						var name = input.attr('name');

						if( input.hasClass( 'type-checkbox' ) && input.is(':checked') === false )
							value = 'false';

						if(input.hasClass( 'data-name' ) )
							name = input.data('name');

						properties[ name ] = value;

					}
				}
			}

			//add the position:
			var _val = self.$('.column-position').val();
			properties[ 'position' ] = _val;

			//add the editor content
			if( self.$( '.lightbox .editor-wrapper' ).length > 0 ){

				self.$( '.lightbox .editor-wrapper' ).each( function( item ){

					var _id = jQuery( this ).data( 'id' );
					var _name = jQuery( this ).data( 'name' );
					properties[ _name ] = tinyMCE.get( _id ).getContent({ format : 'raw' });

				});


			}


			//add multi-dimensional arrays:
			if( self.$('.multi').length > 0 ){

				properties = self.getMultiFields( properties );

			}

			self.saveProperties( properties );
		},

		/**
		 * Save and close the lightbox
		 * 
		 * @param  Event event
		 * 
		 * @return void
		 */
		saveAndClose: function( event ){

			/*var self = this;

			if( self.$('.lightbox').hasClass( 'active' ) && event.keyCode == 13 ){
				self.saveColumn();
				self.closeLightbox();
			}*/
		},

		/**
		 * Save multidimensional arrays of fields
		 *
		 * @param  object properties
		 * @return multidimensional object
		 */
		getMultiFields: function( properties ){

			var self = this;
			var inputs = self.$('.lightbox .field-wrapper .multi');
			retloop: for( var a = 0; a <= inputs.length; a++ ){

				var input = jQuery( inputs[ a ] );
				var val = input.val();
				var type = input.attr('type');
				var disabled = input.attr('disabled');
				var name = input.attr('name');

				//check if checked:
				if( ( type == 'checkbox' || type == 'radio' ) && input.is( ':checked' ) === false )
					continue retloop;

				//overwrite the name for title radio buttons:
				if( input.hasClass( 'title-radio') == true || input.hasClass( 'data-name' ) )
					name = input.data('name');

				if( name !== undefined && disabled == undefined ){

       				var parts = name.split('[');
       				var last = properties;

        			for (var i in parts) {

        			    var part = parts[i];
        			    if (part.substr(-1) == ']') {
        			        part = part.substr(0, part.length - 1);
        			    }

        			    if (i == parts.length - 1) {

        			    	if( last[part] === undefined )
        			    		last[part] = {}

        			        last[part] = val;
        			        continue retloop;

        			    } else if (!last.hasOwnProperty(part)) {
        			        last[part] = {}

        			    }

        			    last = last[part];

        			}
       			}
			}

			return properties;
		},



		/**
		 * Save a media column
		 *
		 * @param  {[type]} properties [description]
		 * @return {[type]}            [description]
		 */
		saveProperties: function( properties ){

			var self = this;
			self.$( '.loader' ).addClass( 'show' );

			var data = {
                'action' 		: 'saveColumnProperties',
                'column_id'		: self.columnId,
                'post_id'		: self.postId,
                'section_id'	: self.sectionId,
                'full_id'		: self.fullId,
                'type' 			: self.$( '.column-controls .type-select' ).val(),
                'properties'	: properties
			};


			jQuery.post( ajaxurl, data, function( response, text, xhr ){

                try{

                    self.checkAjaxResponse( response, xhr );
                
                    self.closeLightbox();

                    self.$el.replaceWith( response );

                    SectionBuilder.refresh();
                    refreshFields();
                
                }catch( e ){

                    console.log(response);
                    console.log( e );
                }
			});
		},



		/**
		 * Change the type of a column
		 *
		 * @return void
		 */
		changeType: function( el ){

			var self = this;
			var type = jQuery( el.target ).val();

			self.$( '.loader' ).addClass( 'show' );

			var data = {
				'action' 		: 'saveColumnType',
				'post_id' 		: self.postId,
				'column_id'		: self.columnId,
				'section_id'	: self.sectionId,
				'type'			: type
			};

			jQuery.post( ajaxurl, data, function( response ){

				self.$el.replaceWith( response );
				SectionBuilder.refresh();
				refreshFields();

			});

		},

		/**
		 * Set the chosen library for column selection
		 */
		setChosen: function(){

			var self = this;
			self.$el.find('.column-controls select.type-select').chosen();
		},

		destroy: function(){
			this.undelegateEvents();
		},


        checkAjaxResponse: function (response, xhr) {

            var self = this;

            if (typeof (xhr) !== 'undefined' && typeof( xhr.status ) !== 'undefined' ){
                if( parseInt( xhr.status ) !== 200 ){
                    throw( 'Status: '+xhr.status );
                } 
            }


            /**
             * Check if the string is html:
             */
            if( self.isHtml( response ) === false ){
                throw( 'No valid html' );
            }
            
            if( typeof(response.error) !== 'undefined' && response.error == true) {

                if (typeof (response.message) !== 'undefined')
                    throw (response.message);

                throw ('General ajax error');
            }

            return true;
        },

        isHtml: function( response ){
            var a = document.createElement('div');
            a.innerHTML = response;

            for (var c = a.childNodes, i = c.length; i--;) {
                if (c[i].nodeType == 1) return true;
            }

            return false;
        }

	});

