jQuery( function ( $ ) {

	deploy_actions();
	deploy_add();
	deploy_delete();

    $( document.body ).on( 'updated_tracks', function(e){
        delete_actions();
    	deploy_actions();
    	deploy_delete();
    } );

    $( document.body ).on( 'update_tracks', function( e ){

    	let ajax_field_value = {};

        ajax_field_value['action'] = 'wap_get_tracks';
        ajax_field_value['post_id'] = $( '#post_ID' ).val();

    	$.ajax({
        	type: "POST",
            url: ajaxurl,
            data: ajax_field_value,
            beforeSend: function () {
                $( '#album_variable_product_options' ).addClass( 'requesting' );
            },
            success: function(msg) {
                let tracks_frame = $( '#tracks' ),
                    isset_tracks_id = [],
                    new_tracks_id = [];

                $.each( tracks_frame.children(), function( e, v ){
                    isset_tracks_id.push( $(this).data('variation_id') );
                } );

                $.each( $( msg.data.html ), function( e, v ){

                    new_tracks_id.push( $(this).data('variation_id') );

                    if( isset_tracks_id.indexOf( $(this).data('variation_id') ) == -1 ){
                        tracks_frame.append( $(this) );
                    }

                } );

                $.each( isset_tracks_id, function( e, v ){
                    if( new_tracks_id.indexOf( v ) == -1 ){
                        $( '#variation_id_' + v, tracks_frame ).remove();
                    }

                });

				$( '#album_variable_product_options' ).removeClass( 'requesting' );
            	$( document.body ).trigger( 'updated_tracks', msg );
            }
        });

    });
 
});

function deploy_add(){

	jQuery( '.add_track_element' ).on( 'click', function( e ){

        e.preventDefault();
        let ajax_field_value = {};

        ajax_field_value['action'] = 'wap_add_track';
        ajax_field_value['post_id'] = jQuery( '#post_ID' ).val();

        jQuery.ajax({
        	type: "POST",
            url: ajaxurl,
            data: ajax_field_value,
            success: function( msg ) {
            	jQuery( document.body ).trigger( 'update_tracks', msg );
            }
        });

    });

}

function delete_actions(){

    jQuery( '.variation_wrapper h3 .tack_title, .variation_wrapper h3 .open_track_wrapper' ).off('click');

    jQuery('.track_variation_title').off('change, keyup');

    jQuery( '.remove_this_track' ).off('click');
    console.log('actions_deleted');

}

function deploy_actions(){

	jQuery( '.variation_wrapper h3 .tack_title, .variation_wrapper h3 .open_track_wrapper' ).on( 'click', function( e ){
        jQuery( this ).parents('.variation_wrapper').toggleClass('closed').find('.variation_inner_wrapper').slideToggle();
    });

    jQuery('.track_variation_title').on( 'change, keyup', function( e ){
    	if( jQuery( this ).val() != '' ){
    		jQuery( this ).parents( '.variation_wrapper' ).find( '.tack_title' ).text( jQuery( this ).val() );
    	}else{
    		jQuery( this ).parents( '.variation_wrapper' ).find( '.tack_title' ).text( 'Empty' );
    	}
    });

}

function deploy_delete(){

	jQuery( '.remove_this_track' ).on( 'click', function( e ){

    	e.preventDefault();
    	let ajax_field_value = {},
            ths = jQuery( this );

        ajax_field_value['action'] = 'wap_remove_track';
        ajax_field_value['post_id'] = ths.attr( 'rel' );

        $.ajax({
        	type: "POST",
            url: ajaxurl,
            data: ajax_field_value,
            success: function( msg  ) {
                // ths.parents('.variation_wrapper').remove();
            	jQuery( document.body ).trigger( 'update_tracks');
            }
        });
    });

}

function add_track_file( button ){

	frame = wp.media({
		multiple : false,
		library : { type : 'audio'},
	});

	frame.on( 'open', function() {
		let selection = frame.state().get( 'selection' );

		attachment = wp.media.attachment( button.data( 'track_id' ) );
		selection.add( attachment ? attachment : '' );
	});

	frame.on( 'select', function() {
		let selected = frame.state().get( 'selection' ).first();

		button.parents( '.variation_inner_wrapper' ).find( '.wap_track_id' ).val( selected.id );
        button.parents( '.variation_inner_wrapper' ).find( '.track_variation_title' ).val( selected.attributes.title );
        button.parents( '.variation_inner_wrapper' ).find( '.track_variation_title' ).keyup();
        button.parents( '.variation_inner_wrapper' ).find( '.track_variation_author' ).val( selected.attributes.meta.artist );
        button.parents( '.variation_inner_wrapper' ).parent().find('.no_media_added').remove();

        jQuery( document.body ).trigger( 'image_added', button );

  	});

  	frame.open();

}

function wap_regenerate_cut( button ){

	let ajax_field_value = {};

    ajax_field_value['action'] = 'wap_process_regenerate_cut';
    ajax_field_value['post_id'] = button.data( 'post_id' );

	$.ajax({
    	type: "POST",
        url: ajaxurl,
        data: ajax_field_value,
        beforeSend: function () {
            button.addClass( 'requesting' );
        },
        success: function(msg) {

        	if( msg.success != false ){
        		button.text( msg.data.text );
                button.parents( 'tbody' ).children( 'cut_status' ).text( msg.data.text );
        	}

			button.removeClass( 'requesting' );

        }
    });
}