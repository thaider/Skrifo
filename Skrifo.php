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

// SETUP
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Skrifo',
	'author' => array( 'Tobias Haider' ),
	'version' => '0.1.0',
	'url' => 'https://www.skriptenforum.net/',
	'descriptionmsg' => 'skrifo-desc',
);

$wgAutoloadClasses['SkrifoHooks'] = dirname( __FILE__ ) . '/Skrifo.hooks.php';
$wgAutoloadClasses['SkrifoNavigation'] = dirname( __FILE__ ) . '/Skrifo.navigation.php';
$wgMessagesDirs['Skrifo'] = __DIR__ . '/i18n';
// TODO: obsolete?
/*$wgExtensionMessagesFiles['SkrifoAlias'] = dirname( __FILE__ ) . '/Skrifo.alias.php';

// SPEZIALSEITE: NEUE LEHRVERANSTALTUNG
$wgAutoloadClasses['SkrifoNewCourse'] = dirname( __FILE__ ) . '/SpecialSkrifoNewCourse.php';
$wgSpecialPages['NewCourse'] = 'SkrifoNewCourse';
*/

$wgSkrifoSettings = array();


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

// TODO: obsolete?
$wgHooks['MagicWordwgVariableIDs'][] = 'SkrifoHooks::addMagicWordVariableIDs';
$wgHooks['LanguageGetMagic'][] = 'SkrifoHooks::addMagicWordLanguage';
$wgHooks['ParserBeforeTidy'][] = 'SkrifoHooks::handleShowAndHide';

// Link zum VisualEditor nur bei Skripten und Fragenausarbeitungen
$wgHooks['SkinTemplateNavigation::Universal'][] = 'SkrifoHooks::HideVisualEditorInNavigation';

// Link zur Administration hinzufügen
$wgHooks['PersonalUrls'][] = 'SkrifoHooks::AdminLink';

// Bearbeiten-Link nur bei Skripten, Fragenausarbeitungen, Prüfungsfragen und für Poweruser
$wgHooks['TweekiSkinHidden'][] = 'SkrifoHooks::HideEditButton';

// BodyClass für Lernunterlagen hinzufügen
$wgHooks['SkinTweekiAdditionalBodyClasses'][] = 'SkrifoHooks::AdditionalBodyClasses';


// GLOBALE VARIABLEN
$wgSkrifoLernunterlagenNS = array( 
	'skriptum' => 'Skriptum',
	'fragenausarbeitung' => 'Fragenausarbeitung',
	'pruefungsfragen' => 'Prüfungsfragen'
	);


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
$wgTweekiSkinNavigationalElements['SKRIFO-EDIT'] = 'SkrifoNavigation::Edit';
$wgTweekiSkinNavigationalElements['SKRIFO-LERNUNTERLAGEN'] = 'SkrifoNavigation::Lernunterlagen';
$wgTweekiSkinSpecialElements['SKRIFO-DATEIEN'] = 'SkrifoNavigation::Dateien';
$wgTweekiSkinSpecialElements['LOGIN-EXT'] = 'SkrifoNavigation::Login';
