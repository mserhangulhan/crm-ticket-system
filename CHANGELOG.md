# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-10-27

### Added
- Webhook notification system (`WebhookDispatcher`) for real-time external integrations.
- Support for `ticket.created` and `ticket.status_updated` event triggers.
- Unit test suite for webhook subscriptions, HTTP delivery handling, and event dispatching.
- Auto-closed Issue #2.

## [1.0.0] - 2026-10-27

### Added
- Initial release of Open Source CRM Ticket System.
- RESTful JSON API endpoints for ticket creation, retrieval, and status updates.
- Flexible database abstraction supporting SQLite and MySQL via PDO.
- PHPUnit automated test suite with full test coverage for core service methods.
- GitHub Actions CI workflow for linting, dependency validation, and unit tests.
- Dependabot automated weekly dependency security monitoring.
- Contribution guidelines (PSR-12), Security Policy, and Code of Conduct.
