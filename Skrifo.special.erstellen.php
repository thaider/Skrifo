<?php

use SMW\ApplicationFactory;
class SpecialLernunterlageErstellen extends SpecialPage {
	function __construct() {
		parent::__construct( 'LernunterlageErstellen' );
	}

	function execute( $par ) {
		global $wgTitle, $wgUser;

		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();


		$pages = array();

		$pages['Lernunterlage'] = array( 'name' => $par, 
						'title' => Title::newFromText( $par )
					);
		$pages['Lernunterlage']['content'] = '{{' . $pages['Lernunterlage']['title']->getSubjectNsText() . '}}';

		$lehrveranstaltung = $pages['Lernunterlage' ]['title']->getText();
		$queryparams = array( "[[" . $lehrveranstaltung . "]]", "?Studienrichtung=", "mainlabel=-", "link=none" );
		$studienrichtung = SMWQueryProcessor::getResultFromFunctionParams( $queryparams, SMW_OUTPUT_WIKI );
		if( preg_match( '/.+ \(.+\)/', $studienrichtung ) === 1 ) {
			$uni = substr( $studienrichtung, strrpos( $studienrichtung, '(' ) + 1, -1 );
			$pages['Studienrichtung'] = array( 
				'name' => $studienrichtung,
				'content' => '{{Studienrichtung}}'
				 );
			$pages['Universität'] = array( 
				'name' => $uni, 
				'content' => '{{Universität}}'
				);
		}
		
		foreach( $pages as $name => $page ) {
			$titleObject = isset( $page['title'] ) ? $page['title'] : Title::newFromText( $page['name'] );
			if( !$titleObject->exists() ) {
				if ( !is_null( $titleObject ) && !$titleObject->isKnown() && $titleObject->canExist() ){
					$newWikiPage = new WikiPage( $titleObject );
					$pageContent = ContentHandler::makeContent( $page['content'], Title::newMainPage() );
					$text = $name . ' neu erstellt';
					$newWikiPage->doEditContent( $pageContent, $text ); 
				}
			}
		}

		$action = ( $pages['Lernunterlage']['title']->getSubjectNsText() === 'Prüfungsfragen' ) ? 'action=formedit' : 'veaction=edit';
		$output->redirect( $pages['Lernunterlage']['title']->getInternalURL( $action ) );
	}
}
