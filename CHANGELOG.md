# Changelog

All notable changes to `zazu-php` are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.1]

Version alignment: the whole SDK family now releases in lockstep with zazu-ruby. No functional changes since [0.1.0].

## [0.1.0]

Initial release.

### Added

- `Zazu\Client` built on Guzzle (named-argument construction, env-var fallbacks)
- Resources: `accounts`, `beneficiaries`, `checkoutSessions`, `customers`, `entity`, `invoices`, `paymentLinks`, `transferDrafts`, `webhookEndpoints`
- Cursor-based `Zazu\Page` with `next()` (max 100 records per page)
- `Zazu\Exception\ApiException` mirroring the shared SDK error taxonomy
- Cassette-replay test harness driven by the Ruby SDK's release tarball
