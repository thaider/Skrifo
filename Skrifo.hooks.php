<?php
/**
 * Hooks for Skrifo extension
 * 
 * @file
 * @ingroup Extensions
 */

class SkrifoHooks {

	/**
	 * Parser Functions initialisieren
	 */
	// TODO: find the bug!
	static function onParserSetup( Parser &$parser ) {
		$parser->setHook( 'sklogin', 'SkrifoHooks::login' );
		$parser->setHook( 'skchanges', 'SkrifoHooks::changes' );
		$parser->setHook( 'skwelcome', 'SkrifoHooks::welcome' );
		$parser->setHook( 'timeago', 'SkrifoHooks::timeago' );
		$parser->setFunctionHook( 'studienrichtungen', 'SkrifoHooks::studienrichtungen' );
		$parser->setFunctionHook( 'fortschritt', 'SkrifoHooks::fortschritt' );
		return true;
		}


	/**
	 * Fortschritt
	 */
	static function fortschritt( $parser, $param1 = 0 ) {
		$segments = round( $param1/20 );
		$fortschritt = '<div class="sk-fortschritt-intro"><div class="sk-fortschritt-intro-container"><div class="sk-fortschritt-intro-text">vollständig: <b>' . $segments . '|5</b></div></div></div>';
		for( $i = 1; $i <= 5; $i++ ) {
			$status = ( $i <= $segments ) ? 'fertig' : 'offen';
			$fortschritt .= '<div class="sk-fortschritt-segment ' . $status . '"></div>';
			}
		$fortschritt = '<div class="sk-fortschritt">' . $fortschritt . '</div>';
		return array( $fortschritt, 'noparse' => true, 'isHTML' => true );
		}


	/**
	 * Time-ago
	 */
	static function timeago( $input, $args, Parser $parser, PPFrame $frame ) {
		try {
			$datetime = new DateTime( $parser->recursiveTagParse( $input, $frame ) );
		}
		catch (Exception $e) {
			return "keine gültige Datumsangabe";
		}
		$now = new DateTime();
		$interval = $datetime->diff( $now );
		if( $interval->y == 1 ) {
			$ago = 'einem Jahr';
		}
		elseif( $interval->y > 0 ) {
			$ago = $interval->y . ' Jahren';
		}
		elseif( $interval->m == 1 ) {
			$ago = 'einem Monat';
		}
		elseif( $interval->m > 0 ) {
			$ago = $interval->m . ' Monaten';
		}
		elseif( $interval->d == 1 ) {
			$ago = 'einem Tag';
		}
		elseif( $interval->d > 0 ) {
			$ago = $interval->d . ' Tagen';
		}
		else {
			$ago = 'kurzem';
		}
		return 'vor ' . $ago;
		}


	/**
	 * Link zum bearbeiten der Studienrichtung
	 */
	static function studienrichtungen( $parser, $param1 = 'Studienrichtungen bearbeiten' ) {
		global $wgUser;
		$output = $parser->recursiveTagParse( SkrifoHooks::studienrichtungLink( $wgUser->getName(), $param1 ) );
		return array( $output, 'noparse' => true, 'isHTML' => true );
		}


	/**
	 * Link zum bearbeiten der Studienrichtung generieren
	 */
	static function studienrichtungLink( $username, $linktext = 'Studienrichtung bearbeiten' ) {
		return '[[Spezial:Mit Formular bearbeiten/Benutzer/Benutzer:' . $username . '|' . $linktext . ']]';
		}

		
	/**
	 * Login-Dropdown ausklappen, wenn <sklogin>-Tag verwendet wird
	 */
	static function login( $input, $args, Parser $parser ) {
		$parser->disableCache();
		return '<script>skLogin = true;</script>';
		}
		
