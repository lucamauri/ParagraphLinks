<?php

/**
 * ParagraphLinks extension - Hooks
 *
 * @file
 * @ingroup Extensions
 * @license GPL-2.0-or-later
 */

class ParagraphLinksHooks {

	/**
	 * BeforePageDisplay hook handler
	 * Adds the extension's ResourceLoader module to appropriate pages
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		
		// Check if the extension is enabled
		if ( !$config->get( 'ParagraphLinksEnabled' ) ) {
			return;
		}

		$title = $out->getTitle();
		
		// Check if we're on a valid namespace
		$enabledNamespaces = $config->get( 'ParagraphLinksNamespaces' );
		if ( !in_array( $title->getNamespace(), $enabledNamespaces ) ) {
			return;
		}

		// Don't load on special pages, edit pages, or history pages
		if ( $title->isSpecialPage() || 
			 $out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return;
		}

		// Add our ResourceLoader module
		$out->addModules( 'ext.paragraphlinks' );
	}
}