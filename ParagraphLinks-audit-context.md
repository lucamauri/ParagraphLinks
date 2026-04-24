# ParagraphLinks — Extension Audit Report

**Date:** 2026-04-13  
**Branch audited:** `php84-compat`  
**Auditor:** Claude Code (claude-sonnet-4-6)

---

## 1. Extension Overview

### What the extension does
ParagraphLinks adds a hover-activated copy-link icon (🔗) to every heading (`h2`–`h6`) in wiki page content. Clicking the icon copies the full URL with the heading's anchor fragment to the clipboard and shows a success or error notification. Despite its name, the extension targets **headings**, not paragraphs (see §6 for the documentation discrepancy).

### Entry point and registration method
Loaded via `wfLoadExtension( 'ParagraphLinks' )` in `LocalSettings.php`, which reads `extension.json`. The manifest version is `2`.

### Hooks registered
| Hook | Handler | Registered in |
|---|---|---|
| `BeforePageDisplay` | `MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks::onBeforePageDisplay` | `extension.json` line 31 |

The hook handler checks whether the extension is globally enabled (`ParagraphLinksEnabled`), whether the page namespace is in `ParagraphLinksNamespaces`, and whether the action is `view` (not `edit`, `history`, or a special page). If all conditions pass it enqueues the `ext.paragraphlinks` ResourceLoader module.

### External API calls / external dependencies
None. The extension has no outbound HTTP calls. The only runtime dependencies are MediaWiki core (`mediawiki.util` is declared but unused — see §4) and the browser-native `navigator.clipboard` API (client-side).

**Section summary:** No issues found.

---

## 2. PHP 8.4 Compatibility Issues

### 2.1 Implicitly nullable parameters
No typed parameters with a `null` default were found in `includes/ParagraphLinksHooks.php`. The method at line 27 uses **untyped** parameters (`$out`, `$skin`), which avoids the implicitly-nullable problem but introduces a different concern (see §4).

### 2.2 Passing potentially null values to built-in functions
No calls to `strlen()`, `strtolower()`, `substr()`, `json_decode()`, etc. were found. Not applicable.

### 2.3 Deprecated `{$var}` string interpolation (PHP 8.2+)
Not present. The only string concatenation in `includes/ParagraphLinksHooks.php` (line 43) uses the `.` operator.

### 2.4 Dynamic property creation on non-stdClass objects
Not present.

**Section summary:** No PHP 8.4 issues found.

---

## 3. MediaWiki 1.43 API Issues

### 3.1 `wfGetDB()` usage
Not present.

### 3.2 `$wgHooks` in PHP files
Not present. Hooks are correctly registered in `extension.json`.

### 3.3 Legacy `User` static factory methods
Not present in production code.

### 3.4 `require_once`-style loading
Not present.

### 3.5 Legacy `Title` static factory methods in tests

**File:** `tests/phpunit/ParagraphLinksHooksTest.php`

| Line | Code | Issue |
|---|---|---|
| 24 | `Title::newMainPage()` | Legacy static factory; MW 1.41+ prefers `$this->getServiceContainer()->getTitleFactory()->newMainPage()` |
| 62 | `Title::makeTitle( NS_USER, 'TestUser' )` | Legacy static factory; prefer `TitleFactory::makeTitle()` via service container |
| 83 | `Title::newMainPage()` | Same as line 24 |
| 103 | `SpecialPage::getTitleFor( 'Version' )` | Legacy static method; `SpecialPageFactory` via service container is preferred in MW 1.38+ |

These are test-only usages, so they do not affect production functionality, but they will trigger deprecation notices in MW 1.43 test runs.

### 3.6 Static service locator in hook handler

**File:** `includes/ParagraphLinksHooks.php`, line 30  
```php
$config = MediaWikiServices::getInstance()->getMainConfig();
```
In MediaWiki 1.35+, the recommended pattern for hook handlers is constructor-based dependency injection using a non-static handler class. Static service locator calls are allowed but flagged by the MediaWiki phan config. For MW 1.43 compliance, the hook handler should be converted to an instance method with `MainConfig` injected via the constructor (or use the `HookHandler` interface).

### 3.7 `AutoloadClasses` vs `AutoloadNamespaces`

**File:** `extension.json`, line 33  
`AutoloadClasses` is a legacy mechanism. `AutoloadNamespaces` was introduced in MW 1.31 and should be used for PSR-4 namespaced extensions:
```json
"AutoloadNamespaces": {
    "MediaWiki\\Extension\\ParagraphLinks\\": "includes/"
}
```

**Section summary:** 6 issues found (4 legacy Title/SpecialPage calls in tests, 1 static service locator in production, 1 legacy autoload key).

---

## 4. Code Quality Issues

### 4.1 Missing PHP type declarations in hook handler

**File:** `includes/ParagraphLinksHooks.php`, line 27  
```php
public static function onBeforePageDisplay( $out, $skin ) {
```
The PHPDoc at lines 23–25 correctly declares `@param OutputPage $out` and `@param Skin $skin`, but the method signature itself lacks type declarations. PHP 8.x will not warn about this, but it is inconsistent with MediaWiki coding standards and prevents static analysis tools from catching type mismatches. Should be:
```php
public static function onBeforePageDisplay( OutputPage $out, Skin $skin ): void {
```

