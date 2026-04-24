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

---

## Post-refactor Assessment – 2026-04-24

**Branch:** `php84-compat`  
**Auditor:** Claude Code (claude-sonnet-4-6)  
**Commits reviewed:** `0a37b4e` (PHP/extension.json/i18n modernisation), `2b5232e` (JS/CSS), `b472bc2` (test suite modernisation), `245f859` (docs)

---

### 1. PHP code

> **Critical blocker found before individual checks can be validated.** Commit `b472bc2` ("fix: modernize test suite") accidentally wrote the new test-class content into `includes/ParagraphLinksHooks.php` instead of `tests/phpunit/ParagraphLinksHooksTest.php`. The correct hook class — which was properly implemented in the preceding commit `0a37b4e` — was overwritten and is no longer present in the repository. `includes/ParagraphLinksHooks.php` currently contains `class ParagraphLinksHooksTest extends MediaWikiIntegrationTestCase`, not `class ParagraphLinksHooks implements BeforePageDisplayHook`. The extension cannot load in this state.

The individual checks below are evaluated against the **intended** post-refactor state (as implemented in `0a37b4e`, before the accidental overwrite), with a note on actual on-disk status.

| Check | Intended state (0a37b4e) | Actual on-disk state |
|---|---|---|
| Implements `BeforePageDisplayHook` | PASS | **FAIL** — class is missing |
| Constructor injects `Config` | PASS | **FAIL** — class is missing |
| No `MediaWikiServices::getInstance()` calls | PASS | **FAIL** — class is missing |
| Type declarations on hook method (`OutputPage $out, Skin $skin): void`) | PASS | **FAIL** — class is missing |
| Null guard on `$out->getTitle()` | PASS | **FAIL** — class is missing |
| No `LoggerFactory` calls | PASS | **FAIL** — class is missing |
| `@license GPL-2.0-or-later` | PASS | **FAIL** — class is missing |

The hook class as written in `0a37b4e` (recoverable via `git show 0a37b4e:includes/ParagraphLinksHooks.php`) correctly addresses every item from the original audit. The sole remaining task is to restore that file.

---

### 2. extension.json

| Check | Result |
|---|---|
| `AutoloadNamespaces` used instead of `AutoloadClasses` | **PASS** |
| `HookHandlers` block present and correctly wired (`class`, `services: ["MainConfig"]`) | **PASS** |
| `Hooks` block references handler by key name (`"BeforePageDisplay": "ParagraphLinksHookHandler"`) | **PASS** |
| `mediawiki.util` absent from ResourceModule dependencies | **PASS** |
| `requires.platform.php` present (`>= 7.4`) | **PASS** |
| `descriptionmsg` used for extension and both config variables | **PASS** |
| License is `GPL-2.0-or-later` | **PASS** |

All seven `extension.json` checks pass.

---

### 3. JavaScript

| Check | Result |
|---|---|
| `aria-label` uses `mw.msg( 'paragraphlinks-copy-link' )` | **PASS** |
| `title` attribute uses `mw.msg( 'paragraphlinks-copy-link' )` | **PASS** |
| Success notification uses `mw.msg( 'paragraphlinks-copied' )` | **PASS** |
| `@license GPL-2.0-or-later` | **PASS** |

All JavaScript checks pass.

---

### 4. CSS

| Check | Result |
|---|---|
| `position: relative` rule scoped to `#mw-content-text h2…h6` | **PASS** |
| Hover-reveal selectors scoped to `#mw-content-text h2:hover…h6:hover` | **PASS** |

All CSS checks pass.

---

### 5. i18n

| Check | en.json | it.json | qqq.json |
|---|---|---|---|
| `paragraphlinks-desc` present | **PASS** | **PASS** | **PASS** |
| `paragraphlinks-config-enabled` present | **PASS** | **PASS** | **PASS** |
| `paragraphlinks-config-namespaces` present | **PASS** | **PASS** | **PASS** |
| User-visible strings free of the word "paragraph" (replaced with "section") | **PASS** | **PASS** | N/A |

All i18n checks pass. Every user-visible string in `en.json` and `it.json` uses "section" rather than "paragraph". The `qqq.json` descriptions now include full translator context (role of the string, attribute type, expected phrase length).

---

### 6. Test suite

