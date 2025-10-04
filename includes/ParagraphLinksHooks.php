<?php

/**
 * ParagraphLinks extension - Hooks
 *
 * @file
 * @ingroup Extensions
 * @license GPL-3.0-or-later
 */

namespace MediaWiki\Extension\ParagraphLinks;

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Skins\Skin;

class ParagraphLinksHooks {

	/**
	 * BeforePageDisplay hook handler
	 * Adds the extension's ResourceLoader module to appropriate pages
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( $out, $skin ) {
		$logger = LoggerFactory::getInstance( 'ParagraphLinks' );
		$logger->info( 'ParagraphLinks: onBeforePageDisplay called' );
		$config = MediaWikiServices::getInstance()->getMainConfig();

		// Check if the extension is enabled
		if ( !$config->get( 'ParagraphLinksEnabled' ) ) {
			$logger->info( 'ParagraphLinks: extension disabled' );
			return;
		}

		$title = $out->getTitle();

		// Check if we're on a valid namespace
		$enabledNamespaces = $config->get( 'ParagraphLinksNamespaces' );
		if ( !in_array( $title->getNamespace(), $enabledNamespaces ) ) {
			$logger->info( 'ParagraphLinks: namespace ' . $title->getNamespace() . ' not enabled' );
			return;
		}

		// Don't load on special pages, edit pages, or history pages
		if ( $title->isSpecialPage() ||
			 $out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			$logger->info( 'ParagraphLinks: not a view action or special page' );
			return;
		}

		// Add our ResourceLoader module
		$logger->info( 'ParagraphLinks: adding module ext.paragraphlinks' );
		$out->addModules( 'ext.paragraphlinks' );
	}
}