### 4.2 Registered i18n messages are not used in JavaScript

**File:** `resources/ext.paragraphlinks.js`, lines 39, 42  
The ResourceLoader module registers three i18n messages (`paragraphlinks-copy-link`, `paragraphlinks-copied`, `paragraphlinks-copy-failed`) in `extension.json` (lines 20–24). However, the JavaScript uses hardcoded English strings instead:

```javascript
mw.notify( 'Link copied!', { type: 'success', ... } );   // line 39
mw.notify( 'Copy failed', { type: 'error', ... } );       // line 42
```

These should use `mw.msg( 'paragraphlinks-copied' )` and `mw.msg( 'paragraphlinks-copy-failed' )` respectively. The message `paragraphlinks-copy-link` is also never used in the JS despite being registered.

### 4.3 Hardcoded `aria-label` and `title` in JavaScript

**File:** `resources/ext.paragraphlinks.js`, lines 29–30  
```javascript
icon.setAttribute( 'aria-label', 'Copy link to this section' );
icon.title = 'Copy link to this section';
```
These are hardcoded English strings. `mw.msg( 'paragraphlinks-copy-link' )` should be used here instead, which would also fix the unused message registration (see §4.2).

### 4.4 Unused `mediawiki.util` ResourceLoader dependency

**File:** `extension.json`, line 19  
`'mediawiki.util'` is listed as a dependency of the `ext.paragraphlinks` module, but it is never called anywhere in `ext.paragraphlinks.js`. This adds an unnecessary module to every page load where the extension is active. The dependency should be removed.

### 4.5 CSS not scoped to content area

**File:** `resources/ext.paragraphlinks.css`, lines 2–4  
```css
h2, h3, h4, h5, h6 {
    position: relative;
}
```
The `position: relative` rule is applied globally to **all** headings on the page, including those in the sidebar, navigation, table of contents, footer, and other skins' chrome. This can interfere with other extensions or skin styles. The rule should be scoped to the content area, e.g.:
```css
#mw-content-text h2,
#mw-content-text h3,
...
```
Similarly, the hover-reveal selectors (lines 21–27) are unscoped and would inject icon placeholders into any heading anywhere on the page (though the icon is only inserted into content headings by the JS).

### 4.6 Fragile `getTitle()` call without null guard

**File:** `includes/ParagraphLinksHooks.php`, line 38  
```php
$title = $out->getTitle();
```
`OutputPage::getTitle()` can return `null` in edge cases (e.g. during maintenance or API actions). The subsequent call on line 42 (`$title->getNamespace()`) will throw a fatal error if `$title` is `null`. A null guard should be added:
```php
$title = $out->getTitle();
if ( $title === null ) {
    return;
}
```

**Section summary:** 6 issues found.

---

## 5. `extension.json` Review

### 5.1 Metadata completeness
| Field | Present | Value |
|---|---|---|
| `name` | Yes | `ParagraphLinks` |
| `author` | Yes | `["Luca Mauri"]` |
| `license-name` | Yes | `GPL-3.0-or-later` |
| `url` | Yes | GitHub URL |
| `description` | Yes | One-line description |
| `version` | Yes | `1.0.1` |
| `type` | Yes | `other` |

Metadata is complete.

### 5.2 Compatibility matrix

**File:** `extension.json`, lines 10–12  
```json
"requires": {
    "MediaWiki": ">= 1.35.0"
}
```
- `requires.MediaWiki` is present but only specifies a minimum. No upper bound is stated, which is standard practice.
- **`requires.platform.php` is absent.** This field documents the PHP version requirement and is important for Packagist and extension manager tooling. Given `composer.json` requires `php: >=7.4`, the following should be added:
  ```json
  "requires": {
      "MediaWiki": ">= 1.35.0",
      "platform": {
          "php": ">= 7.4"
      }
  }
  ```

### 5.3 Consistency with PHP code
- The hook `BeforePageDisplay` → `ParagraphLinksHooks::onBeforePageDisplay` matches the actual class and method in `includes/ParagraphLinksHooks.php`.
- The `AutoloadClasses` entry matches the namespace and file path.
- Config keys `ParagraphLinksEnabled` and `ParagraphLinksNamespaces` match `$config->get(...)` calls in `ParagraphLinksHooks.php` (lines 33, 41).
- The three i18n message keys in the ResourceModule (lines 20–24) are all defined in `i18n/en.json`. However, they are not used in the JS (see §4.2).

**Section summary:** 1 issue found (`requires.platform.php` absent).

---

## 6. Documentation Review

### 6.1 README exists and language
`README.md` is present and written in English.

### 6.2 Paragraphs vs. headings mismatch

