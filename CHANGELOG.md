# Changelog

All notable changes to EventbriteKit are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and
the project uses [Semantic Versioning](https://semver.org/).

## [0.2.0] - 2026-09-24

### Fixed

- Searching events by name on the dashboard no longer fails with an undefined
  method error; the search now uses `filterByName()`.
- The events dashboard search token is now generated and validated under the
  same action name (`events-search`).
- The events dashboard header search element is now cached and reused instead
  of being rebuilt on every call.
- Removed a duplicate response factory assignment in the API client constructor.
- Removed an unused item list instantiation from the events dashboard header
  search.

### Changed

- Added PHPDoc comments to functions across blocks, controllers, source, and
  tests.
- Added native `JsonResponse` return types to the API client's public methods.
- Loosened the `limegreentangerine/lgt_toolkit` constraint to `^0.0.0`.

## [0.1.0] - 2026-09-21

### Added

- EventbriteKit API client for retrieving events.
- Event entity for storing imported EventbriteKit events.
- Event search support, including item list, result, and column set classes and
  a search controller.
- Scheduled task to import events from EventbriteKit, with command, handler, and
  task controller classes.
- Dashboard pages for EventbriteKit, including settings and event management.
- Event list block.
- EventbriteKit logger.

### Tests

- Added unit tests for the API client, event entity, settings controller, event
  import command handler, and import task controller.
- Added integration tests for API endpoints, settings persistence, and event
  import.
- Added test support utilities, fakes, and fixtures.

### Initial release

- Concrete CMS package scaffolding, requiring Concrete CMS 9.5.0, PHP 8.4, and
  the ClassKit package.