	/**
	 * Letzte Änderungen auf der Startseite anzeigen - filtern für angemeldete
	 * NutzerInnen, die Studienrichtungen angegeben haben
	 */
	static function changes( $input, $args, Parser $parser, $frame) {
		$user = $parser->getUser();
		$studienrichtungen = $parser->recursiveTagParse( '{{#ask:[[Benutzer:' . $user->getName() . ']]|mainlabel=-|?UserInterest=|link=none}}', $frame );
		if( $studienrichtungen != '' ) {
			$parser->disableCache();
			$studienrichtungen = explode( ', ', $studienrichtungen );
			$studienrichtungen = '[[Studienrichtung::' . implode( '||', $studienrichtungen ) . ']]';
			}
		else {
			$studienrichtungen = '';
			}
		$query = '{{#ask:[[Kategorie:Lernunterlage]]' . $studienrichtungen . '|?LV|?Semester|?Unterlagentyp|?Leiter|?Zuletzt geändert#ISO|sort=Zuletzt geändert|order=descending|limit=15|format=template|template=changes|link=none|searchlabel=}}';
		$changes = $parser->recursiveTagParse( $query, $frame );
		$wanted = '<div class="sk-heading-line">
				<div class="sk-heading-line-text">Meistgelesen</div>
				<div class="sk-heading-line-line"></div>
			</div>';
		return '<div class="container-fluid"><div class="row">
				<div class="col-md-12"><div class="sk-user-changes">' . $changes . '</div></div>
				<!--<div class="col-md-4"><div class="sk-user-wanted">' . $wanted . '</div></div>-->
			</div></div>';
		}
		
	/**
	 * Begrüßung für angemeldete NutzerInnen auf der Startseite anzeigen
	 */
	static function welcome( $input, $args, Parser $parser ) {
		$parser->disableCache();
		$user = $parser->getUser();
		if( $user->isAnon() ) {
			return $parser->recursiveTagParse( '<div class="sk-startseite-counter">Stöber in der freien Sammlung von <strong>[[Spezial:Daten_durchsuchen/Lernunterlage|{{#ask:[[Kategorie:Lernunterlage]]|format=count}} Lernunterlagen & {{#ask:[[Kategorie:LV]]|format=count}} Lehr&shy;ver&shy;anstal&shy;tungen.]]</strong></div>' );
			} 
		else {
			return '<div class="sk-welcome">Herzlich Willkommen, ' . $parser->getUser()->getName() . '!</div><div class="sk-changes-intro">Hier findest du die Updates deiner Studienrichtung(en) auf einen Blick:</div>';
			}
		}
		
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
		if( SkrifoHooks::IsLernunterlage( $skin ) || SkrifoHooks::IsLernunterlageFile( $skin ) ) { 
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
		$administration_url = array(
			'href' => $admin_title->getFullURL(),
			'text' => 'Administration',
			'id' => 't-administration'
			);
		end( $personal_urls );
		if( key( $personal_urls ) == 'logout' ) {
			$logout_url = array_pop( $personal_urls );
			$personal_urls['administration'] = $administration_url;
			$personal_urls['logout'] = $logout_url;
			}
		else {
			$personal_urls['administration'] = $administration_url;
			}
		return true;
		}

