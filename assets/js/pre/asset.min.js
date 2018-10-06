
function elevateAdminAjax( specificAction, additionalParams, callback ) {
	var data = {
		'action': 'elevate_ajax',
		'elevate_action': specificAction,
		'elevate_nonce': ElevateData.elevate_nonce
	};

	// Add our parameters to the primary AJAX ones
	for ( var key in additionalParams ) {
	    if ( additionalParams.hasOwnProperty( key  )) {
	    	data[ key ] = additionalParams[ key ];
	    }
	}	

	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	jQuery.post( ElevateData.admin_ajax, data, function( response ) {
		callback( response );
	});
}
function elevateIsGutenberg() {
	var guten = jQuery( '.gutenberg__editor' );
	return ( guten.length );
}

function elevateUpdateGooglePreview() {
	var titlePlaceholder = jQuery( '#elevate_post_title' ).attr( 'placeholder' );
	var titleValue = jQuery( '#elevate_post_title' ).val();

	if ( titleValue.length ) {
		jQuery( '.google-title' ).html( titleValue );
		jQuery( '.search-title' ).html( titleValue );
	} else {
		jQuery( '.google-title' ).html( titlePlaceholder );
		jQuery( '.search-title' ).html( titlePlaceholder );
	}

	var titleDesc = jQuery( '#elevate_post_description' ).val();
	if ( titleDesc.length ) {
		jQuery( '.google-desc' ).html( titleDesc );
		jQuery( '.search-desc' ).html( titleDesc );
		jQuery( '.twitter-desc' ).html( titleDesc );
	} else {
		jQuery( '.google-desc' ).html( jQuery( '#elevate_post_description' ).attr( 'placeholder' ) );
		jQuery( '.search-desc' ).html( jQuery( '#elevate_post_description' ).attr( 'placeholder' ) );
		jQuery( '.twitter-desc' ).html( jQuery( '#elevate_post_description' ).attr( 'placeholder' ) );
	}

	var socialImage = false;
	var thumbnailBehaviour = jQuery.parseJSON( ElevateData.thumbnail_behaviour );

	var featuredImage = 0;

	var canChange = 1;
	if ( elevateIsGutenberg() ) {
		featuredImage = jQuery( '.editor-post-featured-image img' );

		// Check to see if the primary area is still open
		if ( !jQuery( '.editor-post-featured-image' ).length ) {
			canChange = 0;
		}
	} else {
		featuredImage = jQuery( '#set-post-thumbnail img' );	
	}
	
	if ( featuredImage.length && featuredImage.attr( 'src' ) && ( thumbnailBehaviour == 'featured' || thumbnailBehaviour == 'include_content' ) ) {
		socialImage = featuredImage.attr( 'src' );
	}

	if ( ElevateData.first_post_image && thumbnailBehaviour == 'include_content' && !socialImage ) {
		socialImage = jQuery.parseJSON( ElevateData.first_post_image );
	}

	// Set the image to the default image to start
	if ( ElevateData.default_image_url && ( !socialImage || thumbnailBehaviour == 'force_global' ) ) {
		socialImage = jQuery.parseJSON( ElevateData.default_image_url );
	}

	// update
	currentImage = jQuery( '#social_preview .twitter-preview img' ).attr( 'src' );
	if ( currentImage != socialImage && canChange ) {
		jQuery( '#social_preview .previews img' ).attr( 'src', socialImage );

		if ( socialImage ) {
			jQuery( '#social_preview .previews img' ).show();
		} else {
			jQuery( '#social_preview .previews img' ).hide();
		}
	}
}

var elevateUpdatingTitle = 0;

function elevateUpdateMetaTitle() {
	var titleInfo = jQuery( '#title' ).val();
	
	if ( titleInfo.length ) {
		var params = {
			content_title: jQuery( '#title' ).val(),
			content_desc: jQuery( '#content' ).val(),
			content_id: ElevateData.post_id
		};

		if ( !elevateUpdatingTitle ) {
			elevateUpdatingTitle = 1;

			elevateAdminAjax( 'get_updated_title', params, function( response ) {
				var decodedResponse = jQuery.parseJSON( response );
				jQuery( '#elevate_post_title' ).attr( 'placeholder', decodedResponse.body.new_title );

				if ( ElevateData.intelligent_desc == 1 ) {
					jQuery( '#elevate_post_description' ).attr( 'placeholder', decodedResponse.body.new_desc );
				}

				elevateUpdateGooglePreview();
				elevateUpdateContentAnalytics();

				elevateUpdatingTitle = 0;
			});				
		}
		
	} 
}

function elevateGutenbergScan() {
	titleInfo = jQuery( '.editor-post-title__input' ).html();

	var textContent = jQuery( '.mce-content-body' );
	var descContent = '';
	var count = 0;
	textContent.each( function() {
		var thisField = jQuery( this );

		if ( thisField.length ) {
			var toAdd = jQuery( this ).text();

			if ( toAdd && typeof toAdd != undefined && toAdd.length && toAdd != 'undefined' ) {
				if ( count ) {
					descContent = descContent + ' ' + jQuery.trim( toAdd );
				} else {
					descContent = jQuery.trim( toAdd );	
					count = 1;
				}	
			}	
		}
	});

	var params = {
		content_title: titleInfo,
		content_desc: descContent,
		content_id: ElevateData.post_id
	};

	if ( !elevateUpdatingTitle ) {
		elevateUpdatingTitle = 1;

		elevateAdminAjax( 'get_updated_title', params, function( response ) {
			var decodedResponse = jQuery.parseJSON( response );
			jQuery( '#elevate_post_title' ).attr( 'placeholder', decodedResponse.body.new_title );

			if ( ElevateData.intelligent_desc == 1 ) {
				jQuery( '#elevate_post_description' ).attr( 'placeholder', decodedResponse.body.new_desc );
			}

			elevateUpdateGooglePreview();
			elevateUpdateContentAnalytics();

			elevateUpdatingTitle = 0;
		});		
	}	
}

var hasTitleContent = 0;
var hasDescContent = 0;

