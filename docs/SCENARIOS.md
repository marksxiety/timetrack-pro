# Overtime Filing Scenarios

> **Config:** `minimum_overtime_hours = 1` (configurable via `setup/config.json`)
> **Rules:**
> - Overtime must be **entirely before** OR **entirely after** the shift
> - Touching the shift boundary is allowed
> - Duration must be >= `minimum_overtime_hours`
> - No overlap with shift allowed (including straddling or wrapping)
> - Shift times are loaded from the database, not the request

### Config: `minimum_overtime_hours`

The value must be in **15-minute (0.25) increments**. Any other value falls back to the default of `1.0`.

| Config value | Minutes | Valid config? |
|---|---|---|
| 0.25 | 15 min | ✅ |
| 0.50 | 30 min | ✅ |
| 0.75 | 45 min | ✅ |
| 1.00 | 1 hr | ✅ |
| 1.25 | 1 hr 15 min | ✅ |
| 1.50 | 1 hr 30 min | ✅ |
| 2.00 | 2 hr | ✅ |
| 0.20 | 12 min | ❌ falls back to 1.0 |
| 0.33 | 20 min | ❌ falls back to 1.0 |
| 0.40 | 24 min | ❌ falls back to 1.0 |

---

## Day Shift — Shift: 8:00 AM – 6:00 PM

### Before Shift

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 1 | 6:00 AM – 8:00 AM | 8AM–6PM | 2.00 hrs | ✅ | Entirely before shift, touches boundary, duration >= 1hr |
| 2 | 7:00 AM – 8:00 AM | 8AM–6PM | 1.00 hr | ✅ | Entirely before shift, touches boundary, duration = 1hr |
| 3 | 7:00 AM – 7:30 AM | 8AM–6PM | 0.50 hr | ❌ | Duration < 1hr minimum |
| 4 | 6:00 AM – 7:00 AM | 8AM–6PM | 1.00 hr | ✅ | Entirely before shift, does not touch boundary, duration = 1hr |
| 5 | 5:00 AM – 7:00 AM | 8AM–6PM | 2.00 hrs | ✅ | Entirely before shift, duration >= 1hr |
| 6 | 7:30 AM – 8:00 AM | 8AM–6PM | 0.50 hr | ❌ | Duration < 1hr minimum |
| 7 | 7:15 AM – 8:00 AM | 8AM–6PM | 0.75 hr | ❌ | Duration < 1hr minimum |

### After Shift

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 8 | 6:00 PM – 8:00 PM | 8AM–6PM | 2.00 hrs | ✅ | Entirely after shift, touches boundary, duration >= 1hr |
| 9 | 6:00 PM – 7:00 PM | 8AM–6PM | 1.00 hr | ✅ | Entirely after shift, touches boundary, duration = 1hr |
| 10 | 6:00 PM – 6:30 PM | 8AM–6PM | 0.50 hr | ❌ | Duration < 1hr minimum |
| 11 | 7:00 PM – 9:00 PM | 8AM–6PM | 2.00 hrs | ✅ | Entirely after shift, does not touch boundary, duration >= 1hr |
| 12 | 6:00 PM – 6:45 PM | 8AM–6PM | 0.75 hr | ❌ | Duration < 1hr minimum |
| 13 | 6:30 PM – 7:30 PM | 8AM–6PM | 1.00 hr | ✅ | Entirely after shift, duration = 1hr |

### Inside / Overlapping Shift

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 14 | 9:00 AM – 11:00 AM | 8AM–6PM | 2.00 hrs | ❌ | Entirely inside the shift |
| 15 | 12:00 PM – 1:00 PM | 8AM–6PM | 1.00 hr | ❌ | Entirely inside the shift |
| 16 | 8:00 AM – 9:00 AM | 8AM–6PM | 1.00 hr | ❌ | Start is at shift boundary, end is inside the shift |
| 17 | 5:00 PM – 6:00 PM | 8AM–6PM | 1.00 hr | ❌ | Start is inside the shift, end is at shift boundary |
| 18 | 4:00 PM – 7:00 PM | 8AM–6PM | 3.00 hrs | ❌ | Starts inside shift, straddles shift end |
| 19 | 7:00 AM – 9:00 AM | 8AM–6PM | 2.00 hrs | ❌ | Ends inside shift, straddles shift start |
| 20 | 7:30 AM – 6:30 PM | 8AM–6PM | 11.00 hrs | ❌ | Wraps the entire shift |