| Check | Result |
|---|---|
| `Title::` static factory calls gone | **FAIL** — `Title::newMainPage()` (×2) and `Title::makeTitle()` (×1) still present in `tests/phpunit/ParagraphLinksHooksTest.php` |
| `SpecialPage::` static factory calls gone | **FAIL** — `SpecialPage::getTitleFor( 'Version' )` still present |
| `makeHandler()` helper present and used in all five test methods | **FAIL** — helper is absent from `tests/phpunit/ParagraphLinksHooksTest.php` |
| `@covers` annotations use fully-qualified class name | **FAIL** — annotations read `@covers ParagraphLinksHooks` (no namespace) |

All four test-suite checks fail. The intended new test content (with `makeHandler()`, `getServiceContainer()->getTitleFactory()`, `getServiceContainer()->getSpecialPageFactory()`, and `@covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks`) was accidentally committed to `includes/ParagraphLinksHooks.php` rather than `tests/phpunit/ParagraphLinksHooksTest.php`.

---

### 7. New issues

#### 7.1 Critical: file-swap regression in commit `b472bc2` (blocker)

Commit `b472bc2` performed a write to the wrong path. The diff shows that the body of the new `ParagraphLinksHooksTest` class was committed into `includes/ParagraphLinksHooks.php`, completely replacing the `ParagraphLinksHooks` hook class that had been correctly implemented two commits earlier. `tests/phpunit/ParagraphLinksHooksTest.php` was never touched.

**Consequence:** The extension is non-functional. `AutoloadNamespaces` maps `MediaWiki\Extension\ParagraphLinks\` to `includes/`, so PHP will attempt to load `ParagraphLinksHooksTest` when the hook handler is instantiated. The `HookHandlers` wiring in `extension.json` references the missing class, causing a fatal error on every page load.

**Fix:** Two files need to be set to their correct content:
- `includes/ParagraphLinksHooks.php` → restore the hook class from `git show 0a37b4e:includes/ParagraphLinksHooks.php`
- `tests/phpunit/ParagraphLinksHooksTest.php` → replace with the new test content currently sitting at `includes/ParagraphLinksHooks.php`

#### 7.2 Minor: `testOnBeforePageDisplayDisabled` skips the null guard path

In the disabled-extension test (`testOnBeforePageDisplayDisabled`), no `OutputPage::getTitle()` mock is set up. This is correct for the short-circuit path (the handler returns before calling `getTitle()`), but the test will produce a PHPUnit warning about unexpected mock method calls if the code path is ever reordered. Not a blocker; worth noting for robustness.

---

### Overall verdict

**Extension is NOT ready to merge to main.**

All `extension.json`, JavaScript, CSS, and i18n changes are correctly implemented and pass every check. However, commit `b472bc2` introduced a file-swap regression that makes the extension non-functional: the hook class (`includes/ParagraphLinksHooks.php`) was overwritten with test-class content, and the test file (`tests/phpunit/ParagraphLinksHooksTest.php`) was never updated. Both files must be corrected before the branch can be merged.

---

## Post-refactor Assessment – 2026-04-24 (Second pass, post-fix)

**Branch:** `php84-compat`  
**Auditor:** Claude Code (claude-sonnet-4-6)  
**Purpose:** Re-verify that the file-swap regression identified in the first-pass assessment has been resolved and all original refactor goals are met in the current working tree.

---

### 1. PHP code

| Check | Result |
|---|---|
| Implements `BeforePageDisplayHook` | **PASS** — `class ParagraphLinksHooks implements BeforePageDisplayHook` (line 24) |
| Constructor injects `Config` | **PASS** — `__construct( Config $config )` assigns to `$this->config` (lines 32–34) |
| No `MediaWikiServices::getInstance()` calls | **PASS** — none present |
| Type declarations on hook method | **PASS** — `onBeforePageDisplay( OutputPage $out, Skin $skin ): void` (line 43) |
| Null guard on `$out->getTitle()` | **PASS** — `if ( $title === null ) { return; }` (lines 50–52) |
| No `LoggerFactory` calls | **PASS** — none present |
| `@license GPL-2.0-or-later` | **PASS** — line 8 |

All seven PHP checks pass.

---

### 2. extension.json

| Check | Result |
|---|---|
| `AutoloadNamespaces` used instead of `AutoloadClasses` | **PASS** — lines 41–43 |
| `HookHandlers` block present and correctly wired (`class`, `services: ["MainConfig"]`) | **PASS** — lines 32–36 |
| `Hooks` block references handler by key name (`"BeforePageDisplay": "ParagraphLinksHookHandler"`) | **PASS** — lines 38–40 |
| `mediawiki.util` absent from ResourceModule dependencies | **PASS** — no `dependencies` key; module lists only `scripts`, `styles`, `messages` |
| `requires.platform.php` present | **PASS** — `">= 7.4"` at lines 12–14 |
| `descriptionmsg` used for extension and both config variables | **PASS** — line 6 and config entries at lines 46, 50 |
| License is `GPL-2.0-or-later` | **PASS** — line 7 |

All seven `extension.json` checks pass.

---

### 3. JavaScript

| Check | Result |
|---|---|
| `aria-label` uses `mw.msg( 'paragraphlinks-copy-link' )` | **PASS** — line 34 |
| `title` attribute uses `mw.msg( 'paragraphlinks-copy-link' )` | **PASS** — line 35 |
| Success notification uses `mw.msg( 'paragraphlinks-copied' )` | **PASS** — line 46 |
| Error notification uses `mw.msg( 'paragraphlinks-copy-failed' )` | **PASS** — line 53 |
| `@license GPL-2.0-or-later` | **PASS** — line 4 |

All JavaScript checks pass.

---

### 4. CSS

| Check | Result |
|---|---|
| `position: relative` rule scoped to `#mw-content-text h2…h6` | **PASS** — lines 2–8 |
| Hover-reveal selectors scoped to `#mw-content-text h2:hover…h6:hover .paragraphlinks-icon` | **PASS** — lines 25–30 |

