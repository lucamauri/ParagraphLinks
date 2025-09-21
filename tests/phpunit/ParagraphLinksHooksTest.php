<?php

/**
 * Test suite for ParagraphLinks extension
 *
 * @group Extensions
 * @group ParagraphLinks
 * @covers ParagraphLinksHooks
 */
class ParagraphLinksHooksTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayEnabled() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_MAIN ]
		] );

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$title = Title::newMainPage();
		$request = new FauxRequest();

		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->once() )->method( 'addModules' )
			->with( 'ext.paragraphlinks' );

		ParagraphLinksHooks::onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayDisabled() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => false
		] );

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );

		$outputPage->expects( $this->never() )->method( 'addModules' );

		ParagraphLinksHooks::onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayWrongNamespace() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_MAIN ]
		] );

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$title = Title::makeTitle( NS_USER, 'TestUser' );
		$request = new FauxRequest();

		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		ParagraphLinksHooks::onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplayEditAction() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_MAIN ]
		] );

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$title = Title::newMainPage();
		$request = new FauxRequest( [ 'action' => 'edit' ] );

		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		ParagraphLinksHooks::onBeforePageDisplay( $outputPage, $skin );
	}

	/**
	 * @covers ParagraphLinksHooks::onBeforePageDisplay
	 */
	public function testOnBeforePageDisplaySpecialPage() {
		$this->overrideConfigValues( [
			'ParagraphLinksEnabled' => true,
			'ParagraphLinksNamespaces' => [ NS_SPECIAL ]
		] );

		$outputPage = $this->createMock( OutputPage::class );
		$skin = $this->createMock( Skin::class );
		$title = SpecialPage::getTitleFor( 'Version' );
		$request = new FauxRequest();

		$outputPage->method( 'getTitle' )->willReturn( $title );
		$outputPage->method( 'getRequest' )->willReturn( $request );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		ParagraphLinksHooks::onBeforePageDisplay( $outputPage, $skin );
	}
}