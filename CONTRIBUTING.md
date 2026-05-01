# Contributing to Spikster

Thank you for considering contributing to Spikster. We welcome contributions from everyone.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/<your-username>/Spikster.git`
3. Create a feature branch: `git checkout -b feat/my-feature`
4. Install dependencies: `composer install && npm install`
5. Copy `.env.example` to `.env` and configure your database
6. Run migrations: `php artisan migrate`

## Development Workflow

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Run `composer format` before committing (enforces PSR-12 via Laravel Pint)
- Run `composer analyse` for static analysis (PHPStan)
- Write tests for new features and bug fixes
- Ensure existing tests pass: `composer test`

## Pull Request Guidelines

- Use a clear, descriptive title prefixed by type (`feat:`, `fix:`, `refactor:`, `docs:`, etc.)
- Reference any related issues in the description
- Keep PRs focused — one feature or fix per PR
- Ensure tests pass and no new warnings are introduced
- Update documentation where applicable

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):
- `feat:` — new feature
- `fix:` — bug fix
- `refactor:` — code change without feature or fix
- `docs:` — documentation only
- `chore:` — maintenance, tooling, dependencies
- `test:` — adding or updating tests

## Reporting Issues

Report bugs and suggest features via [GitHub Issues](https://github.com/yolanmees/Spikster/issues). Include as much detail as possible: steps to reproduce, expected behavior, environment info, and relevant logs.