function elevateUpdateContentAnalytics() {
	var critical = 0;
	var warning = 0;

	var title;
	if ( elevateIsGutenberg() ) {
		title = jQuery( '#post-title-0' ).val();	
	} else {
		title = jQuery( '#title' ).val();
	}

	if ( ElevateData.is_new_page == 1 && !hasTitleContent && !hasDescContent ) {
		jQuery( '.desc .waiting' ).show();
		jQuery( '.desc .bad, .desc .good, .desc .warning' ).hide();	
	} else {
		// scan for critical bugs
		if ( typeof title != undefined && !title.length ) {
			jQuery( '#elevate-no-title' ).show();
			critical = 1;
		} else {
			jQuery( '#elevate-no-title' ).hide();
		}

		var seoTitle = jQuery( '#elevate_post_title' );
		var title = seoTitle.val();
		if ( !title.length ) {
			title = seoTitle.attr( 'placeholder' );
		}

		if ( title.length > 70 ) {
			warning = 1;
			jQuery( '#elevate-title-long' ).show();
		} else {
			jQuery( '#elevate-title-long' ).hide();
		}

		if ( title.length < 20 ) {
			warning = 1;
			jQuery( '#elevate-title-short' ).show();
		} else {
			jQuery( '#elevate-title-short' ).hide();
		}

		var seoDesc = jQuery( '#elevate_post_description' );
		var desc = seoDesc.val();
		if ( !desc.length ) {
			desc = seoDesc.attr( 'placeholder' );
		}

		if ( desc.length > 300 ) {
			warning = 1;
			jQuery( '#elevate-desc-long' ).show();
		} else {
			jQuery( '#elevate-desc-long' ).hide();
		}	

		if ( desc.length < 50 ) {
			warning = 1;
			jQuery( '#elevate-desc-short' ).show();
		} else {
			jQuery( '#elevate-desc-short' ).hide();
		}

		var isGutenberg = elevateIsGutenberg();
		if ( critical ) {
			jQuery( '.desc .bad' ).show();
			jQuery( '.desc .good, .desc .warning, .desc .waiting' ).hide();

			if ( isGutenberg ) {
				jQuery( 'button[aria-label="Elevate SEO"]' ).css( 'background', 'rgba( 255, 0, 0, 0.4 )' );
			}
		} else if ( warning ) {
			jQuery( '.desc .warning' ).show();
			jQuery( '.desc .bad, .desc .good, .desc .waiting' ).hide();		

			if ( isGutenberg ) {
				jQuery( 'button[aria-label="Elevate SEO"]' ).css( 'background', 'rgba( 255, 165, 0, 0.4 )' );

			}
		} else {
			jQuery( '.desc .good' ).show();
			jQuery( '.desc .bad, .desc .warning, .desc .waiting' ).hide();			

			if ( isGutenberg ) {
				jQuery( 'button[aria-label="Elevate SEO"]' ).css( 'background', 'inherit' );
			}
		}

		if ( critical || warning ) {
			jQuery( '#elevate-all-good' ).hide();
		} else {
			jQuery( '#elevate-all-good' ).show();
		}
	}
}

function elevateDoGutenberg() {
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var PluginSidebar = wp.editPost.PluginSidebar;
	var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
	var registerPlugin = wp.plugins.registerPlugin;

	function Component() {
	    return el(
	        Fragment,
	        {},
	        el(
	            PluginSidebarMoreMenuItem,
	            {
	                target: 'elevate-seo',
	            },
	            'Elevate SEO'
	        ),
	        el(
	            PluginSidebar,
	            {

	                name: 'elevate-seo',
	                title: 'Elevate SEO',

	            },
	            el(	
	            	'div',
	            	{
	            		class: 'guten side-box'
	            	},
	            	el(
		        		'div',
			        	{
			        		class: 'status desc'
			        	},
			        	el(
			        		'span',
			        		{
			        			class: 'waiting',
			        		},
			        		el(
			        			'i',
			        			{
			        				class: 'fa fa-pencil'
			        			},
			        			' '
			        		),
			        		el(
			        			'span',
			        			'',
			        			ElevateData.guten_not_enough	
			        		)
			        	),
			        	el(
			        		'span',
			        		{
			        			class: 'good',
			        		},
			        		el(
			        			'i',
			        			{
			        				class: 'fa fa-check'
			        			},
			        			' '
			        		),
			        		el(
			        			'span',
			        			'',
			        			ElevateData.guten_all_good	
			        		)
			        	),
			        	el(
			        		'span',
			        		{
			        			class: 'warning',
			        		},
			        		el(
			        			'i',
			        			{
			        				class: 'fa fa-warning'
			        			},
			        			' '
			        		),
			        		el(
			        			'span',
			        			'',
			        			ElevateData.guten_a_few_adjustments	
			        		)
			        	),
			        	el(
			        		'span',
			        		{
			        			class: 'bad',
			        		},
			        		el(
			        			'i',
			        			{
			        				class: 'fa fa-exclamation'
			        			},
			        			' '
			        		),
			        		el(
			        			'span',
			        			'',
			        			ElevateData.guten_serious	
			        		)
			        	)			        			        	
		        	),
		        	el(
		        		'a',
		        		{
		        			class: 'social_preview_open learn-more button'
		        		},
		        		ElevateData.guten_web_preview
		        	),
		        	el(
		        		'a',
		        		{
		        			class: 'learn_more_open learn-more button'
		        		},
		        		ElevateData.guten_learn_more
		        	),
		        	el(
		        		'span',
		        		{
		        			class: 'clearer'
		        		},
		        		''
		        	)
	            )
	        )
	    );
	}

	registerPlugin( 'elevate-seo', {
	    icon: 'search',
	    render: Component
	} );		

	jQuery( '.edit-post-pinned-plugins button' ).live( 'click', function() {
		setTimeout(
			function() { elevateUpdateContentAnalytics(); },
			5
		);
	});
}

