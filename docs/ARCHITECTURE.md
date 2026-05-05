# Architecture

## System Overview

```mermaid
graph TD
    subgraph Client
        Browser["Vue 3 + Inertia.js"]
    end

    subgraph Server
        Router["Laravel Router"]
        Middleware["Role Middleware"]
        Auth["AuthController"]
        OT["OvertimeRequestController"]
        Schedule["ScheduleController"]
        Shift["ShiftContoller"]
        Hours["RequiredHoursController"]
        AI["OpenAIController"]
    end

    subgraph Data
        DB[("MySQL")]
        Config["Settings Table"]
    end

    subgraph External
        OpenAI["OpenAI API"]
    end

    Browser -->|"HTTP"| Router
    Router --> Middleware
    Middleware --> Auth
    Middleware --> OT
    Middleware --> Schedule
    Middleware --> Shift
    Middleware --> Hours
    Middleware --> AI
    Auth --> DB
    OT --> DB
    Schedule --> DB
    Shift --> DB
    Hours --> DB
    AI --> OpenAI
    OT --> Config
```

---

## Overtime Approval Workflow

The core of TimeTrack Pro is the overtime request lifecycle. Every request follows a strict state machine with defined transitions and validation rules.

### State Diagram

```mermaid
stateDiagram-v2
    [*] --> PENDING : Employee submits request

    PENDING --> APPROVED : Approver approves
    PENDING --> DISAPPROVED : Approver rejects (with remarks)

    PENDING --> PENDING : Employee updates reason/time

    APPROVED --> FILED : Approver files
    APPROVED --> DECLINED : Approver declines filing (with remarks)

    PENDING --> CANCELED : Employee cancels

    FILED --> [*]
    DISAPPROVED --> [*]
    DECLINED --> [*]
    CANCELED --> [*]
```

### State Descriptions

| State | Actor | Description |
|-------|-------|-------------|
| `PENDING` | Employee | Initial state after submission. Can update reason or times while pending. |
| `APPROVED` | Approver | Request validated and approved. Awaiting final filing. |
| `FILED` | Approver | Finalized and recorded. Terminal state. |
| `DISAPPROVED` | Approver | Rejected during review. Requires remarks explaining why. Terminal state. |
| `DECLINED` | Approver | Rejected during filing stage. Requires remarks explaining why. Terminal state. |
| `CANCELED` | Employee | Withdrawn by the employee. Terminal state. |

---

### Valid Transitions

```mermaid
graph LR
    subgraph Employee Actions
        Submit["Submit Request"]
        EditPending["Edit (PENDING)"]
        Cancel["Cancel"]
    end

    subgraph Approver Actions
        Approve["Approve"]
        Disapprove["Disapprove"]
        File["File"]
        Decline["Decline"]
    end

    Submit -->|PENDING| EditPending
    EditPending -->|PENDING| Approve
    EditPending -->|DISAPPROVED| Disapprove
    EditPending -->|CANCELED| Cancel
    Approve -->|APPROVED| File
    Approve -->|DECLINED| Decline
```

### Transition Rules

| From | To | Actor | Validation |
|------|----|-------|------------|
| `(new)` | `PENDING` | Employee | Shift overlap check, minimum hours, time boundary validation |
| `PENDING` | `PENDING` | Employee | Same as new submission (re-validates times and reason) |
| `PENDING` | `APPROVED` | Approver | Current status must be `PENDING` |
| `PENDING` | `DISAPPROVED` | Approver | Requires `remarks` (min 10 chars) |
| `PENDING` | `CANCELED` | Employee | Current status must be `PENDING` |
| `APPROVED` | `FILED` | Approver | Current status must be `APPROVED` |
| `APPROVED` | `DECLINED` | Approver | Requires `remarks` (min 10 chars) |

---

## Request Processing Flow

### Submission Validation

When an employee submits or updates a pending request, the system validates:

```mermaid
flowchart TD
    A[Submit Request] --> B{Has shift times?}
    B -->|No| G[Calculate hours]
    B -->|Yes| C{End > Start?}
    C -->|No| ERR1["Reject: swapped times"]
    C -->|Yes| D{Night shift?}
    D -->|Yes| D1[Adjust end time +1 day]
    D -->|No| E{Before or After shift?}
    D1 --> E
    E -->|Before| G
    E -->|After| G
    E -->|Overlap| ERR2["Reject: must be entirely before or after shift"]
    G --> F{Hours >= minimum?}
    F -->|Yes| SAVE[Create record as PENDING]
    F -->|No| ERR3["Reject: below minimum hours"]
```

### Approval Flow

