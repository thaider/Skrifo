$(document).ready( function() {

	// STUDIENRICHTUNGSAUSWAHL
	// Universitätsauswahl erstellen und befüllen
	var uniselect = '<option></option>';
	$.each( wgStudienrichtungen, function( uni, studienrichtungen ) {
		uniselect += '<option>' + uni + '</option>';
	});
	uniselect = '<select class="form-control" id="uni-select">' + uniselect + '</select>';

	// Studienrichtungsauswahl erstellen
	studienrichtungselect = '<select class="form-control" id="studienrichtung-select"><option></option></select>';
	
	// ins DOM einfügen und Click-/Change-Events binden
	$( '#studienrichtung-wrapper .dropdown-menu' ).append( '<li class="studienrichtung-selects">' + uniselect + studienrichtungselect + '</li>' );
	$( '.studienrichtung-toggle a' ).removeAttr( 'href' );
	$( '#studienrichtung-wrapper li' ).click( function( e ) {
		e.stopPropagation();
	});
	$( '.studienrichtung-toggle' ).click( function( e ) {
		e.stopPropagation();
		$( this ).parent().toggleClass( 'studienrichtung-expand' );
/*		if( $( this ).parent().hasClass( 'studienrichtung-expand' ) ) {
			$( this ).find( 'a' ).text( 'Auswahl anzeigen' );
		} else {
			$( this ).find( 'a' ).text( 'Alle Studienrichtungen anzeigen' );
		}
*/		$( '.studienrichtung-expand #uni-select' ).select2( { placeholder: 'Universität wählen:', minimumResultsForSearch:-1 } ).select2( 'open' );
	});
	$( '#uni-select' ).change( function() {
		var options = '<option></option>';
		var uni = $( this ).val();
		$.each( wgStudienrichtungen[uni], function( key, studienrichtung ) {
			options += '<option>' + studienrichtung + '</option>';
		});
		$( '#studienrichtung-select' ).html( options ).show().select2( { placeholder: 'Studienrichtung wählen:', minimumResultsForSearch: 10 } ).select2( 'open' );
	});
	$( '#studienrichtung-select' ).change( function() {
		var studienrichtung = $( this ).val();
		var uni = $( '#uni-select' ).val();
		window.location.href = wgScript + '?title=Spezial:Daten_durchsuchen/Lernunterlage&_search_Studienrichtung=' + encodeURIComponent( studienrichtung + ' (' + uni + ')' );
	});


	// Fix für SHIBBOLETH
	// TODO: obsolet?
	if($( '#wayf_div' ).length == 1 ) $( '#content .breit' ).addClass( 'anmeldung' );


	// DATEI-Infos in die Infobox verschieben
	if( $( '.fullMedia' ).length == 1 ) {
		fileSize = $( '.fullMedia span.fileInfo' ).text();
		fileSize = fileSize.substring( fileSize.indexOf( 'Dateigröße:' ) + 11 );
		fileSize = fileSize.substring( 0, fileSize.indexOf( ',' ) );
		$( '#fileSize' ).text( fileSize );
	}


	// NEUE LEHRVERANSTALTUNG
	$( '.sk-erstellen-vortragende-weiter' ).click( function( e ) {
		e.stopPropagation();
		$( '.sk-erstellen-bestehende' ).slideUp( { progress: function() { $( window ).resize(); } } );
		$( '.sk-erstellen-titel' ).show();
		$( this ).hide();
		$( '.sk-erstellen-titel-input' ).focus();
	});
	$( '.sk-erstellen-titel-weiter' ).click( function( e ) {
		e.stopPropagation();
		$( '.sk-erstellen-studienrichtung' ).show();
		$( '.sk-erstellen-abschliessen' ).show();
		$( this ).hide();
		$( '.sk-erstellen-studienrichtung .select2-input' ).focus();
	});
	$( '.sk-erstellen-abschliessen-weiter' ).click( function( e ) {
		$( '.sk-erstellen-abschliessen-btn' ).click();
	});
	$( '.sk-erstellen' ).parent( 'form' ).submit( function( e ) {
		$( 'input.sk-erstellen-vortragende-input' ).val( $( 'input.sk-erstellen-vortragende-input' ).val().replace( /, $/g, '' ) );
	});
	$( '.sk-erstellen-vortragende-input' ).on( "change", function( e ) {
		if( typeof( e.added ) != 'undefined' ) {
			if( $( '.sk-erstellen-titel' ).css( 'display' ) == 'none' ) {
				$( '.sk-erstellen-vortragende-weiter' ).show();
				$( '.sk-erstellen-bestehende-spinner' ).show();
			}
			person = e.added.text;
			url = "https://skriptenforum.net/t/wiki/api.php?action=ask&query=[[Leiter::" + person + "]][[Kategorie:LV]]&format=json";
			$.getJSON( url )
			.done( function( data ) {
				counter = 0;
				lvs = 'Bereits eingetragene Lehrveranstaltungen von <b>' + person + '</b>:';
				$.each( data.query.results, function( i, lv ) {
					titel = lv.fulltext.replace( /\(.*\)/g, '' );
					lvs = lvs + '<div class="sk-erstellen-bestehende-titel"><a href="' + lv.fullurl + '">' + titel + '</a></div>';
					counter++;
				});
				if( counter == 0 ) {
					lvs = 'Bisher keine Lehrveranstaltungen von <b>' + person + '</b> eingetragen.';
				}
				$( '<div data-person="' + person + '" style="display:none">' + lvs + '</div>' ).prependTo( '.sk-erstellen-bestehende' ).slideDown( { progress: function() { $( window ).resize(); } } );
				$( '.sk-erstellen-bestehende-spinner' ).hide();
			});
		}
		if( typeof( e.removed ) != 'undefined' ) {
			$( '.sk-erstellen-bestehende > div' ).each( function() {
				person = e.removed.text;
				if( $(this).data( "person" ) == person ) {
					$(this).remove();
				}
			});
		}
	});

});


// SCROLLING - Klasse hinzufügen und Handling für Inhaltsverzeichnis/ToTop
function checkScrolled() {
	var tocposition,
		docposition;

	docposition = $( document ).scrollTop(); 
	if ( docposition > 30 ) { 
		$( 'body' ).addClass( 'scrolled' );
	} else { 
		$( 'body' ).removeClass( 'scrolled' );
	} 

	// ToTop-Link
	if( docposition > 100 ) {
		$( '.sk-totop' ).show();
	} else {
		$( '.sk-totop' ).hide();
	}

	// Inhaltsverzeichnis auf Lernunterlagenseiten
	tocposition = $( '#toctitle' ).position();
	if( typeof tocposition !== 'undefined' ) {
		if ( docposition > tocposition.top + 80 ) {
			$( '.sk-sidebar-wrapper' ).css( 'position', 'fixed' ).css( 'top', ( - tocposition.top ) + 'px' );
		}
		else {
			$( '.sk-sidebar-wrapper' ).css( 'position', 'absolute' ).css( 'top', '83px' );
		} 
	}
}

$( document ).scroll( function() { 
	checkScrolled();
});
$( document ).ready( function() {
	checkScrolled();
});

