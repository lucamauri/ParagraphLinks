<?php

/**
 * Test suite for ParagraphLinks extension
 *
 * @group Extensions
 * @group ParagraphLinks
 * @covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks
 */
class ParagraphLinksHooksTest extends MediaWikiIntegrationTestCase {

	/**
	 * Instantiate the hook handler with the current (possibly overridden) MainConfig.
	 *
	 * Must be called AFTER overrideConfigValues() so that the injected config
	 * already reflects the test-specific values.
	 *
	 * @return \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks
	 */
	private function makeHandler(): \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks {
		return new \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks(
			$this->getServiceContainer()->getMainConfig()
		);
	}

	/**
	 * @covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayEnabled() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_MAIN ]
		] );

		$title = $this->getServiceContainer()->getTitleFactory()->newMainPage();
		$request = new FauxRequest();

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->once() )->method( 'addModules' )
			->with( 'ext.paragraphlinks' );

		$this->makeHandler()->onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayDisabled() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => false
		] );

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		$this->makeHandler()->onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayWrongNamespace() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_MAIN ]
		] );

		$title = $this->getServiceContainer()->getTitleFactory()->makeTitle( NS_USER, 'TestUser' );
		$request = new FauxRequest();

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		$this->makeHandler()->onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayEditAction() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_MAIN ]
		] );

		$title = $this->getServiceContainer()->getTitleFactory()->newMainPage();
		$request = new FauxRequest( [ 'action' => 'edit' ] );

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		$this->makeHandler()->onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplaySpecialPage() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_SPECIAL ]
		] );

		$title = $this->getServiceContainer()->getSpecialPageFactory()->getTitleForAlias( 'Version' );
		$request = new FauxRequest();

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		$this->makeHandler()->onBeforePageDisplay( $outputPage, $skin );
	}
}