# CONTRIBUTING.md

## Contributing to ParagraphLinks

Thank you for your interest in contributing to the ParagraphLinks MediaWiki extension! This document provides guidelines for contributing to the project.

## Development Setup

### Prerequisites

- MediaWiki 1.35.0 or higher
- PHP 7.4 or higher
- Node.js and npm (for linting and development tools)
- Git

### Setting Up Development Environment

1. **Clone the repository:**
   ```bash
   cd /path/to/mediawiki/extensions/
   git clone https://github.com/lucamauri/ParagraphLinks.git ParagraphLinks
   cd ParagraphLinks
   ```

2. **Install development dependencies:**
   ```bash
   npm install
   ```

3. **Enable the extension in MediaWiki:**
   Add to your `LocalSettings.php`:
   ```php
   wfLoadExtension( 'ParagraphLinks' );

   // Development settings
   $wgShowExceptionDetails = true;
   $wgDevelopmentWarnings = true;
   $wgMainCacheType = CACHE_NONE;
   $wgCacheDirectory = false;
   ```

> **Note:** No database update is required. Do not run `maintenance/update.php` for this extension.

## Code Standards

### PHP Code

- Follow [MediaWiki PHP coding conventions]
- Use tabs for indentation
- Add PHPDoc comments for all public methods
- Follow PSR-12 naming conventions
- All classes should have proper namespace declarations

### JavaScript Code

- Follow [MediaWiki JavaScript coding conventions]
- Use ESLint with Wikimedia configuration (included)
- Use modern ES6+ features when appropriate
- Add JSDoc comments for functions
- Use meaningful variable and function names

### CSS Code

- Follow [MediaWiki CSS coding conventions]
- Use meaningful class names with `paragraphlinks-` prefix
- Support accessibility features (high contrast, reduced motion)
- Test on multiple browsers and screen sizes

## Testing

### Running Tests

```bash
# Run PHP unit tests
php /path/to/mediawiki/tests/phpunit/phpunit.php extensions/ParagraphLinks/tests/phpunit/

# Run JavaScript linting
npm run lint

# Fix linting issues automatically
npm run lint:fix
```

### Writing Tests

- Write unit tests for all new functionality
- Test both success and failure scenarios
- Include edge cases in your tests
- Mock external dependencies properly
- Follow MediaWiki testing conventions

## Submitting Changes

### Pull Request Process

1. **Fork the repository** on GitHub

2. **Create a feature branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes:**
   - Write clear, concise commit messages
   - Keep commits focused and atomic
   - Add tests for new functionality
   - Update documentation as needed

4. **Test your changes:**
   ```bash
   # Run all tests
   npm run lint
   php /path/to/mediawiki/tests/phpunit/phpunit.php extensions/ParagraphLinks/
   ```

5. **Submit a pull request:**
   - Provide a clear description of the changes
   - Reference any related issues
   - Include screenshots for UI changes
   - Ensure all checks pass

### Commit Message Format

Use clear, descriptive commit messages following the conventional commits format:

```
fix: correct heading selector scope in CSS

- Scope position:relative to #mw-content-text headings only
- Prevent icon styles from leaking into sidebar and skin chrome

Fixes #123
```

## Code Review Guidelines

### For Contributors

- Be responsive to feedback
- Make requested changes promptly
- Ask questions if feedback is unclear
- Test thoroughly before requesting review

### For Reviewers

- Be constructive and helpful
- Explain the reasoning behind suggestions
- Focus on code quality, security, and maintainability
- Test the changes locally when possible

## Security Considerations

- Never process untrusted user input server-side
- Sanitize all generated HTML content
- Use secure clipboard APIs when available
- Follow MediaWiki security best practices
- Report security issues privately via the GitHub issue tracker

## Accessibility Requirements

- Support keyboard navigation
- Provide proper ARIA labels
- Ensure sufficient color contrast
- Support screen readers
- Test with accessibility tools
- Respect user preferences (reduced motion, high contrast)

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Graceful degradation for older browsers
- Mobile browser compatibility
- Test on different screen sizes

## Documentation

- Update README.md for user-facing changes
- Add inline code comments for complex logic
- Update configuration documentation
- Include examples in documentation

## Issue Reporting

### Bug Reports

When reporting bugs, please include:

- MediaWiki version
- PHP version
- Browser and version
- Steps to reproduce
- Expected vs actual behavior
- Console errors (if any)
- Screenshots (if applicable)

### Feature Requests

For feature requests, please include:

- Clear description of the feature
- Use case and motivation
- Proposed implementation (if any)
- Potential impact on existing functionality

## Release Process

1. Update the version number in `extension.json`
2. Create a GitHub release with a tag and release notes
3. Update the MediaWiki extension registry if applicable

## Getting Help

- Create an issue on GitHub for questions
- Check existing issues and documentation first
- Provide context and examples when asking questions

## License

By contributing, you agree that your contributions will be licensed under the same GPL-2.0-or-later license as the project.

[MediaWiki PHP coding conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions/PHP
[MediaWiki JavaScript coding conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions/JavaScript
[MediaWiki CSS coding conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions/CSS