# ParagraphLinks MediaWiki Extension

**ParagraphLinks** is a [MediaWiki extension][Extension homepage] that adds a hover-activated copy-link icon (🔗) to every section heading in wiki page content. Clicking the icon copies the full URL — including the heading's anchor fragment — to the clipboard, letting users share deep links to specific sections without inspecting the page source.

## Features

- **Hover link icons**: Shows a 🔗 icon when hovering over section headings (`h2`–`h6`)
- **One-click copy**: Copies the full URL with anchor fragment to the clipboard
- **User notification**: Displays a success or error message after the copy attempt
- **Accessible**: Full `aria-label` support for screen readers; keyboard-navigable
- **Mobile friendly**: Adapts to touch devices where hover is unavailable
- **Configurable**: Can be enabled or disabled globally and per namespace
- **No database changes**: Pure client-side implementation; no schema modifications required

## Requirements

- MediaWiki 1.35.0 or higher
- PHP 7.4 or higher
- JavaScript enabled in the browser

## Installation

**Manual installation**

1. Clone the repository into your `extensions/` directory:
   ```bash
   cd /path/to/mediawiki/extensions/
   git clone https://github.com/lucamauri/ParagraphLinks.git ParagraphLinks
   ```
2. Enable the extension in `LocalSettings.php`:
   ```php
   wfLoadExtension( 'ParagraphLinks' );
   ```

**Composer installation**

1. Add the package to your `composer.local.json`:
   ```json
   {
       "require": {
           "lucamauri/paragraphlinks": "~1.0"
       }
   }
   ```
2. Run Composer:
   ```bash
   composer update --no-dev
   ```
3. Enable the extension in `LocalSettings.php`:
   ```php
   wfLoadExtension( 'ParagraphLinks' );
   ```

> **Note:** No database update is required. Do not run `maintenance/update.php` for this extension.

## Configuration

Add any of the following to your `LocalSettings.php` after the `wfLoadExtension` call:

```php
// Enable or disable the extension globally (default: true)
$wgParagraphLinksEnabled = true;

// Namespaces where heading links are active
// Default: [0, 4, 10, 12, 14] (Main, Project, Template, Help, Category)
$wgParagraphLinksNamespaces = [
    NS_MAIN,      // 0
    NS_PROJECT,   // 4
    NS_TEMPLATE,  // 10
    NS_HELP,      // 12
    NS_CATEGORY   // 14
];
```

## How It Works

1. On each page view, the PHP hook `BeforePageDisplay` checks whether the extension is enabled and whether the current namespace is in `$wgParagraphLinksNamespaces`.
2. If both conditions are met, the `ext.paragraphlinks` ResourceLoader module is enqueued.
3. The JavaScript module attaches a 🔗 icon to every `h2`–`h6` element inside `#mw-content-text`.
4. Clicking the icon copies `window.location.href` — with the heading's `id` as the fragment — to the clipboard using the browser's native Clipboard API.
5. A MediaWiki notification confirms success or reports an error.

## File Structure

```
ParagraphLinks/
├── extension.json                        # Extension manifest
├── composer.json                         # Composer metadata
├── includes/
│   └── ParagraphLinksHooks.php           # PHP hook handler
├── resources/
│   ├── ext.paragraphlinks.js             # Client-side logic
│   └── ext.paragraphlinks.css           # Heading icon styles
├── i18n/
│   ├── en.json                           # English messages
│   ├── it.json                           # Italian messages
│   └── qqq.json                          # Message documentation for translators
├── tests/
│   └── phpunit/
│       └── ParagraphLinksHooksTest.php   # PHPUnit tests
├── CHANGELOG.md
├── CONTRIBUTING.md
├── README.md
└── LICENSE
```

## Development

### Running Tests

```bash
# Run PHPUnit tests
php tests/phpunit/phpunit.php extensions/ParagraphLinks/tests/phpunit/

# Run with HTML coverage report
php tests/phpunit/phpunit.php --coverage-html coverage extensions/ParagraphLinks/tests/phpunit/
```

### Development Setup

1. Clone into your MediaWiki `extensions/` directory and enable the extension.
2. Enable detailed error reporting in `LocalSettings.php`:
   ```php
   $wgShowExceptionDetails = true;
   $wgDevelopmentWarnings = true;
   $wgShowDBErrorBacktrace = true;
   ```
3. Disable caching to see changes immediately:
   ```php
   $wgMainCacheType = CACHE_NONE;
   $wgCacheDirectory = false;
   ```

## Contributing

1. Fork the repository on GitHub.
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make your changes and add or update tests.
4. Verify all tests pass.
5. Open a pull request against `main`.

Please follow [MediaWiki coding conventions][MW coding conventions] and add PHPDoc comments to all public methods.

## Troubleshooting

**Icons not appearing**
- Confirm the extension is enabled and `$wgParagraphLinksEnabled` is `true`.
- Check that the current page's namespace is listed in `$wgParagraphLinksNamespaces`.
- Verify JavaScript is enabled in your browser and there are no console errors.

**Copy to clipboard not working**
- The browser Clipboard API requires the page to be served over HTTPS.
- Check the browser console for permission errors.

## Security Considerations

- All processing happens client-side; no user input is sent to the server.
- No database modifications are made.
- The extension uses the browser's native Clipboard API when available.

## Browser Support

| Feature | Modern browsers | Older browsers |
|---|---|---|
| Clipboard copy | Native Clipboard API | `document.execCommand` fallback |
| Icon display | Full CSS support | Graceful degradation |

## License

This extension is licensed under the [GPL-2.0-or-later][LICENSE] license.

## Links

- [Extension homepage on MediaWiki.org][Extension homepage]
- [Source code on GitHub][Source Code]
- [Issue tracker][Issue Tracker]
- [MediaWiki coding conventions][MW coding conventions]

[Extension homepage]: https://www.mediawiki.org/wiki/Extension:ParagraphLinks
[Source Code]: https://github.com/lucamauri/ParagraphLinks
[Issue Tracker]: https://github.com/lucamauri/ParagraphLinks/issues
[LICENSE]: LICENSE
[MW coding conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions