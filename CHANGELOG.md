# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed

- Replace axios with native fetch API across all modules (overtime,
  schedule, shift, upcoming holidays, approver manage schedule, employee
  index)
- Fix response handling for native fetch in employee schedule, employee
  index, and approver manage schedule

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