function doElevateMetaReady() {
	Opentip.defaultStyle = "dark";

	var currentImage = 0;
	if ( elevateIsGutenberg() ) {
		// Do Gutenberg specific code

		// Check for main title changes
		jQuery( '#post-title-0' ).live( 'blur', function() {
			hasTitleContent = 1;

			elevateGutenbergScan();
		});

		jQuery( '.mce-content-body' ).live( 'blur', function() {
			elevateGutenbergScan();
		});

		elevateDoGutenberg();

		setInterval( 
			function() {
				var featuredImage = jQuery( '.editor-post-featured-image img' );	
				if ( featuredImage ) {
					var featuredSrc = featuredImage.attr( 'src' );

					if ( featuredSrc != currentImage ) {
						elevateUpdateGooglePreview();

						currentImage = featuredSrc;
					}
				}
			},
			500
		);
	
	} else {
		jQuery( '#title' ).on( 'blur', function() {
			hasTitleContent = 1;

			elevateUpdateMetaTitle();
		});

		jQuery( '#content' ).on( 'blur', function() {
			elevateUpdateMetaTitle();
		});	

		setInterval( 
			function() {
				var featuredImage = jQuery( '#set-post-thumbnail img' );	
				if ( featuredImage ) {
					var featuredSrc = featuredImage.attr( 'src' );

					if ( featuredSrc != currentImage ) {
						elevateUpdateGooglePreview();

						currentImage = featuredSrc;
					}
				}
			},
			500
		);		
	}

	// Let's update the previews
	setTimeout( 
		function() {
			elevateUpdateGooglePreview();
			elevateUpdateContentAnalytics();
		}, 
		10
	);
	
	jQuery( '#social_preview, #learn_more, #wizard_issue' ).popup({
		opacity: 0.3,
		transition: 'all 0.3s'
	});	

	jQuery( '#social_preview li i' ).click( function() {
		jQuery( '#social_preview li i' ).removeClass( 'active' );
		jQuery( this ).addClass( 'active' );

		var targetTab = jQuery( this ).attr( 'data-tab' );

		jQuery( 'div.google-preview, div.twitter-preview, div.facebook-preview, div.linkedin-preview' ).hide();
		jQuery( 'div.' + targetTab ).show();
	});
}

jQuery( document ).ready( function() { 
	var items = jQuery( '#post-title-0, #title' );
	if ( items.length || jQuery( 'body' ).hasClass( 'gutenberg-editor-page' ) || ElevateData.is_editing_term != '0' ) {
		doElevateMetaReady(); 	
	}
} );


var elevateGoogleAuthWindow;
var elevateProgressBar = 0;

function elevateHandleUploads() {
	jQuery( 'button.upload' ).each( function() {
		var button = jQuery( this );
		var settingName = jQuery( this ).attr( 'data-name' );
		var myForm = jQuery( '#wpwrap' ).append( 
			'<div class="upload-hide"><form method="POST" id="' + settingName + '_form" action="/" id="' + settingName + '_form">' +
				'<input type="FILE" id="' + settingName + '" name="' + settingName + '_file" />' +
				'<input type="hidden" name="file_key" value="' + settingName + '_file" />' +
				'<input type="submit" id="' + settingName + '_submit" name="' + settingName + '_submit" />' +
			'</form></div>'
		);	

		jQuery( 'form#' + settingName + '_form' ).submit( function( formEvent ) {
			var formElement = jQuery( 'form#' + settingName + '_form' ).get( 0 );
			var formData = new FormData( formElement );
			formData.append( 'action', 'elevate_ajax' );
			formData.append( 'elevate_action', 'file_upload' );
			formData.append( 'elevate_nonce', ElevateData.elevate_nonce );
			formData.append( 'acceptable_types', 'image/png,image/jpeg,image/jpeg' );

			jQuery.ajax({
				url: ElevateData.admin_ajax,
				type: "POST",
				data: formData,
				contentType: false,
				cache: false,
				processData: false,
				success: function( data ) {
					var result = jQuery.parseJSON( data );

					if ( result.code == 0 ) {
						// success
						//alert( result.body.file_name );
						jQuery( 'input[name=elevate_' + settingName + ']' ).val( result.body.file_name );
						jQuery( 'a[data-name=' + settingName + ']' ).attr( 'href', result.body.full_file_url );
						button.hide();

						jQuery( '.upload_' + settingName + ' .image-wrapper' ).html( '<img src="' + result.body.full_file_url + '" />' );
						jQuery( '#' + settingName + '_reset').show();

					} else {
						if ( result.error == 'invalid_mime_type' ) {
							alert( ElevateData.msg_invalid_mime );
						} else {
							alert( 'Failed!' );
						}
					}
				}
			});

			formEvent.preventDefault();
		});

		var thisValue = jQuery( 'input[name=elevate_' + settingName + ']' ).val();
		if ( thisValue.length ) {
			button.hide();
		} else {
			button.show();
			jQuery( 'input[name=elevate_' + settingName + ']' ).hide();
			jQuery( '#' + settingName + '_reset').hide();
		}

		jQuery( '#' + settingName + '_reset' ).click( function( e ) {
			var settingName = jQuery( this ).attr( 'data-name' );
			jQuery( 'input[name=elevate_' + settingName + ']' ).val( '' );
			jQuery( '.upload_' + settingName + ' .image-wrapper' ).html( '' );
			e.preventDefault();

			button.show();
			jQuery( 'input[name=elevate_' + settingName + ']' ).hide();
			jQuery( '#' + settingName + '_reset').hide();
		});
	});

	jQuery( 'button.upload' ).click( function( e ) { 
		e.preventDefault();
		
		var settingName = jQuery( this ).attr( 'data-name' );
		jQuery( '#' + settingName ).click().on( 'change', function( c ) {
			jQuery( '#' + settingName + '_submit' ).click();
		});
	});
}

function elevateCheckServices( callback ) {
	if ( jQuery( '.google-services' ).length ) 	{
		var params = {};
		elevateAdminAjax( 'check_all_services', params, function( response ) {
			callback( response );
		});
	}
}