	/**
	 * Text des Links zur Benutzerseite ändern
	 *
	 */
	static function ChangeLinkUserPage( array &$personal_urls, Title $title, SkinTemplate $skin ) {
		if( isset( $personal_urls['userpage'] ) ) {
			$personal_urls['userpage']['text'] = 'Meine Übersicht';
			}
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
		global $wgTweekiSkinHideAll, $wgParser;

		$user = $skin->getSkin()->getUser();
		if( $user->isAnon() ) {
			$welcome = $wgParser->recursiveTagParse( '<div class="sk-startseite-counter">Stöber in der freien Sammlung von <strong>[[Spezial:Daten_durchsuchen/Lernunterlage|{{#ask:[[Kategorie:Lernunterlage]]|format=count}} Lernunterlagen & {{#ask:[[Kategorie:LV]]|format=count}} Lehr&shy;ver&shy;anstal&shy;tungen.]]</strong></div>' );
			} 
		else {
			$welcome = '<div class="sk-welcome">Herzlich Willkommen, ' . $user->getName() . '!</div><div class="sk-changes-intro">Hier findest du die Updates deiner Studienrichtung(en) auf einen Blick:</div>';
			}

		$mainpage_header = '
<div class="sk-startseite-sky">
	<a class="sk-startseite-feedback" href="' . Title::newFromText( 'Spezial:Kontakt' )->getFullURL() . '"></a>
	<div class="sk-startseite-about">
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<div class="sk-startseite-cloud"></div>
					<a href="' . Title::newFromText( 'Project:Über_Skriptenforum.net' )->getFullURL() . '"><span class="sk-startseite-info">i</span>Über das Skriptenforum</a>
				</div>
			</div>
		</div>
	</div><!-- /sk-startseite-about -->
	<div class="container">
		<div class="row">
			<div class="col-md-4 col-md-offset-2">
				<a href="' . Title::newFromText( 'Spezial:Mit_Formular_bearbeiten/Neue_LV' )->getFullURL() . '">
					<div class="sk-startseite-mitmachen" data-skrifo-target="sk-startseite-mitmachen-text">
						<span class="sk-startseite-plus pull-right"><span class="icon-hinzufugen-inv"></span></span>Teile deine Lernunterlage!
					</div>
				</a>
			</div>
			<div class="col-md-4">
				<div class="sk-startseite-adminwerden" data-skrifo-target="sk-startseite-adminwerden-text">
					<span class="sk-startseite-users pull-right"><span class="icon-users"></span></span>Jetzt Admin werden!
				</div>
			</div>
		</div><!-- /row -->
	</div><!-- /container -->
</div><!-- /sk-startseite-sky -->
<div class="sk-startseite-sub sk-startseite-welcome">
	<div class="sk-startseite-sub-shadow"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">' . $welcome . '</div>
		</div>
	</div>
</div><!-- /sk-startseite-sub -->
<div class="sk-startseite-sub sk-startseite-adminwerden-text">
	<div class="sk-startseite-sub-shadow"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="close">&times;</div>
				<div class="sk-startseite-adminwerden-maxerl"></div>
				' . $wgParser->recursiveTagParse( '{{Project:Admins}}' ) . '
			</div>
		</div>
	</div>
</div><!-- /sk-startseite-adminwerden-text -->';

		$namespace = $skin->getSkin()->getTitle()->getNamespace();
		$sidebar = true;
		$mainpage = false;
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
		if( $namespace >= 100 || $namespace == 6 ) { /* Lernunterlagen, Dateien */
			$contentclass = "col-md-offset-4 col-md-6";
			$sidebarclass = "col-md-offset-2 col-md-2";
		}
		if( $skin->getSkin()->getTitle()->equals( Title::newMainPage() ) ) { /* Startseite */
			$mainpage = true;
			$contentclass = "col-md-offset-2 col-md-8";
			$sidebar = false;
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
		$rightoptions['wrapperclass'] = "nav hilfe";
	?>
			<!-- navbar -->
			<div id="mw-navigation" class="<?php $skin->msg( 'tweeki-navbar-class' ); ?> skrifo-navbar" role="navigation">
				<h2><?php $skin->msg( 'navigation-heading' ) ?></h2>
				<div id="mw-head" class="navbar-inner">
					<div class="<?php $skin->msg( 'tweeki-container-class' ); ?>">
					<div class="row">	
						<div class="col-md-3">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>

							<?php if ( $skin->checkVisibility( 'navbar-brand' ) ) { 
								$skin->renderBrand(); 
								} ?>
					
						</div>
						<ul class="col-md-3 nav navbar-nav navbar-collapse collapse skrifo-navbar-form">
							<li class="nav hinzufugen">
								<?php echo $skin->buildItems( 'Special:FormEdit/Neue_LV|<span class="icon-hinzufugen-inv"></span>', $plusoptions, 'costum' ); ?>
							</li>
							<?php echo $skin->buildItems( 'SEARCH', $navbaroptions, 'custom' ); ?>
						</ul>
						<ul class="col-md-2 nav navbar-nav navbar-collapse collapse">
							<?php echo $skin->buildItems( 'STUDIENRICHTUNGEN', $navbaroptions, 'custom' ); ?>
						</ul>
						<ul class="col-md-2 nav navbar-nav navbar-collapse collapse">
							<?php echo $skin->buildItems( 'PERSONAL-EXT', $personaloptions, 'custom' ); ?>
						</ul>
						<ul class="col-md-1 nav navbar-nav navbar-collapse collapse">
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
<?php	if( $namespace == 2 ) { 
		$wgTweekiSkinHideAll[] = 'subnav';
		self::UserPage( $skin ); 
		}
	else { ?>
	    <!-- content -->
	  <?php if( $mainpage ) { ?>
	  <div class="sk-startseite-head"><?php echo $mainpage_header; ?></div>
		<?php } ?>
	    <div class="<?php $skin->msg( 'tweeki-container-class' ); ?> with-navbar-fixed <?php echo $skin->data['userstateclass']; echo ( $skin->checkVisibility( 'navbar' ) ) ? ' with-navbar' : ' without-navbar'; ?>">
	
				<div class="row">
					<div class="<?php echo $contentclass; ?>" role="main">
						<?php $skin->renderContent(); ?>
					</div>
				</div>
	    </div>
	    <!-- /content -->
	<?php } ?>
	
			<div class="<?php $skin->msg( 'tweeki-container-class' ); ?>">
				<?php // $skin->renderSubnav( $subnavclass ); ?>
			</div>
			<?php if( $sidebar ) {
				$skin->renderSidebar( 'left', $sidebarclass );
				$skin->renderSidebar( 'right', 'col-md-offset-10 col-md-2' );
				}
			$skin->renderFooter();
			$skin->printTrail(); 
		}

	/**
	 * Benutzerseite ausgeben 
	 *
	 * @param $skin BaseTemplate
	 */
	static function UserPage( $skin ) {
		$output = $skin->getSkin()->getOutput();
		$visitor = $skin->getSkin()->getUser();
		$pageownername = $skin->getSkin()->getTitle()->getText();
		$pageowner = User::newFromName( $pageownername );
		$pageownerid = $pageowner->getId();
		$homevisitor = false;
		if( $visitor->equals( $pageowner ) ) { 
			$homevisitor = true; 
			}
		$registration = DateTime::createFromFormat( "YmdHis", $pageowner->getRegistration() );
		$contentclass = "col-md-offset-3 col-md-7 sk-reintext sk-hilfe";
		$studienrichtungen = $output->parseinline( '{{#ask:[[Benutzer:' . $pageowner . ']]|mainlabel=-|?UserInterest=|link=none}}' );
		if( $studienrichtungen == '' ) {
			$hasstudienrichtungen = false;
			$studienrichtungen = $output->parseinline( "-" );
			}
//		$studienrichtungen = explode( ',', $studienrichtungen );
//		foreach( $studienrichtungen as &$studienrichtung ) {
//			$studienrichtung = '<a href="">' . $studienrichtung . '</a>';
//			}
//		$studienrichtungen = implode( ',', $studienrichtungen );
		$hasadmin = false;
		$isadmin = false;
		$pageownergroups = $pageowner->getGroups();
		$pageownerrole = '';
		foreach( $pageownergroups as $group ) {
			if( $group == "Steward" ) {
				$pageownerrole = "Admin";
				}
			}
		$bearbeitet = $output->parse( '{{#ask:[[Kategorie:Lernunterlage]][[Seitenbearbeiter::Benutzer:' . $pageowner . ']]|?LV|?Semester|?Unterlagentyp|?Leiter|?Zuletzt geändert#ISO|sort=Zuletzt geändert|order=descending|limit=15|format=template|template=drilldown|link=none|searchlabel=}}' );

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'revision', 'page' ),
			array( 'rev_page' ),
			array(
				'rev_user = ' . $pageownerid,
				'rev_parent_id = 0',
				'page_namespace = 100 OR page_namespace = 102 OR page_namespace = 104'
				),
			__METHOD__,
			array( 'LIMIT' => '15', 'ORDER BY' => 'rev_timestamp DESC' ),
			array( 'page' => array( 'INNER JOIN', array( 'rev_id=page_latest' ) ) )
			);
		$created = array();
		foreach( $res as $row ) {
			$created[] = Title::NewFromId( $row->rev_page )->getFullText();
			}
		foreach( $created as &$creation ) {
			$creation =  $output->parse( '{{#ask:[[' . $creation . ']]|?LV|?Semester|?Unterlagentyp|?Leiter|?Zuletzt geändert#ISO|sort=Zuletzt geändert|order=descending|limit=15|format=template|template=drilldown|link=none|searchlabel=}}' );
			}
		$created = implode( '', $created );
//		$created = $output->parse( '{{#ask:[[' . implode( '||', $created ) . ']]|?LV|?Semester|?Unterlagentyp|?Leiter|?Zuletzt geändert#ISO|sort=Zuletzt geändert|order=descending|limit=15|format=template|template=drilldown|link=none|searchlabel=}}' );
		?>
	    <!-- content -->
	<div class="sk-userpage">
	    <div class="<?php $skin->msg( 'tweeki-container-class' ); ?> with-navbar-fixed <?php echo $skin->data['userstateclass']; echo ( $skin->checkVisibility( 'navbar' ) ) ? ' with-navbar' : ' without-navbar'; ?>">
	
