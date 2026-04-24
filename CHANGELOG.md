# Changelog

All notable changes to this project will be documented in this file.

## [v1.3.5] - 2026-04-24

### Added

- Reusable `PasswordInput` component with built-in show/hide toggle for auth forms
- Reusable `OvertimeRequestDetailModal` component for shared overtime request detail rendering
- Shared composables: `useBulkSelection`, `useOvertimeRequest`, and `useScheduleManager`
- Shared helper modules under `resources/js/Pages/utils/helpers` for date, status, format, color, and CSRF token handling

### Changed

- Refactor auth pages to use shared `PasswordInput` instead of duplicated inline password controls
- Refactor approver Filed/Filing/Pending pages to use shared overtime modal and centralized bulk selection logic
- Refactor employee Index/Request pages to use shared overtime reason enhancement/cancel flow and status/date helpers
- Refactor approver Manage Schedule and employee Schedule to use shared default-shift fill and shift-reference logic
- Refactor API/service modules (`schedule`, `ai`) to use shared CSRF helper and improve inline API docs

### Fixed

- Prevent empty `shift_code` updates in `submitSchedule`, and return `skipped_ids` for rows that cannot be removed
- Add warning state support to `SelectOption` and surface skipped schedule rows after schedule submission

### Style

- Enhance report page layout and improve UI elements
- Prevent navigation dropdown text wrapping in `Layout` menu

### Documentation

- Add JSDoc annotations across helper/composable/API/store modules for improved maintainability and editor hints`r`n`r`n## [v1.3.4] - 2026-04-23

### Added

- Bulk approve and bulk file functionality with selection checkboxes and confirmation modal on Pending and Filing views
- Active status indicator (green/red dot) on approver dashboard user avatars
- Avatar URL and active status fields in overtime request response data
- Deletion warning alert in ShiftCodes management modal
- Page descriptions on Manage Shift Codes and Manage Authorized Hours pages
- Shift reference sidebar on Manage Schedule page showing codes and time ranges

### Changed

- Rename "Operations" dropdown to "Administration" for approver role
- Rename navigation menu items for clarity (e.g., Schedule to Manage Schedules, My Schedule)
- Rename "RD / NWS" label to "RD / DAYOFF" in shift code form
- Redesign Manage Schedule layout with compact year/week selectors and shift reference card
- Simplify Filed view to read-only with clickable rows and removed action column

### Fixed

- Improve ShiftCodes and RequiredHours table layout with proper overflow handling

### Style

- Adjust approver dashboard chart and activity panel height alignment

## [v1.3.3] - 2026-04-22

### Fixed

- Heatmap controller now respects frontend status filter instead of hardcoding
  `APPROVED`, allowing FILED and PENDING data to appear in the heatmap and year
  pills

## [v1.3.2] - 2026-04-21

### Added

- Status filter functionality to heatmap component for filtering by request
  statuses
- Heatmap data endpoint support for accepting multiple statuses in
  `fetchHeatmapData`
- `schedulePage` method in `ScheduleController` to render schedule view with
  shift data
- Shift reference card on employee schedule page showing shift codes and time
  ranges

### Changed

- Replace inertia schedule route with controller method to pass shift data
- Redesign employee schedule layout with shift reference card and use injected
  shift props over API fetch

### Style

- Refine heatmap component layout and improve status filter styling
- Adjust heatmap layout and implement dynamic height synchronization

## [v1.3.1] - 2026-04-20

### Fixed

- Fix reason text rendering and display issues on overtime request views
- Fix heatmap tooltip positioning, data accuracy, and layout inconsistencies

## [v1.3.0] - 2026-04-19

### Added

- Overtime heatmap on employee page to visualize approved overtime hours
  with interactive tooltips
- Date range filtering for heatmap data
- Index on `overtime_requests` table for improved query performance

### Changed

- Redesign profile page layout with active toggle switch, updated form
  elements, and removed password strength bar
- Consolidate schedule and management links into single dropdown with
  role-based visibility

### Fixed

- Ensure dark mode configuration is correctly set in Tailwind CSS
- Fix AuthBackground blob blending effect for proper visibility in both
  light and dark mode

### Style

- Update calendar tile border color for improved visibility
- Improve heatmap layout, styling, and overflow handling for better
  responsiveness

## [v1.1.2] - 2026-04-18

### Added

- Employee schedule page redesign to monthly view with weekly rows and
  per-week default shift toggle
- Months dropdown options and `getWeeksInMonth` utility function
- Size prop to SelectOption component and center select text
- Sort dropdown to employee request filters with newest, oldest, and
  status sort values wired to fetch params
- Sorting support to employee overtime requests with date and status
  options
- Overtime request details modal with update, cancel, and AI reason
  enhancement
- `employee_schedule_id`, `created_at`, and `end_time` fields to
  overtime request response

### Changed

- Show schedule as direct nav link for employees, remove role checks
  from management dropdown
- Wrap schedule submission in database transaction for atomicity
- Improve validation rules in `updateShiftCode` method
- Enhance Modal component structure and styling for better usability
- Refactor email notification template to improve structure and styling
  for Gmail and Outlook compatibility
- Update modal titles for better clarity and user experience
- Set default `MAIL_TIMEOUT` value in mail configuration

### Fixed

- Safely parse year and month payload values to prevent NaN in calendar
- Handle exceptions when sending password reset link

### Style

- Add interactive cursor-following blobs background to all auth pages
  via shared `AuthBackground` component with `mix-blend-screen`
- Add calendar tile animations and refine layout and spacing in
  employee dashboard
- Improve layout and spacing in PaginationLinks and Request components
- Update modal content overflow and padding classes

## [v1.1.1] - 2026-04-17

### Added

- ConfigStore module to load and cache app config via shared reactive ref,
  injected into child components
- Custom TimePicker component with scrollable hour/minute/AM-PM selector,
  replacing SelectOption time inputs on employee overtime page
- `overtime_minute_step` key to default config in `make:config` command
- Regenerate button to AI insight engine with scrollIntoView behavior

### Changed

- Rewrite overtime validation to load shift from DB, use before/after
  classification, fix night shift handling, prevent Carbon mutation in
  `calculateOvertimeHours`, and enforce 0.25 increments
- Extract theme initialization into `getInitialTheme` in themeStore, sync
  `data-theme` and localStorage via watch instead of manual `setTheme` calls
- Replace initials avatar with image avatar and fallback initials in recent
  requests
- Move regenerate button into AI response card and simplify regeneration state
- Extract `readSSEStream` helper in AI service and `streamResponse` helper in
  OpenAI controller with SSE format
- Adjust request page table layout with scrollable container

### Fixed

- Fix holiday date display splitting from empty string to space
- Fix overtime chart height from `min-h-[45vh]` to `max-h-[45vh]`
- Fix SSE streaming reliability: disable gzip compression and output buffering
  for AI streaming routes
- Fix CSRF authentication in AI service and schedule API using XSRF-TOKEN
  cookie

### Testing

- Add OvertimeRequestValidationTest covering day/night shift overlap, swapped
  times, rest day, update, `calculateOvertimeHours`, and config validation
  cases
- Remove default Laravel example tests from Feature and Unit directories

### Documentation

- Add overtime filing scenarios covering day shift, night shift, rest day, and
  classification logic
- Update setup guide with cache clearing step, build checklist, and application
  launch instructions

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


