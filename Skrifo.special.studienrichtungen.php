<?php

class SpecialStudienrichtungen extends SpecialPage {
	function __construct() {
		parent::__construct( 'Studienrichtungen' );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$title = $this->getPageTitle();

		$category = Category::newFromName( 'LV' );
		$pagecount = $category->getPageCount();

		$members = $category->getMembers();

		$output->addWikiText( $pagecount . ' LVs' );
		$output->addHTML( '<a href="' . $title->getFullUrl() . '/go" class="btn btn-default btn-small">go</a>' );

		$table = '<table class="table table-condensed table-bordered">';
		$gesamtliste = array();
		$changes = 0;

		foreach( $members as $member ) {
			$title = $member->getText();
			$revision = Revision::newFromTitle( $member );
			$content = $revision->getContent( Revision::RAW );
			$text = ContentHandler::getContentText( $content );
			$vorlage = $this->getVorlagenText( $text );

			$vortragendeTitel = $this->getVortragendeTitel( $title );

			$oldVortragendeString = $this->getVortragendeVorlageString( $vorlage );

			if( $this->updated( $vorlage ) ) {
				$vorlageNeu = $vorlage;
				}
			else {
				$vortragendeVorlage = $this->getVortragendeVorlage( $oldVortragendeString );
				$vortragende = array_merge( $vortragendeTitel, $vortragendeVorlage );
				$vortragende = array_unique( $vortragende );

				$gesamtliste = array_merge( $gesamtliste, $vortragende );

				$vortragende = implode( ",", $vortragende );

				$newVortragendeString = '|Leiter=' . $vortragende;
				if( !$oldVortragendeString ) {
					$oldVortragendeString = '{{LV';
					$newVortragendeString = '{{LV' . $newVortragendeString;
					}
				$vorlageNeu = str_replace( $oldVortragendeString, $newVortragendeString, $vorlage );
				$vorlageNeu = preg_replace( "/\|LV-Leiter=.*([\|}])/Us", "$1", $vorlageNeu );
				$vorlageNeu = preg_replace( "/\|update=false/Us", "", $vorlageNeu );

				}
			$store = SFUtils::getSMWStore();
			$propertyvalues = SFUtils::getSMWPropertyValues( $store, $member, "Studienrichtung" );
			foreach( $propertyvalues as &$studienrichtung ) {
				$studienrichtung = str_replace( 'Category:', '', $studienrichtung );
				}
			$studienrichtungen = implode( ',', $propertyvalues );

			if( strpos( $vorlage, 'Studienrichtung' ) === false && count( $propertyvalues ) > 0 ) {
				$vorlageNeu = preg_replace( "/}}/Us", "|Studienrichtung=" . $studienrichtungen . "}}", $vorlage );

				if( $par == 'go' ) {
					$page = WikiPage::factory( $member );
					$textNeu = str_replace( $vorlage, $vorlageNeu, $text );
					$contentNeu = ContentHandler::makeContent( $textNeu, $member );
					$page->doEditContent( $contentNeu, 'Bot-Update: Vortragende', EDIT_MINOR + EDIT_FORCE_BOT );
					$changes++;
					$vorlageNeu = $vorlageNeu . ' - changed!';
					}
				}
			if( $changes > 50 ) {
				break;
				}
			$table .= '<tr><th colspan="2">' . $title . '</th></tr>';
			$table .= '<tr><td>' . $studienrichtungen . '</td><td>' . $vorlageNeu . '</td></tr>';
			}

		if( false ) {
			$table = '<table>';
			$gesamtliste = array_unique( $gesamtliste );
			sort( $gesamtliste );
			foreach( $gesamtliste as $name ) {
				$table .= '<tr><td>' . $name . '</td></tr>';
				}
			}

		$table .= '</table>';
		$output->addWikiText( $changes . ' Seiten geändert');
		$output->addHTML( $table );
		}

	/**
	 * Check auf Update
	 * enthält die Vorlage den Marker 'update=false'?
	 *
	 * @param $vorlage String Vorlagentext
	 *
	 * @return Boolean
	 */
	function updated( $vorlage ) {
		if( strpos( $vorlage, 'update=false' ) !== false ) {
			return false;
			}
		else {
			return true;
			}
		}

	/**
	 * Extrahieren der Vortragenden aus dem Titel
	 *
	 * @param $title String
	 *
	 * @return Array der Vortragenden
	 */
	function getVortragendeTitel( $title ) {
		$vortragende = strrchr( $title, "(" );
		if( $vortragende ) {
			$vortragende = substr( $vortragende, 1, -1 );
			return $this->bereinigeVortragende( $vortragende );
			}
		else {
			return array();
			}
		}
	
