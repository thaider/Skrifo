<?php
/**
 * Hooks for Skrifo extension
 * 
 * @file
 * @ingroup Extensions
 */

class SkrifoHooks {
		
	/** 
	 * Überprüfen, ob aktuelle Seite eine Lernunterlage ist 
	 *
	 * @param $skin BaseTemplate
	 */
	static function IsLernunterlage( $skin ) {
		global $wgSkrifoLernunterlagenNS;

		$currentNS = $skin->getSkin()->getTitle()->getNsText();
		return in_array( $currentNS, $wgSkrifoLernunterlagenNS );
	}

	
	/** 
	 * Überprüfen, ob aktuelle Seite eine Datei-Lernunterlage ist
	 *
	 * @param $skin BaseTemplate
	 */
	static function IsLernunterlageFile( $skin ) {
		$title = $skin->getSkin()->getTitle()->getPrefixedText();
		$ns = $skin->getSkin()->getTitle()->getNamespace();	
		$lv = $skin->getSkin()->getOutput()->parseInline( '{{#ask:[[' . $title . ']]|mainlabel=-|?LV=|link=none}}' );
		if( $ns == 6 && $lv != '' ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * BodyClass für Lernunterlagen hinzufügen
	 *
	 * @param $skin BaseTemplate
	 * @param $additionalBodyClasses Array
	 */
	static function AdditionalBodyClasses( $skin, &$additionalBodyClasses ) {
		if( SkrifoHooks::IsLernunterlage( $skin ) ) { 
			$additionalBodyClasses[] = 'sk-lernunterlage';
		}
		return true;
	} 


	/**
	 * Link zur Administrator*innen-Seite in Toolbox-Dropdown einfügen
	 *
	 * @param $personal_urls Array
	 * @param $title Title
	 * @param $skin SkinTemplate
	 */
	static function AdminLink( &$personal_urls, Title $title, SkinTemplate $skin ) {
		if( !$skin->getSkin()->getUser()->isAllowed('administrate') )
			return true;
		$admin_title = Title::newFromText( "Project:Administration" );
		$personal_urls['administration'] = array(
			'href' => $admin_title->getFullURL(),
			'text' => 'Administration',
			'id' => 't-administration'
			);
		return true;
		}


	/**
	 * Resource modules für Skrifo hinzufügen
	 *
	 * @param $out OutputPage
	 * @param $skin Skin
	 */
	static function LoadScripts( OutputPage &$out, Skin &$skin ) {
		$out->addModules( array( 'ext.skrifo.scripts' ) );
	}


	/**
	 * Resource odules für Shibboleth hinzufügen
	 *
	 * @param $out OutputPage
	 * @param $skin Skin
	 */
	static function ShibbolethResources( OutputPage &$out, Skin &$skin ) {
		$out->addModules( array( 'ext.Shibboleth.scripts', 'ext.Shibboleth.styles' ) );
	}


	/**
	 * VisualEditor in der Navigation verstecken (außer für Skripten und Fragenausarbeitungen)
	 *
	 * @param $sktemplate
	 * @param $links Array
	 */
	static function HideVisualEditorInNavigation( &$sktemplate, &$links ) {
		$namespace = $sktemplate->getTitle()->getNsText();
		if( 	
			$namespace != 'Skriptum' &&
			$namespace != 'Fragenausarbeitung' 
		) {
			unset( $links['views']['ve-edit'] );
			}
		return true;
		}


	/**
	 * Bearbeiten-button für Non-Power-Users oder Nicht-Lernunterlagen-Seiten verstecken
	 *
	 * @param $item String item to be checked for it's visibility
	 * @param $qtemplate QuickTemplate template for the current skin
	 */
	static function HideEditButton( $item, $qtemplate ) {
		$namespace = $qtemplate->getSkin()->getTitle()->getNsText();
		$poweruser = false;
		$user = $qtemplate->getSkin()->getUser();
		if( $user !== null ) {
			$poweruser = $user->getOption( 'tweeki-poweruser' );
		}
		if(
			$namespace != 'Skriptum' &&
			$namespace != 'Fragenausarbeitung' &&
			$namespace != 'Prüfungsfragen' &&
			$namespace != 'Datei' &&
			!$poweruser &&
			$item == 'sidebar'
		) {
			return true;
			}
		if(
			$namespace == 'Datei' &&
			!$poweruser &&
			$item == 'EDIT-EXT'
		) {
			return true;
		}
		return false;
		}


	/**
	 * Page Renderer
	 *
	 * @param $skin
	 */
	static function PageRenderer( $skin ) {
		$namespace = $skin->getSkin()->getTitle()->getNamespace();
		$sidebar = true;
		$contentclass = "col-md-offset-5 col-md-5 sk-reintext";
		$sidebarclass = "col-md-offset-3 col-md-2";
		if( $namespace == -1 ) { /* Spezial */
			$contentclass = "col-md-offset-3 col-md-7 sk-reintext";
			$sidebar = false;
			$headicon = 'zahnrad';
		}
		if( $namespace == 0 ) { /* LVs */
			$contentclass = "col-md-offset-3 col-md-7";
			$sidebar = false;
		}
		if( $namespace == 10 ) { /* Vorlagen */
			$contentclass = "col-md-offset-3 col-md-7";
			$sidebar = false;
		}	
		if( $namespace == 2 ) { /* Benutzer */
			$headicon = 'user';
			$sidebar = false;
			$contentclass = "col-md-offset-3 col-md-7 sk-reintext sk-hilfe";
		}
		if( $namespace == 4 ) { /* Projekt */
			$headicon = 'zahnrad';
		}
		if( $namespace == 12 || $namespace == 13 ) { /* Hilfe */
			$headicon = 'hilfe-inv';
			$sidebar = false;
			$contentclass = "col-md-offset-3 col-md-7 sk-reintext sk-hilfe";
		}
		if( $namespace >= 100 || $namespace == 6 || $skin->getSkin()->getTitle()->equals( Title::newMainPage() ) ) { /* Lernunterlagen, Dateien, Startseite */
			$contentclass = "col-md-offset-4 col-md-6";
			$sidebarclass = "col-md-offset-2 col-md-2";
		}
		$skin->data['prebodyhtml'] = '<div class="clearfix"></div>';
		if( isset( $headicon ) && $skin->checkVisibility( 'firstHeading' ) ) {
			$skin->data['prebodyhtml'] = '<div class="sk-head-icon"><span class="icon-' . $headicon . '"></span></div>' . $skin->data['prebodyhtml'];
		}
		$navbaroptions = array(
			"wrapper" => "li",
			"wrapperclass" => "nav nav-block",
			"btnclass" => "btn btn-block"
			);
		$plusoptions = $navbaroptions;
		$plusoptions['wrapperclass'] = "nav pull-right hinzufugen";
		$plusoptions['data-toggle'] = "tooltip";
		$plusoptions['title'] = "Neue Lernunterlage erstellen";
		$plusoptions['data-placement'] = "bottom";
		$personaloptions = $navbaroptions;
		$personaloptions['dropdownclass'] = "dropdown-menu dropdown-menu-right";
		$rightoptions = $navbaroptions;
		$rightoptions['wrapperclass'] = "nav hilfe pull-right";
	?>
			<!-- navbar -->
			<div id="mw-navigation" class="<?php $skin->msg( 'tweeki-navbar-class' ); ?> skrifo-navbar" role="navigation">
				<h2><?php $skin->msg( 'navigation-heading' ) ?></h2>
				<div id="mw-head" class="navbar-inner">
					<div class="<?php $skin->msg( 'tweeki-container-class' ); ?>">
					<div class="row">	
						<div class="col-md-2">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".skrifo-navbar-collapse">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>

							<?php if ( $skin->checkVisibility( 'navbar-brand' ) ) { 
								$skin->renderBrand(); 
								} ?>
					
						</div>

							<ul class="col-md-1 nav navbar-nav skrifo-navbar-collapse collapse">
								<li class="nav pull-right hinzufugen">
									<?php echo $skin->buildItems( 'Special:FormEdit/Neue_LV|<span class="icon-hinzufugen-inv"></span>', $plusoptions, 'costum' ); ?>
								</li>
							</ul>
							<ul class="col-md-3 nav navbar-nav skrifo-navbar-collapse collapse skrifo-navbar-form">
								<?php echo $skin->buildItems( 'SEARCH', $navbaroptions, 'custom' ); ?>
							</ul>
							<ul class="col-md-2 nav navbar-nav skrifo-navbar-collapse collapse">
								<?php echo $skin->buildItems( 'STUDIENRICHTUNGEN', $navbaroptions, 'custom' ); ?>
							</ul>
							<ul class="col-md-2 nav navbar-nav skrifo-navbar-collapse collapse">
								<?php echo $skin->buildItems( 'PERSONAL-EXT', $personaloptions, 'custom' ); ?>
							</ul>
							<ul class="col-md-1 nav navbar-nav skrifo-navbar-collapse collapse">
								<?php echo $skin->buildItems( 'Hilfe:Inhaltsverzeichnis|<div class="sk-hilfe-icon"><span class="icon-hilfe-inv"></span></div><div class="sk-hilfe-text">Hilfe</div>', $rightoptions, 'custom' ); ?>
							</ul>
					</div>
					</div>
				</div>
			</div>
			<!-- /navbar -->
			<div id="mw-page-base"></div>
			<div id="mw-head-base"></div>
			<a id="top"></a>
	    <!-- content -->
	    <div class="<?php $skin->msg( 'tweeki-container-class' ); ?> with-navbar-fixed <?php echo $skin->data['userstateclass']; echo ( $skin->checkVisibility( 'navbar' ) ) ? ' with-navbar' : ' without-navbar'; ?>">
	
				<div class="row">
					<div class="<?php echo $contentclass; ?>" role="main">
						<?php $skin->renderContent(); ?>
					</div>
				</div>
	    </div>
	    <!-- /content -->
	
			<?php 
			$subnavclass = '';
			?>
			<div class="<?php $skin->msg( 'tweeki-container-class' ); ?>">
				<?php $skin->renderSubnav( $subnavclass ); ?>
			</div>
			<?php if( $sidebar ) { ?>
			<div class="sk-sidebar-wrapper">
				<div class="sk-sidebar-container <?php $skin->msg( 'tweeki-container-class' ); ?>">
					<div class="row">
						<?php $skin->renderSidebar( $sidebarclass ); ?>
					</div>
				</div>
			</div>
			<?php }
			$skin->renderFooter();
			$skin->printTrail(); 
		}

}
