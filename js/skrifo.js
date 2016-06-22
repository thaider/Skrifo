$(document).ready( function() {
	if( typeof wgStudienrichtungen !== 'undefined' ) {

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
		window.location.href = mw.config.get( 'wgScript' ) + '?title=Spezial:Daten_durchsuchen/Lernunterlage&_search_Studienrichtung=' + encodeURIComponent( studienrichtung + ' (' + uni + ')' );
	});
	
	}


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

	function setFortschritt( prozent ) {
		$( '.sk-erstellen-fortschritt-prozent' ).css( 'width', prozent + '%' );
	}
		


	// NEUE LEHRVERANSTALTUNG
	$( '.sk-erstellen-vortragende-weiter' ).click( function( e ) {
		e.stopPropagation();
		$( '.sk-erstellen-bestehende' ).slideUp( { progress: function() { $( window ).resize(); } } );
		$( '.sk-erstellen-titel' ).show();
		$( this ).hide();
		setFortschritt( 25 );
		$( '.sk-erstellen-titel-input' ).focus();
		$( window ).resize();
	});
	$( '.sk-erstellen-titel-weiter' ).click( function( e ) {
		e.stopPropagation();
		$( '.sk-erstellen-studienrichtung' ).show();
		$( '.sk-erstellen-abschliessen' ).show();
		$( this ).hide();
		setFortschritt( 50 );
		$( '.sk-erstellen-studienrichtung .select2-input' ).focus();
		$( window ).resize();
	});
	$( '.sk-erstellen-abschliessen-weiter' ).click( function( e ) {
		$( '.sk-erstellen-abschliessen-btn' ).click();
	});
	$( '.sk-erstellen' ).parent( 'form' ).submit( function( e ) {
		// Formular nicht abschicken, wenn Eingabetaste im Titel-Feld gedrückt wird...
		if( $( '.sk-erstellen-studienrichtung:visible' ).length == 0 ) {
			$( '.sk-erstellen-titel-weiter' ).click();
			e.preventDefault();
		}
		$( 'input.sk-erstellen-vortragende-input' ).val( $( 'input.sk-erstellen-vortragende-input' ).val().replace( /, $/g, '' ) );
	});
	$( '.sk-erstellen-vortragende-input' ).on( "change", function( e ) {
		if( typeof( e.added ) != 'undefined' ) {
			if( $( '.sk-erstellen-titel' ).css( 'display' ) == 'none' ) {
				$( '.sk-erstellen-vortragende-weiter' ).show();
				$( '.sk-erstellen-bestehende-spinner' ).show();
			}
			person = e.added.text;
			var path = mw.config.get( 'wgServer' ) + mw.config.get( 'wgScriptPath' );
			url = path + "/api.php?action=ask&query=[[Leiter::" + person + "]][[Kategorie:LV]]&format=json";
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
		docposition,
		wrapper;

	docposition = $( document ).scrollTop(); 
	if ( docposition > 30 ) { 
		$( 'body' ).addClass( 'scrolled' );
	} else { 
		$( 'body' ).removeClass( 'scrolled' );
	} 

	// ToTop-Link
	if( docposition > 100 ) {
		if( $( '.sk-totop' ).is(':hidden' ) ) {
			$( '.sk-totop' ).show();
			var screenheight = $( window ).height();
			var wrapperposition = $( '.sidebar-right-wrapper' ).position().top;
			var totopposition = $( '.sk-totop' ).position().top;
			var bottom = 100;
			var newmargin = screenheight - ( wrapperposition + totopposition + bottom );
			newmargin = Math.max( newmargin, 15 );
			$( '.sk-totop' ).css( 'margin-top', newmargin + 'px' );
		}
	} else {
		$( '.sk-totop' ).hide();
	}

	// Inhaltsverzeichnis auf Lernunterlagenseiten
	tocposition = $( '#toctitle' ).position();
	if( typeof tocposition !== 'undefined' ) {
		wrapper = $( '#toctitle' ).parents( '.sidebar-wrapper' );
		if ( docposition > tocposition.top + 80 ) {
			wrapper.css( 'position', 'fixed' ).css( 'top', ( - tocposition.top ) + 'px' );
		}
		else {
			wrapper.css( 'position', 'absolute' ).css( 'top', '115px' );
		} 
	}
}

$( document ).scroll( function() { 
	checkScrolled();
});
$( document ).ready( function() {
	checkScrolled();
});

// Startseite: Klick auf 'mitmachen' umleiten
$( document ).ready( function() {
	$( '.sk-startseite-adminwerden, .sk-startseite-sub .close' ).click( function( e ) {
		$( '.sk-startseite-welcome' ).toggle();
		$( '.sk-startseite-adminwerden-text' ).toggle();
	});
});

// Lernunterlagen: AutorInnen ein-/ausklappen
$( document ).ready( function() {
	$( '.sk-lu-autorinnen' ).click( function( e ) {
		$( '.sk-lu-autorinnen-full' ).toggle();
		$( '.sk-lu-autorinnen' ).toggleClass( 'open' );
	});
});

// Login-Dropdown ausklappen
$( document ).ready( function() {
	if( typeof skLogin !== 'undefined' ) {
		console.log( 'skLogin is set...' );
		setTimeout( function() { $( '#n-login' ) .click(); }, 2000 );
	}
});

// Login Dropdown: Focus auf Benutzernamen-Eingabefeld nach dem Ausklappen
$( document ).ready( function() {
	$( "#n-login" ).click( function() {
		if( ! $( this ).parent().hasClass( "open" ) ) {
			setTimeout( '$( "#wpName2" ).focus();', 500 );
		}
	});
});

// Inhaltsverzeichnis verstecken, wenn Visual Editor geladen wird
mw.hook( 've.activationComplete' ).add( function() {
	$( '#tweekiTOC' ).hide();
} );

// Button für das Hinzufügen neuer Prüfungsfragen am Beginn des Formulars
// Automatisch neue Prüfungsfragen hinzufügen, falls 'neuerTermin' im QueryString
function addPruefungstermin() {
	$( '.multipleTemplateAdder').click();
	var newposition = $( '.multipleTemplateInstance:last' ).offset().top - 40;
	$( "body, html" ).animate( { scrollTop: newposition }, '3000' );
	$( window ).resize();
}
$( document ).ready( function() {
	$( '.sk-pruefungstermin-hinzufuegen' ).click( function() {
		addPruefungstermin();
	});
	var querystring = window.location.search.substr(1).split('&');
	if( querystring.indexOf( 'neuerTermin' ) !== -1 ) {
		setTimeout( addPruefungstermin, 1000 );
	}
});

// Implementierung von autogrowonclick für Textareas
$( document ).ready( function() {
	$( document ).on( 'focus', '.autogrowonclick', function() {
		$( this ).css( 'height', 'auto' );
		$( this ).autoGrow();
		$( window ).resize();
	});
	$( document ).on( 'blur', '.autogrowonclick', function() {
		$( this ).css( 'height', '70px' );
	});
});