function elevateHandleWizard( step, doSearch, doCreateAnalytics, doAddAnalytics, localStep, callback ) {
	jQuery( '.site-setup li' ).hide();

	// doSearch has potentially 7 steps
	// doCreateAnalytics has potentially 1 steps
	// doAddAnalytics has potentially 1 steps

	var totalSteps = 0;
	if ( doSearch ) {
		totalSteps += 8;
	}

	if ( doCreateAnalytics ) {
		totalSteps += 1;
	}

	if ( doAddAnalytics ) {
		totalSteps += 1;
	}

	var currentPercentage = localStep/totalSteps;

	if ( elevateProgressBar != 0 ) {
		var strPercentage = currentPercentage*100;
		elevateProgressBar.animate( currentPercentage, function() {  elevateProgressBar.setText( strPercentage.toFixed(0).toString() + '%' ); });		
	}

	if ( elevateProgressBar == 0 ) {
	 	elevateProgressBar = new ProgressBar.SemiCircle('#pb', {
	        color: '#7c7',
	        trailColor: '#ddd',
	        duration: 300,
	        easing: 'easeInOut',
	        strokeWidth: 5,
	   	 	trailWidth: 2,
	   	 	text: {
	   	 		value: '0%'

	   	 	},
	   	 	alignToBottom: true,
	    });
	} 

	switch( step ) {
		case 1:
			// generate a new sitemap
			jQuery( 'li.generate-sitemap' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'generate_sitemap', {}, function( response ) {
				var result = JSON.parse( response );
				if ( result.code == ElevateData.ajax_success ) {
					jQuery( 'li.generate-sitemap' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );

					// Figure out our starting point
					if ( doSearch ) {
		    			elevateHandleWizard( 2, doSearch, doCreateAnalytics, doAddAnalytics, 1, callback );
		    		} else if ( doCreateAnalytics ) {
		    			elevateHandleWizard( 9, doSearch, doCreateAnalytics, doAddAnalytics, 1, callback );
		    		} else if ( doAddAnalytics ) {
		    			elevateHandleWizard( 10, doSearch, doCreateAnalytics, doAddAnalytics, 1, callback );
		    		}
				} 
			});
			break;			
		case 2:
			// See if site exists on Search Console
			jQuery( 'li.check-sc-site' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'google_check_sites', {}, function( response ) {
				var result = JSON.parse( response );

				if ( result.code == ElevateData.ajax_success ) {	
					jQuery( 'li.check-sc-site' ).addClass( 'success' ).find( 'i').addClass( 'flip' );

					elevateHandleWizard( 4, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 2, callback );
				} else {
					jQuery( 'li.check-sc-site' ).addClass( 'fail' ).find( 'i').addClass( 'flip' );

					// We need to add the site to Google
					jQuery( 'li.add-sc-site' ).fadeIn();	

					elevateHandleWizard( 3, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );
				}				
			});
			break;
		case 3:
			// Add site on Google
			jQuery( 'li.add-sc-site' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'google_add_site', {}, function( response ) {
				var result = JSON.parse( response );

				if ( result.code == ElevateData.ajax_success ) {
					// Site successfully added
					jQuery( 'li.add-sc-site' ).addClass( 'success' ).find( 'i').addClass( 'flip' );

					// Let's turn the previous frown upside down
					jQuery( 'li.check-sc-site' ).removeClass( 'fail' ).find( 'i' ).removeClass( 'flip' );
					jQuery( 'li.check-sc-site' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );

					elevateHandleWizard( 4, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );
				} else {
					// Site didn't exist, and we couldn't add it; not sure why this would occur
					jQuery( 'li.add-sc-site' ).addClass( 'fail' ).find( 'i' ).addClass( 'flip' );
				}
			});
			break;
		case 4:
			// Check if site is verified
			jQuery( 'li.check-verification' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'check_site_verification', {}, function( response ) {
				var result = JSON.parse( response );

				if ( result.code == ElevateData.ajax_success ) {
					jQuery( 'li.verify-site' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );

					elevateHandleWizard( 6, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 2, callback );
				} else {
					elevateHandleWizard( 5, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );
				}
			});
			break;		
		case 5:
			// Verify site
			jQuery( 'li.verify-site' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'verify_site', {}, function( response ) {
				var result = JSON.parse( response );

				if ( result.code == ElevateData.ajax_success ) {
					jQuery( 'li.verify-site' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );

					elevateHandleWizard( 6, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );
				} else {
					jQuery( 'li.verify-site' ).addClass( 'fail' ).find( 'i' ).addClass( 'flip' );
					
				    jQuery( '#config-error' ).popup({
				    	opacity: 0.3,
				    	autoopen: true,
						transition: 'all 0.3s',
						onclose: function() {
							if ( doCreateAnalytics ) {
								elevateHandleWizard( 9, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 4, callback );	
							} else if ( doAddAnalytics ) {
								elevateHandleWizard( 10, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 5, callback );	
							} else {
								elevateHandleWizard( 50, doSearch, doCreateAnalytics, doAddAnalytics, totalSteps, callback );
							}
						}
				    });
				}
			});
			break;		
		case 6:
			// check sitemap on google search
			jQuery( 'li.check-sitemap' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'check_sitemap', {}, function( response ) {
				var result = JSON.parse( response );
				if ( result.code == ElevateData.ajax_success ) {
					jQuery( 'li.check-sitemap' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );

					// Sitemap already exists and is submitted
					if ( doCreateAnalytics ) {
						elevateHandleWizard( 9, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 3, callback );
					} else if ( doAddAnalytics ) {
						elevateHandleWizard( 10, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 4, callback );
					}
				} else {
					jQuery( 'li.check-sitemap' ).addClass( 'fail' ).find( 'i' ).addClass( 'flip' );

					elevateHandleWizard( 8, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );
				}
			});
			break;					
		case 8:
			// add sitemap to google search
			jQuery( 'li.add-sitemap' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'add_sitemap', {}, function( response ) {
				var result = JSON.parse( response );
				if ( result.code == ElevateData.ajax_success ) {
					jQuery( 'li.add-sitemap' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );

					// Change the X to a Check on previous step
					jQuery( 'li.check-sitemap' ).removeClass( 'fail' ).find( 'i' ).removeClass( 'flip' );
					jQuery( 'li.check-sitemap' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );

					if ( doCreateAnalytics ) {
						elevateHandleWizard( 9, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );	
					} else if ( doAddAnalytics ) {
						elevateHandleWizard( 10, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 2, callback );	
					} else {
						elevateHandleWizard( 50, doSearch, doCreateAnalytics, doAddAnalytics, totalSteps, callback );
					}
				} else {
					jQuery( 'li.add-sitemap' ).addClass( 'fail' ).find( 'i' ).addClass( 'flip' );

					jQuery.confirm({
					    title: ElevateData.msg_setup_error_title,
					    content: ElevateData.msg_setup_cant_add_sitemap,
					    type: 'blue',
					    typeAnimated: true,
					    buttons: {
					        close: function () {		        	
								if ( doCreateAnalytics ) {
									elevateHandleWizard( 9, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );	
								} else if ( doAddAnalytics ) {
									elevateHandleWizard( 10, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 2, callback );	
								} else {
									elevateHandleWizard( 50, doSearch, doCreateAnalytics, doAddAnalytics, totalSteps, callback );
								}
					        }
					    },
					    useBootstrap: false
				    });					
				}

			});
			break;		
		case 9:
			// Create google analytics property
			jQuery( 'li.create-analytics-property' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'create_analytics_property', {}, function( response ) {
				var result = JSON.parse( response );
				if ( result.code == ElevateData.ajax_success ) {
					jQuery( 'li.create-analytics-property' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );
				} else {
					jQuery( 'li.create-analytics-property' ).addClass( 'fail' ).find( 'i' ).addClass( 'flip' );
				}

				if ( doAddAnalytics ) {
					elevateHandleWizard( 10, doSearch, doCreateAnalytics, doAddAnalytics, localStep + 1, callback );	
				} else {
					elevateHandleWizard( 50, doSearch, doCreateAnalytics, doAddAnalytics, totalSteps, callback );
				}
			});			
			break;
		case 10:
			// Add google analytics property
			jQuery( 'li.add-analytics-code' ).addClass( 'active' ).fadeIn();
			elevateAdminAjax( 'add_analytics_code', {}, function( response ) {
				var result = JSON.parse( response );
				if ( result.code == ElevateData.ajax_success ) {
					jQuery( 'li.add-analytics-code' ).addClass( 'success' ).find( 'i' ).addClass( 'flip' );
				} else {
					jQuery( 'li.add-analytics-code' ).addClass( 'fail' ).find( 'i' ).addClass( 'flip' );
				}

				elevateHandleWizard( 50, doSearch, doCreateAnalytics, doAddAnalytics, totalSteps, callback );
			});			
			break;	
		case 50:
			// finished
			jQuery( 'li.complete' ).addClass( 'active' ).fadeIn();
			callback();

			break;			
	}
}

