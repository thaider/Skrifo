<?php
/**
 * Navigational elements for Skrifo extension
 * 
 * @file
 * @ingroup Extensions
 */

class SkrifoNavigation {

	/**
	 * Studienrichtungen
	 *
	 * @param $skin
	 */
	static function Studienrichtungen( $skin ) {
		$mainpage = Title::newMainPage();
		$parseroptions = new ParserOptions();
		$localParser = new Parser();
		$localParser->Title ( $mainpage );
		$localParser->Options( $parseroptions );
		$localParser->clearState();

		$user = $skin->getSkin()->getUser()->getName();
		$customizedUserPage = $localParser->recursiveTagParse( "{{#ask:[[Benutzer:" . $user . "]][[UserInterest::+]]|mainlabel=-|?UserInterest}}" );
		if( $customizedUserPage !== '' ) {
			$result = $localParser->recursiveTagParse( "{{#ask:[[Benutzer:" . $user . "]]|?UserInterest=|link=none|format=list|mainlabel=-}}" );
			}
		else {
			$result = $localParser->recursiveTagParse( "{{#ask:[[Hierarchie::Studienrichtung]]|limit=5|format=list|link=none|searchlabel=}}" );
			$result = $localParser->recursiveTagParse( "{{#ask:[[Studienrichtung::+]][[Kategorie:Lernunterlage]]
|?Studienrichtung
|format=valuerank
|offset=0
|link=all
|headers=show
|maxtags=5
|liststyle=none
|limit=10000
|template=StudienrichtungsDropdown
}}" );
			$result = substr( $result, 5, strrpos( $result, ',' )-5 );
			}
		$result = explode( ", ", $result );
		foreach( $result as &$entry ) {
			$entry = trim( $entry );
			$entryurl = Title::newFromText( 'Spezial:Daten_durchsuchen/Lernunterlage' )->getFullURL( '_search_Studienrichtung=' . $entry );
			$entry = $entryurl . "|" . $entry . "|studienrichtung-top";
			}
		$result = "Studienrichtungen\n*\n*" . implode( "\n*", $result );
		if( $customizedUserPage === '' && $skin->getSkin()->getUser()->isLoggedIn() ) {
			$titleCustomize = Title::newFromText( 'Spezial:FormEdit' );
			$result .= "\n*".$titleCustomize->getFullURL()."/Benutzer/Benutzer:" . $user . "|<i>Liste individualisieren</i>|studienrichtung-top";
			}
		$result .= "\n*\n*Studienrichtungen|<span class='icon-weiter_studienrichtungen pull-right'></span>Alle Studienrichtungen|studienrichtung-toggle sk-dropdown-section";
		$studienrichtungen = array();
		$unis = $localParser->recursiveTagParse( "{{#ask:[[Hierarchie::Universität]]|format=list|link=none}}" );
		$unis = str_replace( 'Kategorie:', '', $unis );
		$unis = explode( ", ", $unis );
		foreach( $unis as $uni ) {
			$counter = $localParser->recursiveTagParse( "{{#ask:[[Hierarchie::Studienrichtung]][[Universität::" . $uni . "]]|format=count}}" );
			if( $counter > 0 ) {
				$studienrichtungen[$uni] = $localParser->recursiveTagParse( "{{#ask:[[Hierarchie::Studienrichtung]][[Universität::" . $uni . "]]|format=list|link=none}}" );
				$studienrichtungen = str_replace( 'Kategorie:', '', $studienrichtungen );
				$studienrichtungen = str_replace( ' (' . $uni . ')', '', $studienrichtungen );
				$studienrichtungen[$uni] = explode( ", ", $studienrichtungen[$uni] );
			}
		}
		echo '<script>wgStudienrichtungen = ' . json_encode( $studienrichtungen ) . ';</script>';
		$options = array(
			'wrapper' => 'li',
			'wrapperclass' => 'nav nav-block',
			'wrapperid' => 'studienrichtung-wrapper',
			'dropdownclass' => 'sk-studienrichtung-dropdown'
			);
		$buttons = TweekiHooks::parseButtons( $result, $localParser, false );
		echo TweekiHooks::renderButtons( $buttons, $options );
	}


	/**
	 * Footer
	 *
	 * @param $skin
	 */
	static function Footer( $skin ) {
		echo wfMessage( 'Tweeki-footer-custom' )->parse();
		}


	/**
	 * Download
	 *
	 * @param $skin
	 */
	static function Download( $skin ) {
		//Download-Buttons nur bei Skripten und Fragenausarbeitungen! 
		if ( $skin->data['namespace'] == "Skriptum" || $skin->data['namespace'] == "Fragenausarbeitung" ):
 		?>
			<div id="DownloadEdit" class="dropdown">
				<a class="dropdown-toggle btn-link" data-toggle="dropdown" href="#">
					<span class="tool-icon icon-download"></span><br>
					download
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-right">
<!--
					<li style="text-align:center"><i>derzeit nicht möglich</i></li>
-->
					<li><a href="<?php $titleDownload=Title::newFromText('Special:Book'); echo $titleDownload->getLocalURL( array( 'bookcmd' => 'render_article', 'arttitle' => $skin->data['title'], 'writer' => 'rl' ) ); ?>" title="als PDF herunterladen">als PDF-Datei</a></li>
<!--
					<li><a href="<?php echo $titleDownload->getLocalURL( array( 'bookcmd' => 'render_article', 'arttitle' => $skin->data['title'], 'writer' => 'odf' ) ); ?>" title="als Open Document herunterladen (bearbeitbar)">als Textdatei (ODT)</a></li>
					<li class="divider" />
					<li><a href="<?php $titleDownloadHelp=Title::newFromText('Hilfe:Download'); echo $titleDownloadHelp->getLinkURL(); ?>"><em>was ist der Unterschied?</em></a></li>
-->
				</ul>
			</div>
		<?php endif;

		// für Datei-Lernunterlagen
		if ( $skin->data['namespace'] == "Datei" ) {
			$href = $skin->getSkin()->getWikiPage()->getFile()->getFullUrl();
			$button = array(
				'href' => $href,
				'html' => '<span class="tool-icon icon-download"></span><br>download',
				'title' => 'Datei herunterladen'
				);
			$options = array(
				'wrapper' => 'div',
				'wrapperclass' => 'dropdown'
				);
		
			echo TweekiHooks::renderButtons( array( $button ), $options );
		}
	}


	/**
	 * Watch
	 *
	 * @param $skin
	 */
	static function Watch( $skin ) {
		$button = null;
		$options = array(
			'wrapperclass' => 'nav btn-group'
			);
		$actions = array_reverse( $skin->data['action_urls'] );
		if( isset( $actions['watch'] )  ) {
			$button = $actions['watch'];
			$options['wrapperid'] = $button['id'];
			unset( $button['id'] );
		} else if( isset( $actions['unwatch'] ) ) {
			$button = $actions['unwatch'];
			$options['wrapperid'] = $button['id'];
			unset( $button['id'] );
		}
		
		if( !is_null( $button ) ) {
			echo TweekiHooks::renderButtons( array( $button ), $options );
		}
	}


	/**
	 * EditHint
	 *
	 * @param $skin
	 */
	static function EditHint( $skin ) {
		echo $skin->getSkin()->getTitle()->getText();
	}


	/**
	 * ToTop
	 *
	 * @param $skin
	 */
	static function ToTop( $skin ) {
		$button = array(
			'href' => '#top',
			'html' => '<span class="icon-nachoben"></span>'
			);
		$options = array(
			'wrapperclass' => 'dropdown sk-totop'
			);
		echo TweekiHooks::renderButtons( array( $button ), $options );
	}


	/**
	 * Edit
	 *
	 * @param $skin
	 */
	static function Edit( $skin ) {
		$items = array();
		// volles Programm bei erweiterten Features
		// TODO: check for admin rights instead
		if ( $skin->checkVisibility( 'EDIT-EXT-special' ) ) {
			$views = $skin->data['view_urls'];
			if( count( $views ) > 2 ) {
				unset( $views['view'] );
				foreach( $views as $key => $item )
					if( ( $key != 've-edit' && $key != 'edit' ) && !( $skin->data['namespace'] == "Prüfungsfragen" && $key == 'formedit' ) ) {
						unset( $views[$key] );
					}
				if( count( $views ) > 0 ) {
					$items = $views;
				}
			}
				$actions = array_reverse( $skin->data['action_urls'] );
			foreach( $actions as $key => $item )
				if( $key != 'move' && $key != 'delete' ) {
					unset( $actions[$key] );
				}
			if( count( $actions ) > 0 ) {
				if( count( $items ) > 0 ) {
					$items[] = array();
				}
				$items = array_merge( $items, $actions );
			}
				$tools = array_reverse($skin->getToolbox());
			foreach( $tools as $key => $item ) {
				if( $key != 'administration' ) {
					unset( $tools[$key] );
				}
				if( !isset( $item['text'] ) ) {
					$item['text'] = $skin->translator->translate( isset( $item['msg'] ) ? $item['msg'] : $key );
				} 
			}
			if( count( $tools ) > 0 ) {
				if( count( $items ) > 0 ) {
					$items[] = array();
				}
				$items = array_merge( $items, $tools );
			}
			if( count( $items ) > 0 ) {
				$button = array(
					'html' => '<span class="tool-icon icon-bearbeiten"></span><br>bearbeiten',
					'id' => 'sk-edit',
					'href' => '#',
					'items' => $items
					);
				return array( $button );
			} else {
				return array();
			}
		// bloßer 'Bearbeiten'-Button für NormalnutzerInnen auf Lernunterlagen-Seiten
		} elseif( SkrifoHooks::IsLernunterlage( $skin ) ) {	
			$views = $skin->data['view_urls'];
			if(count( $views ) > 0) {
				unset( $views['view'] );
				$button = array_shift( $views );
				$button['id'] = 'ca-edit';
				$button['html'] = '<span class="tool-icon icon-bearbeiten"></span><br>bearbeiten';
				return array($button);
			}
		} else {
			return array();
		}
	}


	/**
	 * Lernunterlagen
	 *
	 * @param $skin
	 */
	static function Lernunterlagen( $skin ) {
		global $wgSkrifoLernunterlagenNS, $wgServer, $wgArticlePath;
	
		$icons = array(
			'Fragenausarbeitung' => 'fragenausarbeitung',
			'Prüfungsfragen' => 'pruefungsfragen',
			'Skriptum' => 'skriptum'
			);
		if( SkrifoHooks::IsLernunterlage( $skin ) || SkrifoHooks::IsLernunterlageFile( $skin ) ) {
			$title = $skin->getSkin()->getTitle()->getBaseText();
			if( SkrifoHooks::IsLernunterlageFile( $skin ) ) {
				$title = $skin->getSkin()->getTitle()->getPrefixedText();
				$title = $skin->getSkin()->getOutput()->parseInline( '{{#ask:[[' . $title . ']]|mainlabel=-|?LV=|link=none}}' );
			}
			$buttons = array();
			foreach( $wgSkrifoLernunterlagenNS as $key => $ns ) {		
				$newbutton = array();
				$nstitle = Title::newFromText( $ns . ':' . $title );
				if( $nstitle->exists() ) {
					$newbutton['html'] = '<span class="lernunterlage-icon icon-' . $icons[$ns] . '"></span> <span class="lernunterlage-text">' . $ns . '</span>';
					$newbutton['href'] = $nstitle->getFullURL();
					if( $nstitle->equals( $skin->getSkin()->getTitle() ) ) {
						$newbutton['active'] = true;
					}
					$newbutton['class'] = 'lernunterlage';
				} else {
					$newbutton['html'] = '<span data-toggle="tooltip" data-placement="right" title="' . $ns . ' erstellen"><span class="lernunterlage-icon icon-' . $icons[$ns] . '"></span> <span class="lernunterlage-text"><span class="icon-aufmachen lernunterlage-erstellen-icon"></span>' . $ns . '</span></span>';
					$newbutton['href'] = $wgServer . str_replace( '$1', '', $wgArticlePath ) . 'Spezial:LernunterlageErstellen/' . $ns . ':' . $title;
					$newbutton['class'] = 'lernunterlage lernunterlage-erstellen';
				}
				if( isset( $newbutton['active'] ) && $newbutton['active'] === true ) {
					array_unshift( $buttons, $newbutton );
				} else {
					$buttons[] = $newbutton;
				}
			}
			return $buttons;	
		} else {
			return array();
		}
	}


	/**
	 * Dateien
	 *
	 * @param $skin
	 */
	static function Dateien( $skin ) {
		global $wgSkrifoLernunterlagenNS, $wgServer, $wgArticlePath;
	
		if( SkrifoHooks::IsLernunterlage( $skin ) || SkrifoHooks::IsLernunterlageFile( $skin ) ) {
			$title = $skin->getSkin()->getTitle()->getBaseText();
			if( SkrifoHooks::IsLernunterlageFile( $skin ) ) {
				$title = $skin->getSkin()->getTitle()->getPrefixedText();
				$title = $skin->getSkin()->getOutput()->parseInline( '{{#ask:[[' . $title . ']]|mainlabel=-|?LV=|link=none}}' );
			}
			$countfiles = $skin->getSkin()->getOutput()->parseInline( '{{#ask:[[MaterialFürLV::' . $title . ']]|format=count}}' );
			if( $countfiles > 0 ) {
				echo '<div class="sk-sidebar-lernunterlage dropdown ' . ( SkrifoHooks::IsLernunterlageFile( $skin ) ? ' active' : '' ) . '">
					<a data-toggle="dropdown" class="lernunterlage" href="#">
						<span class="lernunterlage-icon icon-datei"></span> 
						<span class="lernunterlage-text"><span class="caret"></span>' . $countfiles . ' Datei' . ( $countfiles > 1 ? 'en' : '' ) . '</span> 
					</a>
					<ul class="dropdown-menu" id="file-dropdown-menu">';
				echo $skin->getSkin()->getOutput()->parseInline( '{{#ask:[[MaterialFürLV::' . $title . ']]|?MaterialTitel=title|?MaterialDesc=desc|?Unterlagentyp=typ|?Semester=semester|mainlabel=-|format=template|template=Datei|}}' );
				echo '</ul></div>';
			}
		}
	}


	/**
	 * Login
	 *
	 * @param $skin
	 */
	function Login( $skin, $context ) {
	  	global $wgUser, $wgRequest, $wgScript, $wgTweekiReturnto;
	  	
			if ( session_id() == '' ) {
				wfSetupSession();
			}
	
	  	//build path for form action
	  	$returnto = $skin->getSkin()->getTitle()->getFullText();
	  	if ( $returnto == SpecialPage::getTitleFor( 'UserLogin' ) 
	  		|| $returnto == SpecialPage::getTitleFor( 'UserLogout' ) ) {
	  		$returnto = Title::newMainPage()->getFullText();
	  		}
	  	$returnto = $wgRequest->getVal( 'returnto', $returnto );
		if ( isset( $wgTweekiReturnto ) && $returnto == Title::newMainPage()->getFullText() ) {
			$returnto = $wgTweekiReturnto;
		}
		//return to user page if logging in from main page
		if ( $returnto == Title::newMainPage()->getFullText() ) {
//			$returnto = 'Special:MyPage';	
		}
	  	$action = $wgScript . '?title=special:userlogin&amp;action=submitlogin&amp;type=login&amp;returnto=' . $returnto;
	  	
	  	//create login token if it doesn't exist
	  	if( !$wgRequest->getSessionData( 'wsLoginToken' ) ) $wgRequest->setSessionData( 'wsLoginToken', MWCryptRand::generateHex( 32 ) );
	  	$wgUser->setCookies();
		$target = Title::newFromText( 'Test' )->getFullURL();
	
			echo '<li class="nav nav-block">
			<a href="#" class="dropdown-toggle" type="button" id="n-login" data-toggle="dropdown">
	    	' . $skin->getMsg( 'userlogin' )->text() . '
	    	<span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-right skrifo-loginext" role="menu" aria-labelledby="' . $skin->getMsg( 'userlogin' )->text() . '" id="loginext">
				<!--<li class="divider sk-dropdown-top"></li>
				<li class="dropdown-header">über deine Universität</li>
				<li>
					<form action="https://skriptenforum.net/Shibboleth.sso/Login" method="get" name="userloginshib">
						<div class="container-fluid"><div class="row"><div class="col-md-12"><div class="select-container">
							<span class="icon-dropdown"></span>
							<select onchange="if(this.value){submit();}" name="entityID" class="form-control input-sm" disabled>
								<option value="">Universität auswählen</option>
								<option value="https://weblogin.univie.ac.at/shibboleth">Universität Wien</option>
							</select>
							</div>
							<input type="hidden" name="target" value="' . $target . '" />
							<input type="hidden" name="SAMLDS" value="1" />
						</div>
						<div class="col-md-2">
							<button class="btn btn-default btn-block btn-sm" type="submit">
								<span class="fa fa-chevron-right"></span>
							</button>
						</div></div></div>
					</form>
				</li>
				<li class="divider sk-dropdown-bottom"></li>-->
				<li class="divider sk-dropdown-top"></li>
				<li class="dropdown-header">mit Benutzername und Passwort</li>
				<li>
					<form action="' . $action . '" method="post" name="userloginext" class="clearfix">
						<div class="container-fluid"><div class="row"><div class="col-md-12">
							<div class="form-group">
								<label for="wpName2" class="hidden-xs sr-only">
									' . $skin->getMsg( 'userlogin-yourname' )->text() . '
								</label>';
			echo Html::input( 'wpName', null, 'text', array(
						'class' => 'form-control input-sm',
						'id' => 'wpName2',
						'tabindex' => '101',
						'placeholder' => $skin->getMsg( 'userlogin-yourname' )->text()
					) );					
			echo	'
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label for="wpPassword2" class="hidden-xs sr-only">
									' . $skin->getMsg( 'userlogin-yourpassword' )->text() . '
								</label>';
			echo Html::input( 'wpPassword', null, 'password', array(
						'class' => 'form-control input-sm',
						'id' => 'wpPassword2',
						'tabindex' => '102',
						'placeholder' => $skin->getMsg( 'userlogin-yourpassword' )->text()
					) );					
			echo '
							</div>
						</div>
						<div class="col-md-12">
							<a href="' . $wgScript . '?title=special:ResetPassword" class="sk-link-sm">
								' . $skin->getMsg( 'userlogin-resetpassword-link' )->text() . '
							</a>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" name="wpLoginAttempt" tabindex="103" id="wpLoginAttempt2" class="sk-link-primary pull-right btn-link">
									<b>LOGIN</b>
								</button>
							</div>
							<input type="hidden" value="' . $wgRequest->getSessionData( 'wsLoginToken' ) . '" name="wpLoginToken">
						</div></div></div>
					</form>
				</li>
				<li class="divider sk-dropdown-bottom"></li>';
		if( $wgUser->isAllowed( 'createaccount' ) ) {
			echo	'
				<li class="sk-dropdown-section">
					<a href="' . $wgScript . '?title=special:userlogin&amp;type=signup">
						<span class="icon-aufmachen pull-right"></span>' . $skin->getMsg( 'createaccount' )->text() . '
					</a>
				</li>';
			}
		echo '
			</ul>
			</li>';
		echo '<script>
				</script>';
	}
	
}