### Swapped / Inverted Times (End <= Start on same date)

> When end time is earlier than or equal to start time on the same date, the filing is ambiguous.
> The system rejects these because the implied duration crosses midnight and wraps into or through the shift.

| # | Filing | Shift | Implied Duration | Valid? | Reason |
|---|--------|-------|------------------|--------|--------|
| 21 | 8:00 AM – 6:00 AM | 8AM–6PM | 22.00 hrs | ❌ | End < Start — crosses midnight, would wrap through the shift |
| 22 | 8:00 AM – 8:00 AM | 8AM–6PM | 0.00 hr | ❌ | Zero duration (start = end) |
| 23 | 6:00 PM – 6:00 PM | 8AM–6PM | 0.00 hr | ❌ | Zero duration (start = end) |
| 24 | 6:00 PM – 5:00 PM | 8AM–6PM | 23.00 hrs | ❌ | End < Start — crosses midnight, wraps through the shift |
| 25 | 7:00 AM – 6:00 AM | 8AM–6PM | 23.00 hrs | ❌ | End < Start — crosses midnight, wraps through the shift |
| 26 | 9:00 AM – 8:00 AM | 9AM–5PM | 23.00 hrs | ❌ | End < Start — crosses midnight, wraps through the shift |

### Other Edge Cases (Day Shift)

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 27 | 8:00 AM – 6:00 PM | 8AM–6PM | 10.00 hrs | ❌ | Exact same time as shift — not before or after |

---

## Night Shift — Shift: 10:00 PM – 6:00 AM (crosses midnight)

> Schedule date: **Jan 1**
> Logical shift span: **Jan 1 10PM → Jan 2 6AM**
> Morning times (AM) are interpreted as "next day" (after shift) when applicable.

### Before Shift (evening of same day)

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 25 | 8:00 PM – 10:00 PM | 10PM–6AM | 2.00 hrs | ✅ | Entirely before shift, touches boundary, duration >= 1hr |
| 26 | 9:00 PM – 10:00 PM | 10PM–6AM | 1.00 hr | ✅ | Entirely before shift, touches boundary, duration = 1hr |
| 27 | 9:30 PM – 10:00 PM | 10PM–6AM | 0.50 hr | ❌ | Duration < 1hr minimum |
| 28 | 7:00 PM – 9:00 PM | 10PM–6AM | 2.00 hrs | ✅ | Entirely before shift, duration >= 1hr |
| 29 | 8:00 PM – 8:30 PM | 10PM–6AM | 0.50 hr | ❌ | Duration < 1hr minimum |

### After Shift (morning of next day)

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 30 | 6:00 AM – 8:00 AM | 10PM–6AM | 2.00 hrs | ✅ | Entirely after shift (auto-detected as next day), touches boundary, duration >= 1hr |
| 31 | 6:00 AM – 7:00 AM | 10PM–6AM | 1.00 hr | ✅ | Entirely after shift, touches boundary, duration = 1hr |
| 32 | 6:00 AM – 6:30 AM | 10PM–6AM | 0.50 hr | ❌ | Duration < 1hr minimum |
| 33 | 7:00 AM – 9:00 AM | 10PM–6AM | 2.00 hrs | ✅ | Entirely after shift, duration >= 1hr |
| 34 | 6:00 AM – 6:45 AM | 10PM–6AM | 0.75 hr | ❌ | Duration < 1hr minimum |