Both CSS checks pass.

---

### 5. i18n

| Check | en.json | it.json | qqq.json |
|---|---|---|---|
| `paragraphlinks-desc` present | **PASS** | **PASS** | **PASS** |
| `paragraphlinks-config-enabled` present | **PASS** | **PASS** | **PASS** |
| `paragraphlinks-config-namespaces` present | **PASS** | **PASS** | **PASS** |
| User-visible strings use "section" not "paragraph" | **PASS** | **PASS** | N/A |

All i18n checks pass. Every user-facing value in `en.json` and `it.json` uses "section" or "heading" terminology; the word "paragraph" appears only in the extension/key names (proper nouns), which is expected.

---

### 6. Test suite

| Check | Result |
|---|---|
| `Title::` static factory calls gone | **PASS** — replaced with `$this->getServiceContainer()->getTitleFactory()->newMainPage()` (lines 35, 93) and `->makeTitle()` (line 72) |
| `SpecialPage::` static factory calls gone | **PASS** — replaced with `$this->getServiceContainer()->getSpecialPageFactory()->getTitleForAlias()` (line 114) |
| `makeHandler()` helper present and used in all five test methods | **PASS** — defined at line 20; called at lines 45, 60, 81, 102, 123 |
| `@covers` annotation uses fully-qualified class name | **PASS** — class-level annotation reads `@covers \MediaWiki\Extension\ParagraphLinks\ParagraphLinksHooks` (line 8); per-method annotations also use the FQCN (lines 27, 49, 64, 85, 106) |

All four test-suite checks pass.

---

### 7. New issues

#### 7.1 Minor: no `mediawiki.notification` dependency declared (advisory only)

`ext.paragraphlinks.js` calls `mw.notify()`, which is provided by the `mediawiki.notification` ResourceLoader module. The ResourceModule in `extension.json` declares no `dependencies` at all (the formerly unused `mediawiki.util` was correctly removed). In practice `mediawiki.notification` is loaded on every standard page view and the calls will work, but strict ResourceLoader correctness requires listing the module as an explicit dependency:

```json
"dependencies": ["mediawiki.notification"]
```

This is advisory — it will not cause failures on any standard wiki — but it should be addressed before submitting to the MediaWiki extension registry.

#### 7.2 Carry-forward minor: `testOnBeforePageDisplayDisabled` has no `getTitle()` mock expectation

Noted in the first-pass assessment (§7.2). The test correctly omits a `getTitle()` setup because the disabled-branch exits before that call, but PHPUnit will emit an unexpected-interaction warning if the code path is ever reordered. Not a blocker; the behaviour is intentional.

No regressions were introduced by fixing the file-swap. No issues from the original audit remain unresolved except the two advisory items above.

---

### Overall verdict

**All blocking checks passed — extension is ready to merge to main**, subject to the following optional improvements:

1. **Advisory:** Add `"dependencies": ["mediawiki.notification"]` to the `ext.paragraphlinks` ResourceModule in `extension.json` for strict ResourceLoader correctness.
2. **Advisory:** Consider adding a `getTitle()` mock expectation (or an explicit comment explaining its absence) in `testOnBeforePageDisplayDisabled` for future robustness.