```mermaid
sequenceDiagram
    participant E as Employee
    participant F as Frontend
    participant R as Router
    participant M as Role Middleware
    participant C as Controller
    participant DB as Database

    E->>F: Submit overtime request
    F->>R: POST /overtime/request
    R->>M: Check role (employee)
    M->>C: insertOvertimeRequest
    C->>DB: Validate shift overlap
    DB-->>C: Shift OK
    C->>DB: INSERT (status: PENDING)
    C-->>F: Redirect back

    Note over F: Approver views pending requests

    F->>R: GET /overtime/pending
    R->>M: Check role (approver)
    M->>C: fetchOvertimeRequestsViaStatus
    C->>DB: WHERE status = PENDING
    DB-->>C: Request list
    C-->>F: Render Pending page

    F->>R: POST /overtime/update/approver
    R->>M: Check role (approver)
    M->>C: updateOvertimeRequestStatus
    C->>DB: UPDATE status = APPROVED
    C-->>F: Redirect back
```

---

## Role-Based Access

```mermaid
graph TD
    subgraph Guest
        Login["POST /login"]
        Register["POST /register"]
        Reset["POST /reset-password"]
    end

    subgraph Employee
        Submit["POST /overtime/request"]
        Update["POST /overtime/update/employee"]
        MyRequests["GET /overtime/requests"]
        Heatmap["GET /overtime/heatmap"]
        MySchedule["POST /schedule/submit"]
        Profile["POST /profile/update/employee"]
    end

    subgraph Approver
        Pending["GET /overtime/pending"]
        Filing["GET /overtime/filing"]
        Filed["GET /overtime/filed"]
        Approve["POST /overtime/update/approver"]
        ManageSchedule["POST /schedule/employee/submit"]
        ManageShifts["POST /shift/register"]
        ManageHours["POST /hours/register"]
        ManageUsers["POST /users/update"]
        Report["GET /generate/report"]
    end

    subgraph Authenticated
        AI_Enhance["POST /ai/enhance"]
        AI_Analyze["POST /ai/analyze"]
    end
```

| Role | Middleware | Access |
|------|-----------|--------|
| Guest | `guest` | Login, register, password reset |
| Employee | `employee` | Own overtime CRUD, own schedule, profile, heatmap |
| Approver | `approver` | All employee access + approve/file/decline requests, manage shifts/hours/users/schedules, reports |
| Admin | `admin-approver` | Full approver access + system settings management |
| Any Auth | `auth` | AI enhance/analyze |

---

## Data Flow

### How Data Reaches the Dashboard

```mermaid
flowchart LR
    subgraph Write Path
        E["Employee submits OT"] --> V["Validation"]
        V -->|"Valid"| DB[(Database)]
        V -->|"Invalid"| ERR["Error Response"]
    end

    subgraph Read Path
        DB -->|"PENDING"| PD["Pending Page"]
        DB -->|"APPROVED"| FL["Filing Page"]
        DB -->|"FILED"| FD["Filed Page"]
        DB -->|"ALL"| DASH["Dashboard Stats"]
    end

    subgraph Computation
        DASH --> AGG["Aggregate by status"]
        AGG --> PIE["Pie Chart"]
        AGG --> BAR["Bar Chart"]
        DB -->|"Date Range"| HM["Heatmap"]
        DB -->|"Weekly"| REM["Remaining Hours"]
    end
```

### Organization Unit Scoping

All data is scoped to the authenticated user's `organization_unit_id`:

```mermaid
graph TD
    User["Auth::user()"]
    User -->|organization_unit_id| OUs["OrganizationUnit"]
    OUs -->|1:N| Emps["Users (employees)"]
    Emps -->|1:N| Sched["Schedules"]
    Sched -->|1:N| OT["Overtime Requests"]
    OUs -->|1:N| RH["Required Hours"]
    OT -->|N:1| Sched
```

Employees can only see their own data. Approvers see all employees within their organization unit.

---

## AI Integration

```mermaid
sequenceDiagram
    participant F as Frontend
    participant C as OpenAIController
    participant AI as OpenAI API

    F->>C: POST /ai/enhance {reason: "..."}
    C->>C: Validate reason not empty

    alt Validation fails
        C-->>F: 400 Missing reason
    end

    C->>AI: chat->create (validate reason quality)
    AI-->>C: VALID or INVALID

    alt INVALID
        C-->>F: 422 Please provide a valid work-related reason
    end

    C->>AI: chat->createStreamed (enhance reason)
    loop SSE chunks
        AI-->>C: Stream delta
        C-->>F: data: {"content": "..."}
    end
    C-->>F: data: [DONE]
```

---

Back: [README](../README.md)