### Inside / Overlapping Night Shift

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 35 | 11:00 PM – 1:00 AM | 10PM–6AM | 2.00 hrs | ❌ | Entirely inside the shift |
| 36 | 2:00 AM – 4:00 AM | 10PM–6AM | 2.00 hrs | ❌ | Entirely inside the shift (auto-detected as next day) |
| 37 | 10:00 PM – 12:00 AM | 10PM–6AM | 2.00 hrs | ❌ | Start at shift boundary, end is inside shift |
| 38 | 5:00 AM – 6:00 AM | 10PM–6AM | 1.00 hr | ❌ | Inside the shift, ends at shift boundary |
| 39 | 9:00 PM – 1:00 AM | 10PM–6AM | 4.00 hrs | ❌ | Starts before shift, straddles shift start |
| 40 | 4:00 AM – 8:00 AM | 10PM–6AM | 4.00 hrs | ❌ | Starts inside shift, straddles shift end |
| 41 | 8:00 PM – 8:00 AM | 10PM–6AM | 12.00 hrs | ❌ | Wraps the entire shift |

### Swapped / Inverted Times (End <= Start on same date)

> When end time is earlier than or equal to start time on the same date, the filing is ambiguous.
> The system rejects these because the implied duration crosses midnight and would overlap with the shift.

| # | Filing | Shift | Implied Duration | Valid? | Reason |
|---|--------|-------|------------------|--------|--------|
| 42 | 8:00 PM – 7:00 PM | 10PM–6AM | 23.00 hrs | ❌ | End < Start — crosses midnight, wraps through the shift |
| 43 | 9:00 PM – 5:00 PM | 10PM–6AM | 20.00 hrs | ❌ | End < Start — crosses midnight, wraps through the shift |
| 44 | 10:00 PM – 9:00 PM | 10PM–6AM | 23.00 hrs | ❌ | End < Start — crosses midnight, wraps through the shift |
| 45 | 6:00 AM – 5:00 AM | 10PM–6AM | 23.00 hrs | ❌ | End < Start — crosses midnight, wraps through the shift |
| 46 | 7:00 AM – 7:00 AM | 10PM–6AM | 0.00 hr | ❌ | Zero duration (start = end) |
| 47 | 10:00 PM – 10:00 PM | 10PM–6AM | 0.00 hr | ❌ | Zero duration (start = end) |

### Other Edge Cases (Night Shift)

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 48 | 10:00 PM – 6:00 AM | 10PM–6AM | 8.00 hrs | ❌ | Exact same time as shift |

---

## No Shift / Rest Day (shift times = `--`)

> When a schedule has no shift times (rest day, holiday, etc.), the time overlap checks are skipped entirely.

| # | Filing | Shift | Duration | Valid? | Reason |
|---|--------|-------|----------|--------|--------|
| 49 | 8:00 AM – 5:00 PM | No shift | 9.00 hrs | ✅ | No shift to overlap with; duration >= 1hr |
| 50 | 9:00 AM – 9:30 AM | No shift | 0.50 hr | ❌ | Duration < 1hr minimum |
| 51 | 10:00 PM – 2:00 AM | No shift | 4.00 hrs | ✅ | No shift to overlap with; crosses midnight, duration >= 1hr |

---

## Classification Logic Summary

```
if shift has no times (rest day):
    skip overlap checks
    only enforce minimum duration

else:
    parse shift start/end from database
    detect night shift: raw shift_end < raw shift_start

    if night shift:
        shift_end += 1 day

    // Reject swapped/inverted times (end <= start on same date)
    // These cross midnight ambiguously and always overlap with the shift
    if submitted_end <= submitted_start:
        → REJECT (end is before or equal to start — ambiguous, wraps through shift)

    classify the filing:

        // "Before shift" test
        if submitted_end <= shift_start:
            → BEFORE SHIFT
            check: duration >= minimum_overtime_hours

        // "After shift" test
        else if submitted_start >= shift_end:
            → AFTER SHIFT
            check: duration >= minimum_overtime_hours

        // Night shift: try interpreting AM times as next day
        else if night shift:
            submitted_start += 1 day
            submitted_end += 1 day
            if submitted_start >= shift_end:
                → AFTER SHIFT (next day)
                check: duration >= minimum_overtime_hours

        else:
            → REJECT (overlaps, wraps, or straddles the shift)
```
