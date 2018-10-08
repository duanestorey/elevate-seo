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
		tempImage = jQuery.parseJSON( ElevateData.first_post_image );
		if ( tempImage != 0 ) {
			socialImage = tempImage;

		}	
	}

	// Set the image to the default image to start
	if ( ( ElevateData.default_image_url && !socialImage ) || thumbnailBehaviour == 'force_global' ) {
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
	// Reinit Opentip
	Opentip.defaultStyle = "dark";
	Opentip.findElements();

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

