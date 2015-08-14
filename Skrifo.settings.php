<?php

##############
# NAMESPACES #
##############
$wgExtraNamespaces = array(
	100 => "Skriptum", 
	101 => "Skriptum_Diskussion",
	102 => "Prüfungsfragen", 
	103 => "Prüfungsfragen_Diskussion", 
	104 => "Fragenausarbeitung",
	105 => "Fragenausarbeitung_Diskussion"
	);

define("NS_SKRIPTUM", 100);
define("NS_SKRIPTUM_TALK", 101);
define("NS_PFRAGEN", 102);
define("NS_PFRAGEN_TALK", 103);
define("NS_FRAGENA", 104);
define("NS_FRAGENA_TALK", 105);

$wgNamespacesWithSubpages += array(
	NS_SKRIPTUM => true,
	NS_PFRAGEN => true,
	NS_FRAGENA => true
	);

$wgNamespacesToBeSearchedDefault += array(
	NS_SKRIPTUM => true,
	NS_PFRAGEN => true,
	NS_FRAGENA => true,
	NS_CATEGORY => true,
	NS_FILE => true
	);

$wgNamespacesToBeSearchedDefault[NS_MAIN] = false;
	
$wgNamespaceAliases = array(
	'Skrifo' => NS_PROJECT
	);
	
$wgContentNamespaces = array (
	NS_MAIN, 
	NS_SKRIPTUM, 
	NS_PFRAGEN,
	NS_FRAGENA
	);

## only users with 'editinterface' right (sysops) can edit critical namespaces
$wgNamespaceProtection[NS_TEMPLATE] = array('editinterface');
$wgNamespaceProtection[NS_CATEGORY] = array('editinterface');
$wgNamespaceProtection[NS_PROJECT] = array('editinterface');
$wgNamespaceProtection[152] = array('editinterface');
$wgNamespaceProtection[154] = array('editinterface');
$wgNamespaceProtection[156] = array('editinterface');
$wgNamespaceProtection[158] = array('editinterface');
$wgNamespaceProtection[170] = array('editinterface');


####################
# HIDE PREFERENCES #
####################

$wgHiddenPrefs[] = 'gender';
$wgHiddenPrefs[] = 'skin';
$wgHiddenPrefs[] = 'imagesize';
$wgHiddenPrefs[] = 'thumbsize';
$wgHiddenPrefs[] = 'date';
$wgHiddenPrefs[] = 'timecorrection';
$wgHiddenPrefs[] = 'rows';
$wgHiddenPrefs[] = 'cols';
$wgHiddenPrefs[] = 'editsectiononrightclick';
$wgHiddenPrefs[] = 'editondblclick';
$wgHiddenPrefs[] = 'showtoolbar';
$wgHiddenPrefs[] = 'externaleditor';
$wgHiddenPrefs[] = 'externaldiff';
$wgHiddenPrefs[] = 'editfont';
$wgHiddenPrefs[] = 'rcdays';
$wgHiddenPrefs[] = 'rclimit';
$wgHiddenPrefs[] = 'hideminor';
$wgHiddenPrefs[] = 'usenewrc';
$wgHiddenPrefs[] = 'hidepatrolled';
$wgHiddenPrefs[] = 'newpageshidepatrolled';
$wgHiddenPrefs[] = 'watchlisthidebots';
$wgHiddenPrefs[] = 'watchlisthideanons';
$wgHiddenPrefs[] = 'watchlisthideliu';
$wgHiddenPrefs[] = 'smw-prefs-ask-options-tooltip-display';
$wgHiddenPrefs[] = 'smw-prefs-ask-options-collapsed-default';
$wgHiddenPrefs[] = 'srf-prefs-datatables-options-update-default';
$wgHiddenPrefs[] = 'srf-prefs-datatables-options-cache-default';
$wgHiddenPrefs[] = 'srf-prefs-eventcalendar-options-update-default';
$wgHiddenPrefs[] = 'srf-prefs-eventcalendar-options-paneview-default';
$wgHiddenPrefs[] = 'language';
$wgHiddenPrefs[] = 'underline';
$wgHiddenPrefs[] = 'stubthreshold';
$wgHiddenPrefs[] = 'showhiddencats';
$wgHiddenPrefs[] = 'numberheadings';
$wgHiddenPrefs[] = 'diffonly';
$wgHiddenPrefs[] = 'norollbackdiff';


###############
# PERMISSIONS #
###############

$wgGroupPermissions['*']['edit'] = false; # no editing for non-registered users
$wgGroupPermissions['user']['move'] = false; # no right to move pages for registered users