**File:** `README.md`, throughout  
The extension is called "ParagraphLinks" and the README repeatedly refers to "paragraphs", "paragraph content", and "paragraph links". However, the actual JavaScript implementation (`ext.paragraphlinks.js`, line 16) operates exclusively on `h2, h3, h4, h5, h6` elements — **headings**, not paragraphs (`<p>` elements). The README, feature descriptions, and extension name all describe paragraph-level linking, which the code does not implement. This is a systematic documentation/naming inconsistency.

### 6.3 Contradictory database instructions

**File:** `README.md`, lines 28–30 and lines 47–49  
Both the manual and Composer installation sections instruct users to run `php maintenance/update.php`. However:
- The README's own "Security Considerations" section states "No database modifications are made."
- `extension.json` does not define any `UpdateScripts`, `Tables`, or database-related keys.
- There is no `maintenance/` directory in the extension.

Running `update.php` is harmless but the instruction is misleading and contradicts the "No Database Changes" claim.

### 6.4 License inconsistency

**File:** `README.md`, line 182  
```
This extension is licensed under the GPL-2.0-or-later license.
```
Both `extension.json` (line 7) and `composer.json` (line 6) declare `GPL-3.0-or-later`. The README footer incorrectly states GPL-2.0-or-later. This is a factual error that could create legal uncertainty.

### 6.5 File structure table in README is incomplete

**File:** `README.md`, lines 88–104  
The documented file tree omits `CHANGELOG.md` and `CONTRIBUTING.md`, which are present in the repository root.

### 6.6 Inline comments
Inline comments in `ParagraphLinksHooks.php` are present and meaningful. The JavaScript file has adequate comments explaining each block. CSS comments are adequate for its brevity.

**Section summary:** 4 issues found (heading/paragraph mismatch, misleading `update.php` instructions, license inconsistency, incomplete file tree).

---

## 7. Suggested Improvements

### 7.1 Rename or broaden scope to match functionality
The extension is called "ParagraphLinks" and was presumably designed to link to paragraphs, but only links to headings. Either:
- Rename to `HeadingLinks` (or `SectionLinks`) and update all documentation/i18n, or
- Extend the JS to also operate on `<p>` elements within `#mw-content-text .mw-parser-output`, which is what the name implies.

The latter would be more useful to the broader MediaWiki ecosystem, as anchor-linking to individual paragraphs is a common user request.

### 7.2 Use dependency injection in the hook handler
Convert `ParagraphLinksHooks` from a static-method class to a proper DI-based handler:
```php
class ParagraphLinksHooks implements BeforePageDisplayHook {
    public function __construct( private MainConfig $config ) {}
    public function onBeforePageDisplay( OutputPage $out, Skin $skin ): void { ... }
}
```
Register it in `extension.json` using the `HookHandlers` key. This removes the `MediaWikiServices::getInstance()` call, making the code easier to test and phan-clean.

### 7.3 Use `mw.msg()` for all user-visible strings
All three strings currently hardcoded in JavaScript (notification messages, `aria-label`, `title` attribute) should go through `mw.msg()` using the already-registered keys. This makes the extension fully translatable with zero extra effort since the message keys and translations already exist.

### 7.4 Add more languages to i18n
Currently only English (`en.json`) and Italian (`it.json`) are provided. Contributing a `qqq.json` documenter entry for `paragraphlinks-copy-link` would also improve translator guidance (the current `qqq.json` description says "Message documentation for copy link tooltip" but does not mention it is the `aria-label` / `title` attribute of the icon, context that translators need).

### 7.5 Scope CSS to the content area
As noted in §4.5, the global heading selectors should be scoped to `#mw-content-text` to avoid unintended side-effects on other parts of the page.

### 7.6 Remove unused `mediawiki.util` dependency
As noted in §4.4, this should simply be removed from `extension.json`.

### 7.7 Add `requires.platform.php` to `extension.json`
As noted in §5.2, add the PHP platform requirement for better tooling compatibility.

### 7.8 Upgrade `AutoloadClasses` to `AutoloadNamespaces`
Modern extensions use `AutoloadNamespaces` for PSR-4 classes. Updating this is a one-line change in `extension.json` and removes the need to list each class individually.

### 7.9 Add a `ContentHandler` integration or `OutputPage::getRevisionId()` guard
Currently the extension loads the JS module even on redirect pages, category pages with no body text, and pages with no headings at all. A lightweight guard in PHP (e.g. check `$out->getRevisionId() !== 0`) would avoid loading the module on pages where it would be a no-op.

### 7.10 Consider adding a user preference toggle
Allowing logged-in users to disable the icons via `Special:Preferences` (using `Extension:BetaFeatures` or a plain `$wgDefaultUserOptions` preference) would make the extension more adoptable on public wikis where some users find persistent icons distracting.

---

## Summary Table

| Section | Issues |
|---|---|
| 1. Extension overview | No issues found |
| 2. PHP 8.4 compatibility | No issues found |
| 3. MediaWiki 1.43 API issues | 6 issues found |
| 4. Code quality | 6 issues found |
| 5. extension.json | 1 issue found |
| 6. Documentation | 4 issues found |
| 7. Suggested improvements | 10 suggestions |
| **Total defects** | **17** |