				<div class="row">
					<div class="<?php echo $contentclass; ?>" role="main">
						<?php $skin->renderContent(); ?>
						<div class="sk-user-details">
							<div class="sk-user-since">
								Mitglied seit: 
								<span class="sk-user-value"><?php echo $registration->format( "j.n.Y" ); ?></span>
							</div>
							<div class="sk-user-studienrichtung">
								Meine Studienrichtungen: 
								<span class="sk-user-value">
									<?php echo $studienrichtungen; ?>
								</span>
								<?php if( $homevisitor ) {
									echo '<a href="' . Title::NewFromText( 'Spezial:FormEdit/Benutzer/Benutzer:' . $pageowner )->getFullURL() . '">hinzufügen/ändern</a>';
									} ?>
							</div>
							<?php if( $hasadmin ) { ?>
							<div class="sk-user-admin">
								Admin bei: 
								<span class="sk-user-value">Philosophie</span>
							</div>
							<?php } ?>
						</div>
						<div class="user-role">
							<?php echo $pageownerrole; ?>
						</div>
						<div class="user-contact">
							Kontaktiere mich<br>
							<a href="<?php echo Title::NewFromText( 'Special:E-Mail/' . $pageowner )->getFullURL(); ?>">
								<span class="icon-nachricht"></span>
							</a>
						</div>
					</div>
				</div>
	    </div>
	</div>
	<div class="sk-userpage-contribs">
		<div class="container">
			<div class="row">
				<div class="<?php echo $contentclass; ?>">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-6 sk-user-created">
								<h3>Von mir erstellte Lernunterlagen</h3>
								<?php echo $created; ?>
							</div>
							<div class="col-md-6 sk-user-authored">
								<h3>Von mir bearbeitete Lernunterlagen</h3>
								<?php echo $bearbeitet; ?>
							</div>
						</div>	
					</div>
				</div>
			</div>
		</div>
	</div>
	    <!-- /content -->
		<?php
		}

}
