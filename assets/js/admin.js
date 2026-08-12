( function ( $ ) {
	'use strict';

	$( function () {
		$( '.fsi-color-picker' ).wpColorPicker();

		var $tbody = $( '#fsi-icons-table-body' );
		var nextIndex = $tbody.find( 'tr.fsi-icon-row' ).length;

		$( '#fsi-add-icon-row' ).on( 'click', function () {
			var template = document.getElementById( 'fsi-icon-row-template' ).innerHTML;
			var row = template.split( '__INDEX__' ).join( nextIndex );

			$tbody.append( row );
			nextIndex++;
		} );

		$tbody.on( 'click', '.fsi-remove-icon-row', function () {
			$( this ).closest( 'tr' ).remove();
		} );
	} );
} )( jQuery );
