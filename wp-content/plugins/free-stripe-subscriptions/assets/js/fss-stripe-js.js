jQuery( document ).ready( function() {
		if ( typeof StripeCheckout != 'undefined' ) {
			var handler = StripeCheckout.configure({
				key: ajax_object.api_key,
				locale: 'auto',
				panelLabel: 'Become a member: ',
				token: function(token) {
					var amount = jQuery( '#amount' ).val().replace( '$', '' ) * 100;
					var first_name = jQuery( '#first_name' ).val();
					var last_name = jQuery( '#last_name' ).val();
					var zip_code = jQuery( '#zip_code' ).val();
					var data = {
						'action': 'create_subscription',
						'token': token,
						'amount': amount,
						'metadata': {
										'first_name': first_name,
										'last_name': last_name,
										'zip_code': zip_code
									}
					};

					jQuery.post(ajax_object.ajaxurl, data, function(response) {
						var entry_content = jQuery( '.tm-content' );
						entry_content.empty();
					
						if ( 'success' === response ) {
							var stripe_supscription_success = '<div class="fss_subscription-success"><h2>Thank you for becoming a member of ' + ajax_object.org + '!</h2><p>If you don\'t already have access to our member forums, you\'ll get your invitation within the next couple of days. If you have any questions at all, email info@putpeoplefirstpa.org</p></div>';
							entry_content.append( stripe_supscription_success );
						} else {
							var stripe_subscription_error = '<p class="fss_subscription-fail">We\'re sorry, but there was an error on our end and we weren\'t able to create your membership';
							if ( response != 0 ) {
								stripe_subscription_error += ': "' + response + '"';
							}
						
							stripe_subscription_error += '.<br>Please don\'t try to submit the form again (to avoid the possibility of having your card charged twice) and email ' + ajax_object.support_email + ' to let us know about the problem. Please include the text of this error message in your email.</p>';

							entry_content.append( stripe_subscription_error );
						}
					});
				}
			});

			jQuery( '#fss-button' ).on('click', function(e) {
				validate_required_fields();
				if ( jQuery( '.fss-field_error' ).length == 0 ) {
					var amount = jQuery( '#amount' ).val().replace( '$', '' ) * 100;
					handler.open({
						name: ajax_object.title,
						description: ajax_object.desc,
						amount: amount
					});
				}
				e.preventDefault();
			});

			jQuery( window ).on('popstate', function() {
				handler.close();
			});
		}
});
