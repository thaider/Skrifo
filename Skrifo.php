<?php
/**
 * Skrifo extension
 * 
 * @file
 * @ingroup Extensions
 * 
 * @author Tobias Haider <tobias@skriptenforum.net>
 * @license GPL v2 or later
 * @version 2.0
 */

// LOAD SETTINGS
// To enable their publication on github without compromising our database
// credentials some of the configuration settings that you would normally
// find in LocalSettings.php have been moved to Skrifo.settings.php
require_once( "Skrifo.settings.php" );


// EXTENSION SETUP
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Skrifo',
	'author' => array( 'Tobias Haider' ),
	'version' => '2.0',
	'url' => 'https://github.com/thaider/Skrifo',
	'descriptionmsg' => 'skrifo-desc',
);

$wgAutoloadClasses['SkrifoHooks'] = __DIR__ . '/Skrifo.hooks.php';
$wgAutoloadClasses['SkrifoNavigation'] = __DIR__ . '/Skrifo.navigation.php';
$wgAutoloadClasses['SpecialLernunterlageErstellen'] = __DIR__ . '/Skrifo.special.erstellen.php';
$wgMessagesDirs['Skrifo'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['SkrifoAlias'] = __DIR__ . '/Skrifo.alias.php';
$wgExtensionMessagesFiles['SkrifoMagic'] = __DIR__ . '/Skrifo.i18n.magic.php';

// SPECIAL PAGES
$wgSpecialPages['LernunterlageErstellen'] = 'SpecialLernunterlageErstellen';

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


#########
# HOOKS #
#########

// Load Resource Modules
$wgHooks['BeforePageDisplay'][] = 'SkrifoHooks::LoadScripts';

// Link to VisualEditor only for "Skripten" and "Fragenausarbeitungen"
$wgHooks['SkinTemplateNavigation::Universal'][] = 'SkrifoHooks::HideVisualEditorInNavigation';

// Add Link to Administration
$wgHooks['PersonalUrls'][] = 'SkrifoHooks::AdminLink';

// Edit Button Only For 'Lernunterlagen' and Powerusers
$wgHooks['TweekiSkinHidden'][] = 'SkrifoHooks::HideEditButton';

// Add BodyClass For 'Lernunterlagen'
$wgHooks['SkinTweekiAdditionalBodyClasses'][] = 'SkrifoHooks::AdditionalBodyClasses';

// Rename Link To User Page
$wgHooks['PersonalUrls'][] = 'SkrifoHooks::ChangeLinkUserpage';

// Add Dropdowns for Login and Fields of Study
$wgHooks['ParserFirstCallInit'][] = 'SkrifoHooks::onParserSetup';

// Styling For Search Results
$wgHooks['ShowSearchHitTitle'][] = 'SkrifoHooks::onShowSearchHitTitle';

// Rename course and "Lernunterlagen" simultaneously
$wgHooks['TitleMoveComplete'][] = 'SkrifoHooks::onTitleMoveComplete';


// Global Variables
$wgSkrifoLernunterlagenNS = array( 
	'skriptum' => 'Skriptum',
	'fragenausarbeitung' => 'Fragenausarbeitung',
	'pruefungsfragen' => 'Prüfungsfragen'
	);
