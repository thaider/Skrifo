<?php
/**
 * Skrifo extension
 * 
 * @file
 * @ingroup Extensions
 * 
 * @author Tobias Haider <tobias@skriptenforum.net>
 * @license GPL v2 or later
 * @version 0.1.0
 */

// SETTINGS LADEN
// Damit eine Veröffentlichung auf github unproblematisch ist, enthält
// Skrifo.settings.php einige der Konfigurationen, die normalerweise in
// LocalSettings.php gesetzt würden
require_once( "Skrifo.settings.php" );


// EXTENSION SETUP
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Skrifo',
	'author' => array( 'Tobias Haider' ),
	'version' => '0.1.0',
	'url' => 'https://github.com/thaider/Skrifo',
	'descriptionmsg' => 'skrifo-desc',
);

$wgAutoloadClasses['SkrifoHooks'] = __DIR__ . '/Skrifo.hooks.php';
$wgAutoloadClasses['SkrifoNavigation'] = __DIR__ . '/Skrifo.navigation.php';
$wgAutoloadClasses['SpecialLernunterlageErstellen'] = __DIR__ . '/Skrifo.special.erstellen.php';
$wgAutoloadClasses['SpecialVortragende'] = __DIR__ . '/Skrifo.special.vortragende.php'; // TODO: temp
$wgAutoloadClasses['SpecialStudienrichtungen'] = __DIR__ . '/Skrifo.special.studienrichtungen.php'; // TODO: temp
$wgMessagesDirs['Skrifo'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['SkrifoAlias'] = __DIR__ . '/Skrifo.alias.php';
$wgExtensionMessagesFiles['SkrifoMagic'] = __DIR__ . '/Skrifo.i18n.magic.php';

// SPECIAL PAGES
$wgSpecialPages['LernunterlageErstellen'] = 'SpecialLernunterlageErstellen';
$wgSpecialPages['Vortragende'] = 'SpecialVortragende';
$wgSpecialPages['Studienrichtungen'] = 'SpecialStudienrichtungen';

// RESOURCE MODULES
$wgResourceModules['x.skrifo.styles'] = array(
	'styles' => array(
		'css/skrifo.less' => array( 'media' => 'screen' ),
		'css/icons.less' => array( 'media' => 'screen' ),
		'css/fonts.less' => array( 'media' => 'screen' )
	),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Skrifo',
);
$wgResourceModules['ext.skrifo.scripts'] = array(
	'scripts' => array( '/js/skrifo.js' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Skrifo',
	'dependencies' => array( 'ext.semanticforms.select2' )
);

// Ressourcen für SHIBBOLETH
// TODO: funktioniert noch nicht
//$wgHooks['BeforePageDisplay'][] = 'SkrifoHooks::ShibbolethResources';
$wgResourceModules['ext.Shibboleth.scripts'] = array(
	'scripts' => array('idpselect_config.js','idpselect.js'),
	'localBasePath' => '/usr/local/share/shibboleth-ds'
);
$wgResourceModules['ext.Shibboleth.styles'] = array(
	'styles' => array('idpselect.css'),
	'localBasePath' => '/usr/local/share/shibboleth-ds'
);


// HOOKS

// Resource Modules laden
$wgHooks['BeforePageDisplay'][] = 'SkrifoHooks::LoadScripts';

// Link zum VisualEditor nur bei Skripten und Fragenausarbeitungen
$wgHooks['SkinTemplateNavigation::Universal'][] = 'SkrifoHooks::HideVisualEditorInNavigation';

// Link zur Administration hinzufügen
$wgHooks['PersonalUrls'][] = 'SkrifoHooks::AdminLink';

// Bearbeiten-Link nur bei Skripten, Fragenausarbeitungen, Prüfungsfragen und für Poweruser
$wgHooks['TweekiSkinHidden'][] = 'SkrifoHooks::HideEditButton';

// BodyClass für Lernunterlagen hinzufügen
$wgHooks['SkinTweekiAdditionalBodyClasses'][] = 'SkrifoHooks::AdditionalBodyClasses';

//
$wgHooks['PersonalUrls'][] = 'SkrifoHooks::ChangeLinkUserpage';

// Login-Dropdown, Studienrichtungen
$wgHooks['ParserFirstCallInit'][] = 'SkrifoHooks::onParserSetup';

// Styling der Suchergebnisse anpassen
$wgHooks['ShowSearchHitTitle'][] = 'SkrifoHooks::onShowSearchHitTitle';

// Lehrveranstaltung und Lernunterlagen gleichzeitig umbenennen
$wgHooks['TitleMoveComplete'][] = 'SkrifoHooks::onTitleMoveComplete';


// GLOBALE VARIABLEN
$wgSkrifoLernunterlagenNS = array( 
	'skriptum' => 'Skriptum',
	'fragenausarbeitung' => 'Fragenausarbeitung',
	'pruefungsfragen' => 'Prüfungsfragen'
	);


// Anpassungen der TWEEKI skin 
// siehe http://tweeki.thai-land.at/

// TWEEKI Custom Bootstrap-Dateien
$wgTweekiSkinCustomizedBootstrap = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Skrifo'
	);

// TWEEKI Custom Page Renderer
$wgTweekiSkinPageRenderer = 'SkrifoHooks::PageRenderer';

// TWEEKI Custom CSS
$wgTweekiSkinCustomCSS[] = 'x.skrifo.styles';

// TWEEKI Navigational Elements für Skrifo
$wgTweekiSkinSpecialElements['STUDIENRICHTUNGEN'] = 'SkrifoNavigation::Studienrichtungen';
$wgTweekiSkinSpecialElements['FOOTER'] = 'SkrifoNavigation::Footer';
$wgTweekiSkinSpecialElements['SKRIFO-DOWNLOAD'] = 'SkrifoNavigation::Download';
$wgTweekiSkinSpecialElements['SKRIFO-WATCH'] = 'SkrifoNavigation::Watch';
$wgTweekiSkinSpecialElements['SKRIFO-TOTOP'] = 'SkrifoNavigation::ToTop';
$wgTweekiSkinNavigationalElements['SKRIFO-EDIT'] = 'SkrifoNavigation::Edit';
$wgTweekiSkinNavigationalElements['SKRIFO-LERNUNTERLAGEN'] = 'SkrifoNavigation::Lernunterlagen';
$wgTweekiSkinSpecialElements['SKRIFO-DATEIEN'] = 'SkrifoNavigation::Dateien';
$wgTweekiSkinSpecialElements['LOGIN-EXT'] = 'SkrifoNavigation::Login';
$wgTweekiSkinSpecialElements['SKRIFO-EDITHINT'] = 'SkrifoNavigation::EditHint';
