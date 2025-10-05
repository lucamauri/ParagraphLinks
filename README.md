# ParagraphLinks MediaWiki Extension
**ParagraphLinks** is a [MediaWiki extension][Extension homepage] that adds hover link icons to paragraphs, allowing users to easily copy direct links to specific content within wiki pages without needing to inspect the page source or manually add anchors.

## Features
- **Hover Link Icons**: Shows a link icon (🔗) when hovering over paragraphs
- **Automatic Anchor Generation**: Creates meaningful anchor IDs based on paragraph content
- **One-Click Copy**: Click the link icon to copy the full URL with anchor to clipboard
- **Mobile Friendly**: Adapts to touch devices where hover isn't available
- **Accessible**: Full keyboard navigation and screen reader support
- **Configurable**: Enable/disable per namespace and global settings
- **No Database Changes**: Pure client-side implementation with no schema modifications

## Installation

You can install ParagraphLinks in two ways:

**Manual Installation**  
1. Clone into your `extensions/` directory:  
   ```bash
   cd /path/to/mediawiki/extensions/
   git clone https://github.com/lucamauri/ParagraphLinks.git ParagraphLinks
   ```  
2. Enable the extension in `LocalSettings.php`:  
   ```php
   wfLoadExtension( 'ParagraphLinks' );
   ```  
3. Update your wiki database schema (if prompted):  
   ```bash
   php maintenance/update.php
   ```  

**Composer Installation (Local without Packagist)**  
1. in your `composer.local.json` file create or add the following to `require` section:
   ```json
   "require": {
      "lucamauri/paragraphlinks": "~1.0",
   }
   ```
   and run
   ```bash
   composer update --no-dev
   ```
2. Load the extension in `LocalSettings.php`:  
   ```php
   wfLoadExtension( 'ParagraphLinks' );
   ```  
3. Run schema updates if needed:  
   ```bash
   php maintenance/update.php
   ``` 

## Configuration

The extension provides several configuration options:

```php
// Enable or disable the extension globally (default: true)
$wgParagraphLinksEnabled = true;

// Namespaces where paragraph links are enabled
// Default: [0, 4, 10, 12, 14] (Main, Project, Template, Help, Category)
$wgParagraphLinksNamespaces = [
    NS_MAIN,        // 0 - Main namespace
    NS_PROJECT,     // 4 - Project namespace  
    NS_TEMPLATE,    // 10 - Template namespace
    NS_HELP,        // 12 - Help namespace
    NS_CATEGORY     // 14 - Category namespace
];
```

## How It Works

1. **Automatic Detection**: The extension scans all paragraphs in the main content area
2. **Anchor Generation**: Creates unique anchor IDs based on the first few words of each paragraph
3. **Visual Feedback**: Shows a link icon on hover (always visible on mobile)
4. **Clipboard Integration**: Uses modern Clipboard API with fallback for older browsers
5. **User Notification**: Shows success/error messages when copying links

## Browser Support

- **Modern Browsers**: Full support with Clipboard API
- **Older Browsers**: Fallback using `document.execCommand`
- **Mobile Devices**: Touch-optimized interface
- **Accessibility**: Screen reader and keyboard navigation support

## File Structure

```
ParagraphLinks/
├── extension.json                        # Extension configuration
├── includes/
│   └── ParagraphLinksHooks.php           # Server-side hooks
├── resources/
│   ├── ext.paragraphlinks.js             # Client-side JavaScript
│   └── ext.paragraphlinks.css            # Styles
├── i18n/
│   ├── en.json                           # English messages
│   └── qqq.json                          # Message documentation
├── tests/
│   └── phpunit/
│       └── ParagraphLinksHooksTest.php   # Unit tests
├── README.md
└── LICENSE
```

## Development

### Requirements

- MediaWiki 1.35.0 or higher
- PHP 7.4 or higher
- Modern browser with JavaScript enabled

### Running Tests

```bash
# Run PHPUnit tests
php tests/phpunit/phpunit.php extensions/ParagraphLinks/tests/phpunit/

# Run with coverage
php tests/phpunit/phpunit.php --coverage-html coverage extensions/ParagraphLinks/tests/phpunit/
```

### Development Setup

1. Clone the repository into your MediaWiki `extensions/` directory
2. Enable developer mode in MediaWiki:
   ```php
   $wgShowExceptionDetails = true;
   $wgDevelopmentWarnings = true;
   $wgShowDBErrorBacktrace = true;
   ```
3. Disable caching during development:
   ```php
   $wgMainCacheType = CACHE_NONE;
   $wgCacheDirectory = false;
   ```

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make your changes and add tests
4. Ensure all tests pass
5. Submit a pull request

### Code Standards

- Follow [MediaWiki coding conventions]
- Use tabs for indentation in PHP, spaces in JavaScript/CSS
- Add PHPDoc comments for all public methods
- Write unit tests for new functionality
- Ensure accessibility compliance

## Security Considerations

- The extension only operates on the client-side
- No user input is processed server-side
- Generated anchor IDs are sanitized
- No database modifications are made
- Uses secure clipboard API when available

## Troubleshooting

**Link icons not appearing:**
- Check that the extension is enabled in configuration
- Verify the current page's namespace is in `$wgParagraphLinksNamespaces`
- Ensure JavaScript is enabled in your browser

**Copy to clipboard not working:**
- Modern browsers require HTTPS for clipboard access
- Check browser console for JavaScript errors
- Verify clipboard permissions are granted

**Performance issues:**
- The extension only processes paragraphs with substantial content (>10 characters)
- Client-side processing is minimal and cached
- No server-side performance impact

## License

This extension is licensed under the GPL-2.0-or-later license. See the [LICENSE] file for details.

## Changelog

### Version 1.0.0
- Initial release
- Hover link icons for paragraphs
- Automatic anchor generation
- Clipboard integration
- Mobile support
- Accessibility features
- Configurable namespaces

## Links

- [MediaWiki Extension Documentation]
- [Issue Tracker]
- [Source Code]

[MediaWiki coding conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions
[LICENSE]: LICENSE
[MediaWiki Extension Documentation]: https://www.mediawiki.org/wiki/Manual:Extensions
[Issue Tracker]: https://github.com/lucamauri/ParagraphLinks/issues
[Source Code]: https://github.com/lucamauri/ParagraphLinks
[Extension homepage]: https://www.mediawiki.org/wiki/Extension:ParagraphLinks