function elevateSaveWizardState() {
	// Set up mapping
	var language = jQuery( '#elevate_wizard_language' ).val();
	var site_title = jQuery( '#elevate_site_title' ).val();
	var site_name = jQuery( '#elevate_site_name' ).val();
	var home_title = jQuery( '#elevate_home_title' ).val();
	var home_desc = jQuery( '#elevate_site_description' ).val();
	var twitter_name = jQuery( '#elevate_social_twitter_name' ).val();
	var bing_auth_code = jQuery( '#elevate_bing_auth_code' ).val();
	var default_image = jQuery( '#elevate_facebook_default_image' ).val();
	var site_lang = language;

	if ( site_lang == 'auto' ) {
		site_lang = ElevateData.wp_locale;
	}

	//alert( site_title );
	var params = {
		'settings': {
			'site_title': site_title,
			'selected_locale': language,
			'site_name': site_name,
			'home_title': home_title,
			'home_desc': home_desc,
			'social_twitter_name': twitter_name,
			'bing_auth_code': bing_auth_code,
			'facebook_default_image': default_image
		}
	};

	elevateAdminAjax( 'wizard_save_state', params, function( response ) {
	});	
}

function elevateInitialize() {
	function disableButtons( disable ) {
		if ( disable ) {
			jQuery( '.actions li' ).addClass( 'disabled' );
		} else {
			jQuery( '.actions li' ).removeClass( 'disabled' );
		}
	}

	var wizard = jQuery( "#elevate-wizard" );
	if ( wizard ) {
		// enumerate the sections

		var count = 0;
		jQuery( '#elevate-wizard section' ).each( function() {
			jQuery( this ).attr( 'data-pos', count );
			count = count +1;
		});
		var servicesChecked = 0;
		var servicesConfigured = 0;

		var WIZARD_HOSTING = -1;
		var hosting = jQuery( 'section.speed' );
		if ( hosting.length ) {
			WIZARD_HOSTING = parseInt( hosting.attr( 'data-pos' ) );
		}

		var WIZARD_INTRO = parseInt( jQuery( 'section.intro' ).attr( 'data-pos' ) );
		var WIZARD_BASICS = parseInt( jQuery( 'section.basics' ).attr( 'data-pos' ) );
		var WIZARD_SITE_NAME = parseInt( jQuery( 'section.site-name' ).attr( 'data-pos' ) );
		var WIZARD_HOME_TITLE = parseInt( jQuery( 'section.home-title' ).attr( 'data-pos' ) );
		var WIZARD_HOME_DESC = parseInt( jQuery( 'section.description' ).attr( 'data-pos' ) );
		var WIZARD_BRANDING = parseInt( jQuery( 'section.branding' ).attr( 'data-pos' ) );
		var WIZARD_PREVIEW = parseInt( jQuery( 'section.preview' ).attr( 'data-pos' ) );
		var WIZARD_SERVICES_INSPECT = parseInt( jQuery( 'section.google-services' ).attr( 'data-pos' ) );
		var WIZARD_SERVICES_CONFIG = parseInt( jQuery( 'section.google-services-config' ).attr( 'data-pos' ) );
		var WIZARD_SERVICES_SETUP = parseInt( jQuery( 'section.site-setup' ).attr( 'data-pos' ) );
		var WIZARD_DONE = parseInt( jQuery( 'section.done' ).attr( 'data-pos' ) );

		var WIZARD_NEWS = jQuery( 'section.wizard-news' ).attr( 'data-pos' );

		var startStep = 0;
		if ( ( typeof( ElevateData.post_oauth_skip ) !== 'undefined' ) && ElevateData.post_oauth_skip  ) {
			startStep = WIZARD_SERVICES_INSPECT;
		}			

		function checkServices() {
			var servicesArea = jQuery( '.google-services' );
			if ( servicesArea.length ) {
				
				if ( !servicesChecked ) {
					disableButtons( true );

	    			elevateCheckServices( function( response ) { 
	    				disableButtons( false );

	    				// Artificially delay a bit longer so users can read the text

	    				var decodedData = jQuery.parseJSON( response );
	    				setTimeout( function() {
	    					jQuery( '#services-checking' ).hide();
	    					//jQuery( '#services-checked' ).fadeIn();

	    					//if ( decodedData.body.has_google_analytics && decodedData.body.on_search_console ) {
	    					if ( decodedData.body.has_google_analytics ) {	
	    						jQuery( '#services-found' ).fadeIn();

	    						jQuery( '#services-checked li div' ).addClass( 'checked' );
	    					} else {
	    						jQuery( '#services-missing' ).fadeIn();

	    						jQuery( '#config-search-console' ).addClass( 'checked' );
	    						jQuery( '#config-add-analytics, #config-install-analytics' ).addClass( 'disabled' );
	    					}

	    					servicesChecked = true;
	    				}, 500 );
	    			});
	    		}	
	    	}		
		}

		wizard.steps({
			startIndex: startStep,
		    headerTag: "h3",
		    titleTemplate: '<span class="number">#index#</span> #title#',
		    bodyTag: "section",
		    transitionEffect: "slideLeft",
		    autoFocus: true,
		    onInit: function( e, CurrentIndex ) {
		    	jQuery( '.elevate #elevate-wizard' ).fadeIn( 200 );

		    	if ( startStep == WIZARD_SERVICES_INSPECT ) {
		    		checkServices();
		    	}

		    	jQuery( '.steps li' ).prepend( '<div class="connector"><div class="dot"></div></div>' );
		    },
		    onFinished: function( e, currentIndex ) {
		    	elevateAdminAjax( 'wizard_done', {}, function( response ) {
    				window.location.href = ElevateData.settings_general_page;
				});	
		    },
		    onStepChanging: function( e, currentIndex, newIndex ) {
		    	elevateSaveWizardState();

		    	if ( currentIndex == WIZARD_SERVICES_INSPECT ) {
		    		var servicesArea = jQuery( '.google-services' );
		    		if ( servicesArea.length ) {
		    			return servicesChecked;
		    		}

		    		return true;
		    	} else if ( currentIndex == ( WIZARD_SERVICES_CONFIG ) ) {
		    		// on Google authentication page
		    		//alert( "You haven't configured Search Console - are you sure you want to proceed?" )
		    	} else if ( currentIndex == WIZARD_SERVICES_CONFIG + 1 ) {
		    		return servicesConfigured;
		    	} else if ( currentIndex == WIZARD_HOME_DESC ) {
		    		var siteName = jQuery( '#elevate_site_name' ).val();
		    		var homeTitle = jQuery( '#elevate_home_title' ).val();
		    		var homeDesc = jQuery( '#elevate_site_description' ).val();

		    		jQuery( '#google-title' ).html( homeTitle );
		    		jQuery( '#google-desc' ).html( homeDesc );
		    	} else if ( currentIndex == WIZARD_BRANDING ) {
		    		var previewImageDom = jQuery( '.branding .image-wrapper img' );
		    		if ( previewImageDom.length ) {
			    		var previewImage = previewImageDom.attr( 'src' );
			    		if ( previewImage.length ) {
			    			jQuery( '#google-image' ).css( 'background', 'url("' + previewImage + '")' ).show();
			    		} else {
			    			jQuery( '#google-image' ).css( 'background', 'inherit' ).hide();
			    		}	
		    		} else {
		    			jQuery( '#google-image' ).hide();
		    		}
		    	}

		    	return true;
		    },
		    onStepChanged: function( e, currentIndex, priorIndex ) {
		    	if ( currentIndex == WIZARD_SERVICES_INSPECT ) {
		    		checkServices();
		    	} else if ( currentIndex == WIZARD_SERVICES_SETUP && typeof( ElevateData.has_tokens ) !== 'undefined' ) {
		    		// Start our trip to RecursionVille
		    		var doSearchConsole = jQuery( '#config-search-console' ).hasClass( 'checked' );
		    		var doCreateAnalytics = jQuery( '#config-add-analytics' ).hasClass( 'checked' );
		    		var doAddAnalytics = jQuery( '#config-install-analytics' ).hasClass( 'checked' );

		    		if ( !servicesConfigured ) {
		    			disableButtons( true );

			    		elevateHandleWizard( 1, doSearchConsole, doCreateAnalytics, doAddAnalytics, 0, function() {
			    			servicesConfigured = true;
			    			
			    			disableButtons( false );
			    		} );	
		    		}
		    		
		    	} else if ( currentIndex == WIZARD_DONE ) {
		    		var value = jQuery( '#elevate_wizard_email' ).val();

		    		if ( value ) {
		    			elevateAdminAjax( 'subscribe_mailing_list', { email: value }, function( response ) {
		    				//alert( response );
						});	
		    		}
		    	}
		    }
		});
	}

	// Reinit Opentip
	Opentip.defaultStyle = "dark";
	Opentip.findElements();

	elevateHandleUploads();

	// Set up default social media icon
	var defaultImage = jQuery.parseJSON( ElevateData.default_image );
	if ( defaultImage.length ) {
		jQuery( '.branding button.upload' ).hide();
		jQuery( '.branding button.remove' ).show();
		jQuery( '.branding a.link' ).attr( 'href', jQuery.parseJSON( ElevateData.default_image_url ) );
		jQuery( '.branding input[type=text]' ).val( defaultImage );
		jQuery( '.branding .image-wrapper' ).html( '<img src="' + jQuery.parseJSON( ElevateData.default_image_url ) + '" />' );
	}

	jQuery( '#elevate_bing_auth_code' ).on( 'paste', function( e ) {
		elevateSaveWizardState();
	});

	jQuery( '#elevate_bing_auth_code' ).on( 'blur', function( e ) {
		elevateSaveWizardState();
	});	

	jQuery( '.google-services-config div li div' ).click( function( e ) {
		if ( !jQuery( this ).hasClass( 'disabled' ) ) {
			if ( jQuery( this ).hasClass( 'checked' ) ) {
				jQuery( this ).removeClass( 'checked' );
			} else {
				jQuery( this ).addClass( 'checked' );
			}
		}

		e.preventDefault();
	});

	jQuery( '#wizard-fix' ).click( function( e ) {
		e.preventDefault();

		jQuery( this ).html( ElevateData.wizard_try_fix );

		elevateAdminAjax( 'fix_htaccess', {}, function( response ) {
			jQuery( 'ul.needs-fixing .can-fix' ).hide();
			jQuery( 'ul.needs-fixing .good' ).fadeIn( 1000 );

			jQuery( '#wizard-fix' ).html( ElevateData.wizard_fix ).hide();
			jQuery( 'p.fix-notice' ).show();
		});	
	});

	jQuery( '#elevate_search_console_deactivate' ).click( function( e ) {
		elevateAdminAjax( 'revoke_all_tokens', {}, function( response ) {
			window.location.href = window.location.href;
		});

		e.preventDefault();
	});

	jQuery( '#elevate_search_console_activate' ).click( function( e ) {
		window.location.href = ElevateData.oauth_auth_url;
		e.preventDefault();
	});

	jQuery( '#elevate_show_wizard_again' ).click( function( e ) {
		elevateAdminAjax( 'show_wizard', {}, function( response ) {
			window.location.href = ElevateData.settings_wizard_page;
		});

		e.preventDefault();
	});

	jQuery( '#elevate_reset_all_settings' ).click( function( e ) {
		elevateAdminAjax( 'reset_settings', {}, function( response ) {
			window.location.href = ElevateData.settings_wizard_page;
		});

		e.preventDefault();
	});

	var dashboard = jQuery( '.elevate #dashboard' );
	if ( dashboard.length ) {		
		jQuery( '.item-2 img, .item-3 img' ).fadeIn();
		elevateAdminAjax( 'get_dashboard_data', {}, function( response ) {

			jQuery( '.item-2 img, .item-3 img' ).fadeOut();
			var decode = jQuery.parseJSON( response );

			var clicks = decode.body.search_analytics.clicks;
			if ( !clicks ) {
				clicks = 0;
			}			

			if ( ElevateData.has_google_tokens == 1 ) {
				jQuery( '.crawl-errors' ).html( decode.body.crawl_errors.count );	
				jQuery( '.clicks' ).html( clicks );
			}

			jQuery( '.impressions' ).html( decode.body.search_analytics.impressions );

			jQuery( '.not-found' ).html( decode.body.crawl_errors.not_found );
			jQuery( '.not-auth' ).html( decode.body.crawl_errors.permissions );
			jQuery( '.server-error' ).html( decode.body.crawl_errors.server_error );
			jQuery( '.total-errors' ).html( decode.body.crawl_errors.not_found + decode.body.crawl_errors.permissions + decode.body.crawl_errors.server_error );

			jQuery( '.click-rate' ).html( decode.body.search_analytics.ctr );
			jQuery( '.position' ).html( decode.body.search_analytics.position );

			if ( decode.body.has_analytics_installed ) {
				jQuery( '.analytics-installed' ).html( jQuery( '.analytics-installed' ).attr( 'data-tracking' ) );
			} else {
				jQuery( '.analytics-installed' ).html( jQuery( '.analytics-installed' ).attr( 'data-not-tracking' ) );
			}

			if ( decode.body.is_site_verified == 1 ) {
				jQuery( '.site-verify' ).html( jQuery( '.site-verify' ).attr( 'data-verified' ) );
			} else {
				jQuery( '.site-verify' ).html( jQuery( '.site-verify' ).attr( 'data-not-verified' ) );
			}			

			if ( decode.body.sitemap_info.has_sitemap ) {
				if ( decode.body.sitemap_info.is_generating ) {
					jQuery( '.sitemap-generated' ).html( jQuery( '.sitemap-generated' ).attr( 'data-generating' ) );
					jQuery( '.last-modified' ).html( jQuery( '.sitemap-generated' ).attr( 'data-pending' ) );
					jQuery( '.sitemap-entries' ).html( jQuery( '.sitemap-generated' ).attr( 'data-pending' ) );	
				} else {
					jQuery( '.sitemap-generated' ).html( jQuery( '.sitemap-generated' ).attr( 'data-generated' ) );
					jQuery( '.last-modified' ).html( decode.body.sitemap_info.modified_time );
					jQuery( '.sitemap-entries' ).html( decode.body.sitemap_info.entries );	
				}	
			} else {
				jQuery( '.sitemap-generated' ).html( jQuery( '.sitemap-generated' ).attr( 'data-not-generated' ) );
				jQuery( '.last-modified' ).html( jQuery( '.last-modified' ).attr( 'data-none' ) );
				jQuery( '.sitemap-entries' ).html( jQuery( '.last-modified' ).attr( 'data-none' ) );
			}
		});

		jQuery( '.item-1 img' ).fadeIn();
		elevateAdminAjax( 'get_dashboard_data_speed', {}, function( response ) {
			jQuery( '.item-1 img' ).fadeOut();
			var decode = jQuery.parseJSON( response );

			if ( decode.body.desktop.response_bytes == null && decode.body.mobile.response_bytes == null ) {
				// Likely offline
				jQuery( '.desktop-speed' ).html( 0 );
				jQuery( '.mobile-speed' ).html( 0 );
				//jQuery( '.speed-info .desktop-size' ).html( 0 );
				//jQuery( '.speed-info .mobile-size' ).html( 0 );
				//jQuery( '.speed-info .average-speed' ).html( 0 );	
			} else {
				jQuery( '.desktop-speed' ).html( decode.body.desktop.speed );
				jQuery( '.mobile-speed' ).html( decode.body.mobile.speed );

				//jQuery( '.speed-info .desktop-size' ).html( decode.body.desktop.response_bytes );
				//jQuery( '.speed-info .mobile-size' ).html( decode.body.mobile.response_bytes );

				//var num = ( decode.body.desktop.speed + decode.body.mobile.speed ) / 2;
				//jQuery( '.speed-info .average-speed' ).html( num.toFixed( 1 ) );	

				jQuery( '.css-files' ).html( decode.body.desktop.css_resources );
				jQuery( '.css-size' ).html( decode.body.desktop.css_bytes );

				jQuery( '.js-files' ).html( decode.body.desktop.js_resources );
				jQuery( '.js-size' ).html( decode.body.desktop.js_bytes );

				jQuery( '.resource-files' ).html( decode.body.desktop.response_resources );
				jQuery( '.resource-size' ).html( decode.body.desktop.response_bytes );

			}
		});	

		elevateAdminAjax( 'get_dashboard_data_analytics', {}, function( response ) {
			var decode = jQuery.parseJSON( response );

			if ( typeof( decode.body ) !== undefined && typeof ( decode.body.totals ) !== undefined ) {
				jQuery( '.analytics-visits' ).html( decode.body.totals.visitors );
				jQuery( '.analytics-views' ).html( decode.body.totals.views );	
			}
		});
	}
	

	if ( dashboard.length ) {
		elevateAdminAjax( 'get_dashboard_pagespeed_data', {}, function( response ) {
			var decode = jQuery.parseJSON( response );
			var ctx = document.getElementById("speed-chart").getContext('2d');
			var myChart = new Chart(ctx, {
			    type: 'line',
			    data: {
			        labels: decode.body.labels,
			        datasets: [
				        {
				            label: 'Mobile Speed',
				            data: decode.body.mobile_data,
				            borderWidth: 2,
				            backgroundColor: 'transparent',
				            borderColor: '#faa',
				            showLine: true
				        },
				       	{
				            label: 'Desktop Speed',
				            data: decode.body.desktop_data,
				            borderWidth: 2,
				            backgroundColor: 'transparent',
				            borderColor: '#aaf',
				            showLine: true
				        }
			        ]
			    },
			    options: {
			    	legend: {
			    		labels: {
			    			fontColor: 'white'
			    		}
			    	},
			        scales: {
			            yAxes: [{
			                ticks: {
			                    beginAtZero: true
			                },
			                 gridLines: {
		   						color: 'rgba( 255, 255, 255, 0.1 )' // makes grid lines from y axis red
		  					}
			            }]
			        }
			    }
			});	
			jQuery( '#speed-chart' ).parent().show();
		});

		elevateAdminAjax( 'get_dashboard_search_data', {}, function( response ) {
			var decode = jQuery.parseJSON( response );
			var ctx2 = document.getElementById("search-chart").getContext('2d');
			var myChart = new Chart(ctx2, {
			    type: 'line',
			    data: {
			        labels: decode.body.labels,
			        datasets: [
				        {
				            label: 'Impressions',
				            data: decode.body.impressions,
				            borderWidth: 3,
				            backgroundColor: 'transparent',
				            borderColor: '#faa',
				            showLine: true
				        },
				       	{
				            label: 'Clicks',
				            data: decode.body.clicks,
				            borderWidth: 3,
				            backgroundColor: 'transparent',
				            borderColor: '#aaf',
				            showLine: true
				        }
			        ]
			    },
			    options: {
			    	legend: {
			    		labels: {
			    			fontColor: 'white'
			    		}
			    	},
			        scales: {
			            yAxes: [{
			                ticks: {
			                    beginAtZero: true
			                },
			                 gridLines: {
		   						color: 'rgba( 255, 255, 255, 0.1 )' // makes grid lines from y axis red
		  					}
			            }]
			        }
			    }
			});	
			jQuery( '#search-chart' ).parent().show();
		});

		elevateAdminAjax( 'get_dashboard_404_data', {}, function( response ) {
			var decode = jQuery.parseJSON( response );
			var ctx3 = document.getElementById("crawl-chart").getContext('2d');
			var myChart = new Chart(ctx3, {
			    type: 'line',
			    data: {
			        labels: decode.body.labels,
			        datasets: [
				        {
				            label: '404 Errors',
				            data: decode.body.errors_not_found,
				            borderWidth: 1,
				            backgroundColor: 'transparent',
				            borderColor: '#faa',
				            showLine: true
				        }
			        ]
			    },
			    options: {
			    	legend: {
			    		labels: {
			    			fontColor: 'white'
			    		}
			    	},
			        scales: {
			            yAxes: [{
			                ticks: {
			                    beginAtZero: true
			                },
			                 gridLines: {
		   						color: 'rgba( 255, 255, 255, 0.1 )' // makes grid lines from y axis red
		  					}
			            }]
			        }
			    }
			});	
			jQuery( '#crawl-chart' ).parent().show();		
		});

		var ctx4 = document.getElementById( "visits-chart" ).getContext( '2d' );
		var myChart = new Chart(ctx4, {
		    type: 'bar',
		    data: {
		        labels: [ "Red", "Blue", "Yellow", "Green", "Purple", "Orange", "1", "2", "3", "4" ],
		        datasets: [
			        {
			            label: 'Page Views',
			            data: [ 12, 19, 3, 5, 2, 3, 5, 10, 4, 4 ],
			            borderWidth: 1,
			            backgroundColor: '#faa',
			            showLine: true
			        },
			       	{
			            label: '404 Not Found',
			            data: [ 15, 19, 5, 5, 2, 3, 8, 2, 5, 4 ],
			            borderWidth: 1,
			            backgroundColor: '#aaf',
			            showLine: true
			        }
		        ]
		    },
		    options: {
		    	legend: {
		    		labels: {
		    			fontColor: 'white'
		    		}
		    	},
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero: true
		                },
		                 gridLines: {
	   						color: 'rgba( 255, 255, 255, 0.1 )' // makes grid lines from y axis red
	  					}
		            }]
		        }
		    }
		});	
		jQuery( '#crawl-chart' ).parent().show();			
	}
}

jQuery( document ).ready( function() {
	var items = jQuery( '#post-title-0, #title' );
	if ( !items.length ) {
		elevateInitialize();
	}
});