## stewards
$wgGroupPermissions['Steward']['delete']           = true;
$wgGroupPermissions['Steward']['deletedhistory']   = true; // can view deleted history entries, but not see or restore the text
$wgGroupPermissions['Steward']['undelete']         = true;
$wgGroupPermissions['Steward']['editusercssjs']    = true;
$wgGroupPermissions['Steward']['import']           = true;
$wgGroupPermissions['Steward']['importupload']     = true;
$wgGroupPermissions['Steward']['move']             = true;
$wgGroupPermissions['Steward']['move-subpages']    = true;
$wgGroupPermissions['Steward']['patrol']           = true;
$wgGroupPermissions['Steward']['autopatrol']       = true;
$wgGroupPermissions['Steward']['protect']          = true;
$wgGroupPermissions['Steward']['proxyunbannable']  = true;
$wgGroupPermissions['Steward']['rollback']         = true;
$wgGroupPermissions['Steward']['unwatchedpages']   = true;
$wgGroupPermissions['Steward']['autoconfirmed']    = true;
$wgGroupPermissions['Steward']['upload_by_url']    = true;
$wgGroupPermissions['Steward']['ipblock-exempt']   = true;
$wgGroupPermissions['Steward']['blockemail']       = true;
$wgGroupPermissions['Steward']['markbotedits']     = true;
$wgGroupPermissions['Steward']['apihighlimits']    = true;
$wgGroupPermissions['Steward']['browsearchive']    = true;
$wgGroupPermissions['Steward']['noratelimit']      = true;
$wgGroupPermissions['Steward']['movefile']         = true;

$wgGroupPermissions['Steward']['administrate'] = true; # "administration"-link will show up in toolbox
$wgGroupPermissions['sysop']['administrate'] = true;

## oversighting
$wgGroupPermissions['bureaucrat']['deleterevision']  = true;
$wgGroupPermissions['bureaucrat']['hideuser'] = true;
$wgGroupPermissions['bureaucrat']['suppressrevision'] = true;
$wgGroupPermissions['bureaucrat']['suppressionlog'] = true;

## all rights for parsoid
if ( isset( $_SERVER['REMOTE_ADDR'] ) && $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ) {
	$wgGroupPermissions['*']['read'] = true;
	$wgGroupPermissions['*']['edit'] = true;
}

$wgAddGroups['sysop'][] = 'Steward';
$wgRemoveGroups['sysop'][] = 'Steward';

$wgImplicitGroups[] = 'bot';
$wgImplicitGroups[] = 'smwadministrator';
$wgAutoPromote = array();


########
# SKIN # 
########

require_once( "$IP/skins/Tweeki/Tweeki.php" );
$wgTweekiSkinHideable = array( 'firstHeading', 'subnav', 'sidebar' ); 
$wgTweekiSkinHideAnon = array();
$wgTweekiSkinHideAll = array_merge( $wgTweekiSkinHideAll, array( 'footer-icons', 'footer-info-viewcount' ) );
$wgTweekiSkinFooterIcons = false;
$wgTweekiSkinUseTooltips = true;
$wgTweekiSkinUseBootstrapTheme = false;

$wgDefaultSkin = 'tweeki';


##############
# EXTENSIONS #
##############

## autoloading of extensions installed via composer (SemanticMediawiki et al.)
require( "$IP/vendor/autoload.php" );

## templatedata
require_once "$IP/extensions/TemplateData/TemplateData.php";
// Set this to true to enable the TemplateData GUI editor
$wgTemplateDataUseGUI = false;

## visual editor
require_once("$IP/extensions/VisualEditor/VisualEditor.php");
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgVisualEditorParsoidForwardCookies = true;
$wgVisualEditorParsoidURL = 'http://localhost:8142';
$wgVisualEditorSupportedSkins[] =  'tweeki';

## category-tree
require_once( "$IP/extensions/CategoryTree/CategoryTree.php" );
$wgCategoryTreeCategoryPageOptions['mode'] = 'all';

## syntax-highlighting
require_once( "$IP/extensions/SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.php" );

## parserfunctions
require_once( "$IP/extensions/ParserFunctions/ParserFunctions.php" );
$wgPFEnableStringFunctions = true;

## semantic mediawiki
$smwgNamespaceIndex = 150;
// included via composer's autoload
enableSemantics('skriptenforum.net');
$smwgNamespacesWithSemanticLinks += array(
	NS_SKRIPTUM => true, 
	NS_PFRAGEN => true, 
	NS_FRAGENA => true
	);
$smwgRSSWithPages = false; # im RSS-Feed von Queries nur Seitentitel anzeigen
$smwgLinksInValues = true;
//$smwgDefaultStore = 'SMWSQLStore2';

