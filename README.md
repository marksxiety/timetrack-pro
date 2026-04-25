<div align="center">

# TimeTrack Pro

<p>
  <a href="https://github.com/marksxiety/timetrack-pro/actions/workflows/build.yml">
    <img src="https://github.com/marksxiety/timetrack-pro/actions/workflows/build.yml/badge.svg" />
  </a>
  <a href="https://github.com/marksxiety/timetrack-pro/actions/workflows/server-test.yml">
    <img src="https://github.com/marksxiety/timetrack-pro/actions/workflows/server-test.yml/badge.svg" />
  </a>
  <a href="https://github.com/marksxiety/timetrack-pro/actions/workflows/client-test.yml">
    <img src="https://github.com/marksxiety/timetrack-pro/actions/workflows/client-test.yml/badge.svg" />
  </a>
</p>

<p>
  <a href="https://github.com/marksxiety/timetrack-pro/releases/latest">
    <img src="https://img.shields.io/github/v/release/marksxiety/timetrack-pro?label=release" />
  </a>
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/license-MIT-green" />
</p>

</div>

---

## Overview

TimeTrack Pro digitizes the full overtime lifecycle from request filing to final approval and filing.

It focuses not only on tracking overtime hours, but also on improving the **quality of justification** behind each request using structured workflows and AI-assisted processing.

---

## Key Features

### Overtime Filing
- Employees submit overtime requests with reasons
- AI-enhanced description formatting for clarity
- Validation against schedules and policy rules

### Approval Workflow
- Multi-stage approval process
- Clear status tracking across the lifecycle
- Full audit trail of actions and changes

### Schedule Management
- Weekly shift configuration per employee
- Flexible shift codes and assignment rules

### Policy Enforcement
- Configurable overtime limits per department
- Prevents invalid or excessive submissions

### Analytics and Insights
- Dashboard with visual reports
- Trend analysis for overtime patterns
- AI-assisted summaries and insights

---

## Approval Workflow

TimeTrack Pro uses a structured lifecycle to ensure traceability and control over overtime requests.

### State Flow

Draft → Submitted → Approved → Pending Filing → Filed

### Alternative Outcomes

- Disapproved (rejected during approval stage)
- Declined (rejected during filing stage)
- Canceled (withdrawn by employee)

All transitions are logged to ensure full auditability.

---

## Tech Stack

- Backend: Laravel 11 (PHP 8.2+)
- Frontend: Vue 3 + Inertia.js
- Styling: TailwindCSS + DaisyUI
- Database: MySQL
- Charts: ECharts
- AI Integration: OpenAI API
- Build Tool: Vite 7

---

## Documentation

| Document | Description |
|----------|-------------|
| [Installation](docs/INSTALLATION.md) | Setup project dependencies |
| [Setup](docs/SETUP.md) | Environment configuration |
| [Database](docs/DATABASE.md) | Schema and relationships |
| [Routes](docs/ROUTES.md) | API and web route structure |
| [Architecture](docs/ARCHITECTURE.md) | System design, approval workflow, and data flow |