# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [1.0.2] – 2026-04-24
### Changed
- Convert hook handler from static class to instance-based with constructor dependency injection
- Implement `BeforePageDisplayHook` interface on hook handler class
- Wire `MainConfig` injection via `HookHandlers` block in `extension.json`
- Replace `AutoloadClasses` with `AutoloadNamespaces` in `extension.json`
- Scope CSS heading selectors to `#mw-content-text` to avoid side effects on skin chrome
- Replace all hardcoded English strings in JavaScript with `mw.msg()` calls
- Add `descriptionmsg` i18n keys for extension description and config variables
- Correct all user-visible strings from "paragraph" to "section" to match actual behaviour
- Simplify `CHANGELOG.md` and fix `CONTRIBUTING.md`

### Fixed
- Remove static `MediaWikiServices::getInstance()` service locator call from hook handler
- Add null guard on `$out->getTitle()` to prevent fatal error in edge cases
- Add type declarations to hook method signature
- Remove unused `mediawiki.util` ResourceLoader dependency
- Add `requires.platform.php` to `extension.json`
- Fix `@license` tag in PHP and JS files to `GPL-2.0-or-later`
- Fix license field in `composer.json` from `GPL-3.0-or-later` to `GPL-2.0-or-later`
- Fix clone URL placeholder in `CONTRIBUTING.md`
- Remove spurious `php maintenance/update.php` instructions from `README.md` and `CONTRIBUTING.md`
- Modernize test suite: replace legacy `Title::` and `SpecialPage::` static factory calls
- Update `@covers` annotations to fully-qualified class name in test suite

### Removed
- Debug `LoggerFactory` logging calls from hook handler


## [1.0.1] – 2025-10-04
### Fixed
- Fixed hook registration to use fully qualified class name with namespace: `MediaWiki\\Extension\\ParagraphLinks\\ParagraphLinksHooks::onBeforePageDisplay`
- Fixed ResourceLoader module registration by changing `"packageFiles"` to `"scripts"` in extension.json for proper auto-loading
- Fixed anchor ID detection to use existing MediaWiki-generated `<span id="">` elements instead of generating new IDs from heading text
- Fixed CSS positioning to prevent heading text from shifting when icons appear (changed to absolute positioning)
- Added missing `use MediaWiki\MediaWikiServices;` import to resolve "Class not found" error

### Changed
- Icon now appears to the left of headings (left-aligned) instead of right-aligned
- Improved heading detection logic to skip headings without proper MediaWiki anchor spans


## [1.0.0] – 2025-09-24
### Added
- Initial public release under the GPL-2.0-or-later license.
- Hover-activated link icons (🔗) for section headings (H2–H6).
- Clipboard API integration for one-click copy functionality.
- Accessibility: ARIA labels and screen-reader-friendly behavior.
- Configurable namespaces through `$wgParagraphLinksNamespaces`.
- Unit tests for hook registration and namespace checks.
- Support for touch devices with mobile-friendly behavior.
- Internationalization (i18n) support with English and message documentation files.
- Comprehensive README and developer guidelines.
- Composer support for installation via `composer require lucamauri/paragraphlinks`.


## [0.9.0-beta] – 2025-09-10
### Added
- Prototype extension proof-of-concept for paragraph link copying.
- Experimental JavaScript wrapper and hover interaction testing.
- Development-oriented logging for testing hook invocation.


## [0.1.0-alpha] – 2025-08-30
### Added
- Early internal prototype integrating custom ResourceLoader module.
- Initial MediaWiki LTS (1.39) compatibility testing.
- Proof-of-concept for anchor ID generation.
- Basic extension.json manifest creation.


[1.0.2]: https://github.com/lucamauri/ParagraphLinks/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/lucamauri/ParagraphLinks/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/lucamauri/ParagraphLinks/releases/tag/v1.0.0
[0.9.0-beta]: https://github.com/lucamauri/ParagraphLinks/releases/tag/v0.9.0-beta
[0.1.0-alpha]: https://github.com/lucamauri/ParagraphLinks/releases/tag/v0.1.0-alpha