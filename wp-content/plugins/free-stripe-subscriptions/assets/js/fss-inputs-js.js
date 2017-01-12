var button;
var amount_field, first_name_field, last_name_field, zip_code_field;

var error_messages = {
	'empty': 'This is a required field.',
	'digits': 'Please use only digits for this field.',
	'wrong-length': 'Please use exactly % characters for this field.',
	'low-money': 'Please enter an amount of at least %.'
}

var required_fields = [];

jQuery( document ).ready(function() {
	// get the button
	button = jQuery( '#fss-button' );
	// get the fields
	first_name_field = jQuery( '#first_name' );
	last_name_field = jQuery( '#last_name' );
	zip_code_field = jQuery( '#zip_code' );
	amount_field = jQuery( '#amount' );

	// prepare the inputs
	mask_amount_field();

	// deal with field requirements
	require( amount_field, { min_money: '$1.00' } );
	require( last_name_field );
	require( first_name_field );
	require( zip_code_field, { type: 'digits', entry_length: '5' } ); 

});

var mask_amount_field = function() {
	amount_field.maskMoney({
		prefix: '$'
	});
	amount_field.maskMoney( 'mask', '0.00' );
}

var require = function( field, options ) {
	if ( typeof options == 'object' ) {
		field.blur( options, validate_required_field );
	} else {
		field.blur( validate_required_field );
	}

	var field_label = jQuery( 'label[for=' + field.attr( 'id' ) + ']' );
	field_label.addClass( 'fss-field_required' );
	required_fields.push( field );
}

var validate_required_field = function( event ) {
	var target = event.target;
	var parent = event.target.parentNode;

	// empty error
	if ( ! target.value ) {
		clear_errors( target );
		add_error_message( target, 'empty' );
		return;
	} else {
		clear_errors( target, 'empty' );
	}

	if ( event.data ) {
		var data = event.data;

		// type error
		if ( typeof data.type !== 'undefined' ) {
			switch ( data.type ) {
				case 'digits':
					if ( isNaN( target.value ) ) {
						add_error_message( target, 'digits' );
					} else {
						clear_errors( target, 'digits' );
					}
					break;
			}
		}

		if ( typeof data.entry_length !== 'undefined' ) {
		// length error
			if ( data.entry_length != target.value.length ) {
				add_error_message( target, 'wrong-length', data.entry_length );
			} else {
				clear_errors( target, 'wrong-length' );
			}
		}

		if ( typeof data.min_money !== 'undefined' ) {
			var value = target.value.replace( '$', '' );;

			if ( value < data.min_money.replace( '$',  '' ) ) {
				add_error_message( target, 'low-money', data.min_money );
			} else {
				clear_errors( target, 'low-money' );
			}
		}
	}
}

var validate_required_fields = function() {
	jQuery.each( required_fields, function( index, field ) { 
		field.blur();
	});
}

var add_error_message = function ( target, message_code, data ) {
	if ( target.parentNode.getElementsByClassName( 'fss-field_error-' + message_code ).length > 0 ) {
		return;
	}

	var error_message = document.createElement( 'p' );
	var message_text = error_messages[message_code];
	if ( data ) {
		message_text = message_text.replace( '%', data );
	}
	error_message.innerHTML = message_text;

	error_message.classList.add( 'fss-field_error' );
	error_message.classList.add( 'fss-field_error-' + message_code );

	target.parentNode.appendChild( error_message );
}

var clear_errors = function( target, code ) {
	var error_collection;

	if ( code ) {
		error_collection = target.parentNode.getElementsByClassName( 'fss-field_error-' + code );
	} else {
		error_collection = target.parentNode.getElementsByClassName( 'fss-field_error' );
	}

	while ( error_collection[0] ) {
		target.parentNode.removeChild( error_collection[0] );
	}
}