# Changelog

All notable changes to this project will be documented in this file.

## [v1.1.0] - 2026-04-15

### Added

- Password reset flow: forgot password and reset password pages with custom
  email notification template
- Breadcrumbs component for improved navigation across all pages
- Recent overtime requests list on employee dashboard
- Holiday badges on employee calendar days
- Overtime statistics calculation displayed on employee view
- Performance indexes on overtime and schedules tables for faster filtering
- NameHelper utility for generating user initials
- Warning state and streaming enhance support on overtime reason textarea
- AI validation guardrail and streaming response for reason enhancement
- Shift code and organization unit seeders with default data
- `make:config` artisan command and API endpoint for `setup/config.json`
- Concurrently dev dependency with unified start script
- Recent activities panel replacing pie chart on approver dashboard
- Highlight current year and week in dropdown options
- Production favicon and SVG app icon

### Changed

- Replace axios with native fetch API across all modules
- Rename OpenAI service and routes to generic AI naming with configurable
  env vars
- Rename ROA (Required Overtime Allowance) to Overtime Limit throughout
  the application
- Redesign login, register, and manage users pages with modern card-based
  layouts
- Redesign employee dashboard calendar and request statistics
- Redesign report page header and stat cards layout
- Replace inline SVGs with Iconify icons on all auth pages
- Move period selector to top bar on approver dashboard
- Separate recent requests and month overtimes data sources for calendar
- Extract minimum overtime hours to config file
- Replace default user seeder with organization unit and shift code seeders
- Update padding, height, and width consistency across all pages

### Fixed

- Prevent calendar navigation race condition and outside-month day handling
- Add CSRF token to schedule submission headers
- Fix response handling for native fetch in employee schedule, employee
  index, and approver manage schedule
- Disable autocomplete on login email and password inputs
- Fix Vue `ref` import in Modal component
- Adjust table column span and icon formatting in approver views

### Documentation

- Rewrite README with project overview, features, and system flow diagrams
- Add step-by-step setup guide with summary checklist
- Add database schema, installation, and API routes documentation
- Add mail configuration guide for Gmail and Outlook
- Add AI configuration section to setup guide

## [v1.0.0] - 2025-10-22

### Added

- Initial public deployment
- Employee overtime filing with shift validation and overlap detection
- Approver workflow: review, approve, decline, and file overtime requests
- Weekly schedule management for employees and approvers
- Shift code management with configurable time windows
- Weekly overtime limits per organization unit
- Analytics dashboard with ECharts (bar, pie, stacked bar charts)
- AI-powered reason enhancement (OpenAI API)
- AI-generated executive reports (SSE streaming)
- Philippine holidays integration (Nager.Date API)
- Role-based access: employee, approver, admin
- Light/dark theme support
- Profile management with avatar upload
