jQuery( document ).ready( function () {

	jQuery( document ).on( 'submit', '#changecrab-admin-form', function ( e ) {

		e.preventDefault();

		// We inject some extra fields required for the security
		jQuery(this).append('<input type="hidden" name="action" value="store_admin_data" />');
		jQuery(this).append('<input type="hidden" name="security" value="'+ changecrab_exchanger._nonce +'" />');

		// We make our call
		jQuery.ajax( {
			url: changecrab_exchanger.ajax_url,
			type: 'post',
			data: jQuery(this).serialize(),
			success: function (response) {
				var ResponeData = JSON.parse(response);
				console.log
				if(ResponeData.success == false) {
					jQuery('#notices').html('<div class="notice notice-error is-dismissible bar errorbar">Incorrect Project ID. Try again.</div>'); 
				} else {
					jQuery('#notices').html('<div class="notice notice-success is-dismissible bar">Successfully Saved</div>'); 
				}
			}
		} );

	} );

} );