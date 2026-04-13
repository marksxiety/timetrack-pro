# Routes

All routes are defined in `routes/web.php` using Laravel's web routes
with Inertia.js.

---

## Public

| Method | URI            | Name      | Description                           |
| ------ | -------------- | --------- | ------------------------------------- |
| GET    | `/login`       | `login`   | Login page                            |
| POST   | `/login`       | —         | Authenticate user                     |
| GET    | `/register`    | `register` | Registration page                     |
| POST   | `/register`    | —         | Register new user                     |
| GET    | `/setup/config` | —        | Returns setup/config.json as JSON     |
| GET    | `/404`         | `404`     | Unauthorized / not found page         |

---

## Authenticated

*Requires `auth` middleware.*

| Method | URI               | Name             | Description                     |
| ------ | ------------------ | ---------------- | ------------------------------- |
| GET    | `/`                | `main`           | Role-based landing page         |
| POST   | `/logout`          | `logout`         | Log out the user                |
| POST   | `/openai/analyze`  | `openai.analyze` | AI report analysis (SSE stream) |
| POST   | `/openai/enhance`  | `openai.enhance` | AI reason enhancement           |

---

## Employee

*Requires `employee` middleware (auth + role: employee).*

### Shift Codes

| Method | URI                  | Name | Description                        |
| ------ | -------------------- | ---- | ---------------------------------- |
| GET    | `/employee/shift/list` | —  | Fetch available shift codes (JSON) |

### Schedule

| Method | URI                  | Name             | Description                     |
| ------ | -------------------- | ---------------- | ------------------------------- |
| GET    | `/schedule`          | `schedule`       | Manage schedule page            |
| GET    | `/schedule/list`     | —                | Fetch employee schedule (JSON)  |
| GET    | `/schedule/user`     | —                | Get user schedule (JSON)        |
| POST   | `/schedule/submit`   | `schedule.submit`| Submit or update schedule       |

### Overtime Requests

| Method | URI                        | Name                      | Description           |
| ------ | -------------------------- | ------------------------- | --------------------- |
| POST   | `/overtime/request`         | `overtime.request`        | File new request      |
| POST   | `/overtime/update/employee` | `overtime.update.employee`| Update/cancel pending |
| GET    | `/overtime/requests`        | `overtime.requests.employee` | View request history |

### Profile

| Method | URI                      | Name                  | Description          |
| ------ | ------------------------ | --------------------- | -------------------- |
| GET    | `/employee/profile`       | `profile.employee`    | View profile page    |
| POST   | `/profile/update/employee`| `profile.update.employee` | Update profile or avatar |

---

## Approver

*Requires `approver` middleware (auth + role: approver).*

### Shift Codes (CRUD)

| Method  | URI                 | Name           | Description   |
| ------- | ------------------- | -------------- | ------------- |
| GET     | `/shift`            | `shifts`       | Manage page   |
| POST    | `/shift/register`   | `shift.register` | Add new code |
| PUT     | `/shift/{shift}`    | `shift.update` | Update code   |
| DELETE  | `/shift/{shift}`    | `shift.delete` | Delete code   |
| GET     | `/approver/shift/list` | —             | Fetch codes (JSON) |

### Overtime Limits

| Method | URI                        | Name            | Description      |
| ------ | -------------------------- | --------------- | ---------------- |
| GET    | `/hours`                   | `hours`         | Manage page      |
| POST   | `/hours/register`          | `hours.register`| Register limit   |
| PUT    | `/hours/{requiredHours}`   | `hours.update`  | Update limit     |

### Overtime Requests (Approval)

| Method | URI                        | Name                      | Description          |
| ------ | -------------------------- | ------------------------- | -------------------- |
| POST   | `/overtime/update/approver` | `overtime.update.approver`| Approve/decline/file |
| GET    | `/overtime/pending`        | `overtime.pending`        | View pending requests|
| GET    | `/overtime/filing`         | `overtime.filing`         | View requests to file|
| GET    | `/overtime/filed`          | `overtime.filed`          | View filed requests  |

### Schedule Management

| Method | URI                        | Name            | Description            |
| ------ | -------------------------- | --------------- | ---------------------- |
| GET    | `/schedule/manage`         | `schedule.manage`| Manage page            |
| GET    | `/schedule/employee/list`  | —               | Fetch schedule matrix  |
| POST   | `/schedule/employee/submit`| —               | Submit employee schedules |

### User Management

| Method | URI               | Name                 | Description    |
| ------ | ----------------- | -------------------- | -------------- |
| GET    | `/users/registered` | `approver.manage.user` | Manage page  |
| POST   | `/users/update`   | `approver.update.user` | Update user   |

### Reports

| Method | URI                        | Name                            | Description  |
| ------ | -------------------------- | ------------------------------- | ------------ |
| GET    | `/generate/report/option`  | `approver.generate.report`      | Report page  |
| GET    | `/generate/report`         | `approver.generate.report.daterange` | Generate by date range |
