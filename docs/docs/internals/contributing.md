# Contributing

Guidelines for contributing to Yii2 AI Boost.

## Getting Started

1. Fork the repository: https://github.com/codeChap/mcp-server-yii2-ai-boost
2. Clone your fork locally
3. Create a feature branch: `git checkout -b feature/my-feature`
4. Make your changes
5. Run tests and linting
6. Push and create a pull request

## Development Setup

```bash
# Clone the repository
git clone https://github.com/codeChap/mcp-server-yii2-ai-boost.git
cd mcp-server-yii2-ai-boost

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer cs-check

# Run static analysis
composer analyze
```

## Code Standards

### PSR-12 Compliance

Code must follow PSR-12 style guide:

```bash
# Check style
composer cs-check

# Auto-fix style issues
composer cs-fix
```

### Strict Types

All PHP files must declare strict types:

```php
<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;
```

### Type Hints

Use type hints for all function parameters and return types:

```php
// ❌ Wrong
public function getName()
{
    return 'application_info';
}

// ✅ Correct
public function getName(): string
{
    return 'application_info';
}
```

### Documentation

Use PHPDoc blocks:

```php
/**
 * Sanitize output to remove sensitive data
 *
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
protected function sanitize(mixed $data): mixed
{
    // ...
}
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
composer test -- tests/Unit/Tools/ApplicationInfoToolTest.php

# Run with coverage
composer test:coverage
```

### Writing Tests

Place tests in `tests/` mirroring `src/` structure:

```
src/Mcp/Tools/MyTool.php
tests/Unit/Tools/MyToolTest.php
```

Example test:

```php
<?php

namespace codechap\yii2boost\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;
use codechap\yii2boost\Mcp\Tools\MyTool;

class MyToolTest extends TestCase
{
    private MyTool $tool;

    protected function setUp(): void
    {
        $this->tool = new MyTool();
    }

    public function testGetName(): void
    {
        $this->assertEquals('my_tool', $this->tool->getName());
    }

    public function testExecute(): void
    {
        $result = $this->tool->execute([
            'param1' => 'value1',
        ]);

        $this->assertIsArray($result);
    }
}
```

## Static Analysis

```bash
# Run PHPStan
composer analyze

# PHPStan is configured at level 8
```

Fix any issues found by PHPStan.

## Pull Request Process

### Before Creating PR

1. **Update from master**
   ```bash
   git fetch origin
   git rebase origin/master
   ```

2. **Run full test suite**
   ```bash
   composer test
   composer cs-check
   composer analyze
   ```

3. **Write clear commit messages**
   ```
   feat: Add new feature
   fix: Fix bug description
   docs: Update documentation
   ```

### PR Description

Include:
- **What** - What does this change do?
- **Why** - Why is this change needed?
- **How** - How does it work?
- **Tests** - What tests were added/updated?

Example:

```
## Description
Adds support for querying logs from Sentry error tracking service.

## Motivation
Users now have unified log access from multiple sources including Sentry.

## Implementation
- Created SentryLogReader implementing LogReaderInterface
- Integrated with LogInspectorTool
- Added unit tests

## Testing
- All existing tests pass
- Added SentryLogReaderTest with 95% coverage
```

### Code Review

Be open to feedback:
- Address comments professionally
- Explain your reasoning when appropriate
- Request changes if you disagree
- Thank reviewers for their time

## Types of Contributions

### 1. New Tools

See [Adding Tools](adding-tools.md) guide.

**Requirements:**
- Extends BaseTool
- Implements required methods
- JSON schema for parameters
- Comprehensive test coverage
- Documentation in /docs

### 2. New Readers

See [Adding Readers](adding-readers.md) guide.

**Requirements:**
- Implements LogReaderInterface
- Handles errors gracefully
- Returns normalized format
- Registered in LogInspectorTool
- Unit tests

### 3. Bug Fixes

Create an issue first if one doesn't exist:
- Describe the bug clearly
- Include reproduction steps
- Provide error logs if available

Then create a PR with:
- Reference to issue
- Minimal changes to fix bug
- Test that catches the bug
- Ensure other tests still pass

### 4. Documentation

Documentation improvements are welcome!

**Locations:**
- Main docs: `docs/docs/`
- Code comments: In-line in source
- README: `README.md`

### 5. Performance Improvements

Optimization PRs should include:
- Benchmark showing improvement
- Before/after metrics
- Explanation of approach
- No functional changes
- All tests passing

## Documentation Contributions

### Building Docs Locally

```bash
# Install dependencies
pip install -r docs/requirements.txt

# Serve docs locally
cd docs
mkdocs serve

# Visit http://localhost:8000
```

### Documentation Structure

- **Guide** - User documentation
- **Cookbook** - Practical recipes
- **Internals** - Developer documentation

Place new docs in appropriate section.

## Reporting Issues

### Before Creating Issue

- Check existing issues (including closed ones)
- Search GitHub for similar problems
- Review documentation

### Issue Template

```markdown
## Description
[Clear description of the issue]

## Steps to Reproduce
1. ...
2. ...
3. ...

## Expected Behavior
[What should happen]

## Actual Behavior
[What actually happens]

## Environment
- PHP Version: 8.1
- Yii2 Version: 2.0.45
- OS: macOS 12.6
- IDE: Claude Code

## Error Logs
[Relevant log output from @runtime/logs/]

## Additional Context
[Any other context]
```

## Commit Messages

Use clear, descriptive commit messages:

```
feat: Add support for X
  - Implement feature Y
  - Add tests for edge cases
  - Update documentation

fix: Resolve issue with X
  - Root cause: Y
  - Solution: Z
  - Fixes #123

docs: Improve documentation for X
  - Add examples
  - Clarify explanation
  - Fix typos

refactor: Improve code structure
  - Extract method Y
  - Reduce complexity
  - Improve readability
```

## Release Process

Maintainers use semantic versioning:
- **MAJOR** - Breaking changes
- **MINOR** - New features
- **PATCH** - Bug fixes

Example: v1.2.3

## Getting Help

### Questions?

- Check [Documentation](../guide/index.md)
- Review existing [Issues](https://github.com/codeChap/mcp-server-yii2-ai-boost/issues)
- Ask in Issue discussions

### Need Feedback?

Before creating a PR:
- Open an issue proposing your change
- Get feedback from maintainers
- Proceed with implementation once approved

## Code of Conduct

- **Be Respectful** - Treat others with respect
- **Be Inclusive** - Welcome diverse perspectives
- **Be Professional** - Keep discussions productive
- **Be Patient** - Response times vary

## License

By contributing, you agree that your contributions will be licensed under the BSD 3-Clause License (same as the project).

## Recognition

Contributors will be recognized in:
- CHANGELOG for each release
- GitHub contributors page
- Documentation acknowledgments

Thank you for contributing to Yii2 AI Boost! 🎉