	function getVorlagenText( $page ) {
		preg_match( '/{{LV.*}}/Us', $page, $vorlage );
		if( !isset( $vorlage[0] ) ) {
			return false;
			}
		else {
			return $vorlage[0];
			}
		}

	function getVortragendeVorlageString( $vorlage ) {
		preg_match( '/\|Leiter=.*[\|}]/Us', $vorlage, $vortragende );
		if( !isset( $vortragende[0] ) ) {
			return false;
			}
		else {
			return substr( $vortragende[0], 0, -1 );
			}
		}
		
	/**
	 * Extrahieren der Vortragenden aus der Vorlage
	 *
	 * @param $page String Inhalt des Vortragendenstrings
	 *
	 * @return Array der Vortragenden
	 */
	function getVortragendeVorlage( $vortragende ) {
		$vortragende = str_replace( '|Leiter=', '', $vortragende );
		$vortragende = $this->bereinigeVortragende( $vortragende );
		return $vortragende;
		}
	
	/**
	 * Extrahieren des zu ersetzenden Strings in der Vorlage
	 * 
	 * @param $title String
	 *
	 * @return String zu ersetzende Zeichenkette
	 */
	function getReplacementString( $title ) {
		$page = $this->getpage( $title );
		preg_match( '/{{LV.*}}/Ui', $page, $vorlage );
		preg_match( '/(Vortragende=.*)[|}]/Ui', $vorlage[0], $vortragende );
		return $vortragende[1];
		}

	/**
	 * Vortragenden-String bereinigen
	 *
	 * @param $vortragende String
	 *
	 * @return Array
	 */
	function bereinigeVortragende( $vortragende ) {
		$korrektur = array(
				'Vortragende Mehrere' => '',
				'Dausien Univ.-Prof. Dr. Bettina' => 'Bettina Dausien',
				'Diem-Wille Gertraud' => 'Gertraud Diem-Wille',
				'Grandner Margarete Maria' => 'Margarete Maria Grandner',
				'Grimm Univ.-Prof. Dr. Jürgen' => 'Jürgen Grimm',
				'Gruber Karl Heinz' => 'Karl Heinz Gruber',
				'Gruber Natascha' => 'Natascha Gruber',
				'Haider Hilde' => 'Hilde Haider',
				'Hoffmann Mag. Christine' => 'Christine Hoffmann',
				'Klien Peter' => 'Peter Klien',
				'K. Kruschkova' => 'Krassimira Kruschkova',
				'Maria Breinbauer Ines' => 'Ines Maria Breinbauer',
				'Olbrich-Baumann Mag. Dr. Andreas' => 'Andreas Olbrich-Baumann',
				'Richter Rudolf' => 'Rudolf Richter',
				'Ring-Vorlesung' => '',
				'Sattlberger Eva' => 'Eva Sattlberger',
				'Sinnkriterium? Was versteht man unter dem empirischen' => '',
				'Stoller Silvia' => 'Silvia Stoller',
				'Tieber Claus' => 'Claus Tieber',
				'Tigges Stefan' => 'Stefan Tigges',
				'Vortragende diverse' => '',
				'Vortragenden Alle' => '',
				'Walter Zeidler Kurt' => 'Kurt Walter Zeidler',
				'X. Eder Franz' => 'Franz X. Eder',
				'Zahlmann Stefan' => 'Stefan Zahlmann',
				'salihu bashkim' => 'Bashkim Salihu',
				'test' => '',
				'Kamitz Reinhard' => 'Reinhard Kamitz',
				'Loidolt Sophie' => 'Sophie Loidolt',
				'Wladika Michael' => 'Michael Wladika'
				);
		$vortragende = explode( ',', $vortragende );
		foreach( $vortragende as $key => &$vortragender ) {

			// Leerzeichen entfernen
			$vortragender = trim( $vortragender );
			$vortragender = $this->tauscheVorNachname( $vortragender );

			// fehlerhafte Einträge korrigieren
			if( isset( $korrektur[$vortragender] ) ) {
				$vortragender = $korrektur[$vortragender];
				}

			// leere Einträge entfernen
			if( $vortragende[$key] == '' ) {
				unset( $vortragende[$key] );
				}
			}
		return $vortragende;
		}

	/**
	 * Vor- und Nachnamen vertauschen
	 *
	 * @param $name String
	 *
	 * @return String
	 */
	function tauscheVorNachname( $name ) {
		if( $lastspace = strrpos( $name, ' ' ) ) {
			$name = substr( $name, $lastspace+1 ) . ' ' . substr( $name, 0, $lastspace );
			}
		return $name;
		}



}