## semantic forms
$sfgNamespaceIndex = 200;
include_once("$IP/extensions/SemanticForms/SemanticForms.php");
$sfgAutocompleteOnAllChars = true;

## semantic form inputs
require_once('extensions/SemanticFormsInputs/SemanticFormsInputs.php');

# $sfgRenameEditTabs renames the edit-with-form tab to just "Edit", and
#   the traditional-editing tab, if it is visible, to "Edit source", in
#   whatever language is being used.
# The wgGroupPermissions 'viewedittab' setting dictates which types of
# visitors will see the "Edit" tab, for pages that are editable by form -
# by default all will see it.
$sfgRenameEditTabs = true;
$wgGroupPermissions['*']['viewedittab']   = false;
$wgGroupPermissions['sysop']['viewedittab']   = true;

## semantic drilldown
$sdgNamespaceIndex = 170;
include_once("$IP/extensions/SemanticDrilldown/SemanticDrilldown.php");
$sdgNumResultsPerPage=250; //Anzahl der ausgegebenen Seiten im Drilldown-Output
$sdgMinValuesForComboBox=0;

## semantic internal objects
include_once("$IP/extensions/SemanticInternalObjects/SemanticInternalObjects.php");

## semantic result formats - enabling additional formats
$srfgFormats[] = 'valuerank';

## inputbox
require_once( "$IP/extensions/InputBox/InputBox.php" );

## lookupuser
require_once( "$IP/extensions/LookupUser/LookupUser.php" );
$wgGroupPermissions['*']['lookupuser'] = false;
$wgGroupPermissions['sysop']['lookupuser'] = false;
$wgGroupPermissions['bureaucrat']['lookupuser'] = true;

## collection
require_once("$IP/extensions/Collection/Collection.php");
$wgCollectionMWServeURL = "http://localhost:8080";
#$wgCollectionMWServeCert = "";
$wgCollectionFormats = array(
           'rl' => 'PDF',
           'odf' => 'ODT',
       );
$wgCollectionLicenseURL = 'https://skriptenforum.net/w/index.php?title=Skriptenforum.net:CC-by-sa&action=raw';

       
## search log
//require_once( "$IP/extensions/SearchLog/SearchLog.php" );
//there was a problem with update.php and search log's sql

## newuser message
//include_once( "$IP/extensions/NewUserMessage/NewUserMessage.php" );

## skrifoDocuments: Dokumente hochladen
require_once( "$IP/extensions/SkrifoDocuments/SkrifoDocuments.php" ); 

## archivator
//require_once( "$IP/extensions/SkrifoArchivator/SkrifoArchivator.php" ); 
$wgSkrifoArchivatorRevisionLimit = 500;

## scribunto (lua)
require_once "$IP/extensions/Scribunto/Scribunto.php";
$wgScribuntoDefaultEngine = 'luastandalone';
## Editor für Lua-Module
require_once "$IP/extensions/WikiEditor/WikiEditor.php";
$wgDefaultUserOptions['usebetatoolbar'] = 1;
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;
require_once "$IP/extensions/CodeEditor/CodeEditor.php";
$wgScribuntoUseGeSHi = true;
$wgScribuntoUseCodeEditor = true;

## shibboleth
//require_once("$IP/extensions/ShibAuthPlugin/ShibAuthPluginConf.php");

## flagged revision
#require_once("$IP/extensions/FlaggedRevs/FlaggedRevs.php");
# Disable on-demand statistic generation.
#$wgFlaggedRevsStatsAge = false;

# Groups (eduPersonScopedAffiliation: "faculty@...")
$wgGroupPermissions['Lehrende']['review']    = true;
$wgGroupPermissions['Lehrende']['validate']    = true;

## UserMerge
require_once( "$IP/extensions/UserMerge/UserMerge.php" );
$wgGroupPermissions['bureaucrat']['usermerge'] = true;

## Captcha
require_once( "$IP/extensions/ConfirmEdit/ConfirmEdit.php" );
require_once( "$IP/extensions/ConfirmEdit/QuestyCaptcha.php");
$wgCaptchaClass = 'QuestyCaptcha';
$wgCaptchaQuestions[] = array( 'question' => "Wie lautet der Nachname des österreichischen Bundespräsidenten?", 'answer' => "Fischer" );


####################
# SPECIAL SETTINGS #
####################

// SemanticExtraSpecialProperties
$GLOBALS['sespSpecialProperties'] = array( '_EUSER' );

// jQuery Migrate (required by Semantic Result Formats et al.)
$wgIncludejQueryMigrate = true;
