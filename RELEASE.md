# Release Policy

## Versioning

Spikster follows [Semantic Versioning 2.0.0](https://semver.org/):

- **MAJOR** — incompatible API changes or breaking database migrations
- **MINOR** — backward-compatible new features, deprecations
- **PATCH** — backward-compatible bug fixes, security patches

Pre-release tags use suffixes: `-alpha.X`, `-beta.X`, `-rc.X`.

## Release Cadence

- **Major releases**: approximately every 12 months
- **Minor releases**: approximately every 2–3 months
- **Patch releases**: as needed (hotfixes, security fixes)

## Release Process

1. All changes merged to `master` must pass CI (tests, static analysis, lint)
2. A maintainer creates a signed tag matching the version
3. Release notes are generated from commits (we use Conventional Commits)
4. The GitHub release is published with a changelog summary
5. Docker images and installer scripts are updated

## Deprecation Policy

- Deprecated features are announced in release notes for at least **one minor version** before removal
- A deprecation notice is added to relevant documentation
- Breaking changes only land in major version bumps
- Security patches are backported to the latest minor of the previous major for 6 months

## Supported Versions

Only the latest major release line receives active development. Security patches are backported to the previous major for 6 months after a new major release.
