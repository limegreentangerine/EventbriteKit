# Changelog

All notable changes to Eventbrite are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and
the project uses [Semantic Versioning](https://semver.org/).

## [0.1.0] - 2026-09-21

### Added

- Eventbrite API client for retrieving events.
- Event entity for storing imported Eventbrite events.
- Event search support, including item list, result, and column set classes and
  a search controller.
- Scheduled task to import events from Eventbrite, with command, handler, and
  task controller classes.
- Dashboard pages for Eventbrite, including settings and event management.
- Event list block.
- Eventbrite logger.

### Tests

- Added unit tests for the API client, event entity, settings controller, event
  import command handler, and import task controller.
- Added integration tests for API endpoints, settings persistence, and event
  import.
- Added test support utilities, fakes, and fixtures.

### Initial release

- Concrete CMS package scaffolding, requiring Concrete CMS 9.5.0, PHP 8.4, and
  the ClassKit package.
