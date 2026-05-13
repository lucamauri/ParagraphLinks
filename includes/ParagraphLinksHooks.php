<?php

/**
 * ParagraphLinks extension - Hooks
 *
 * @file
 * @ingroup Extensions
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\ParagraphLinks;

use MediaWiki\Config\Config;
use MediaWiki\Hook\BeforePageDisplayHook;

/**
 * Hook handler for the ParagraphLinks extension.
 *
 * Receives its dependencies via constructor injection, wired through
 * the HookHandlers block in extension.json.
 */
class ParagraphLinksHooks implements BeforePageDisplayHook {

	/** @var Config */
	private Config $config;

	/**
	 * @param Config $config Main MediaWiki configuration object
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * BeforePageDisplay hook handler.
	 * Enqueues the ext.paragraphlinks ResourceLoader module on eligible pages.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		// Check if the extension is globally enabled
		if ( !$this->config->get( 'ParagraphLinksEnabled' ) ) {
			return;
		}

		$title = $out->getTitle();
		if ( $title === null ) {
			return;
		}

		// Check if the current namespace is in the enabled list
		$enabledNamespaces = $this->config->get( 'ParagraphLinksNamespaces' );
		if ( !in_array( $title->getNamespace(), $enabledNamespaces ) ) {
			return;
		}

		// Skip special pages and non-view actions (edit, history, etc.)
		if ( $title->isSpecialPage() ||
			$out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return;
		}

		$out->addModules( 'ext.paragraphlinks' );
	}
}