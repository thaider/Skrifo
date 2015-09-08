<?php

class SpecialLernunterlageErstellen extends SpecialPage {
	function __construct() {
		parent::__construct( 'LernunterlageErstellen' );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		$wikitext = 'Hello world!';
		$output->addWikiText( $wikitext );
		$output->addWikiText( $par );
		$title = $par;
		$titleObject = Title::newFromText( $title );
		if( !$titleObject->exists() ) {
			if ( !is_null( $titleObject ) && !$titleObject->isKnown() && $titleObject->canExist() ){
				$newWikiPage = new WikiPage( $titleObject );
				$pageContent = ContentHandler::makeContent( '{{' . $titleObject->getSubjectNsText() . '}}', Title::newMainPage() );
				$newWikiPage->doEditContent( $pageContent, "Lernunterlage neu erstellt" ); 
			}
		}


		$output->redirect( $titleObject->getInternalURL( 'veaction=edit' ) );
	}
}
