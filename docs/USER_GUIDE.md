# AACCUP PAMS User Guide

This guide explains how each AACCUP PAMS user role uses the system to monitor accreditation compliance.

## Getting Started

1. Open the AACCUP PAMS web address supplied by your administrator.
2. Sign in with your institutional email address and password.
3. The system opens on the Dashboard.
4. Use **Sign Out** in the left navigation when you finish.

Your account must be active. If your account is inactive or your credentials are incorrect, contact the QUAMC administrator.

## Roles And Access

| Role | Main responsibility | Data visibility |
| --- | --- | --- |
| QUAMC Administrator | Launch cycles and manage system operations | All colleges, programs, cycles, activities, evidence, and reports |
| President | Institution-level oversight | All cycles and read-only information |
| Campus Director | Main-campus oversight | All cycles and read-only information |
| VPAA | Academic oversight | All cycles and read-only information |
| Dean | College oversight | Programs and cycles in the assigned college |
| Program Head | Program compliance management | The assigned program only |
| Internal Accreditor | Assigned-area review | Assigned areas and related activities only |

The server enforces these permissions even when a user tries to open a URL directly.

## Dashboard

The Dashboard shows accreditation cycles available to your role.

- **Green:** more than 30 days remain.
- **Amber:** 8 to 30 days remain.
- **Red:** fewer than 8 days remain or the deadline has passed.
- **Overdue for renewal:** the program validity date has expired.

Select a cycle card to open its areas, activities, review status, and available actions.

## Administrator: Launch A Cycle

1. Open **Administration** from the left navigation.
2. Select a program.
3. Enter the academic year.
4. Enter the date the SAR was received.
5. Select **Launch Cycle**.

The system calculates the compliance deadline one year after the SAR received date and automatically creates the ten AACCUP area assignments.

## Program Head: Manage Compliance Activities

1. Open your program's cycle from the Dashboard.
2. Expand an AACCUP area.
3. Select **New Activity**.
4. Enter the activity title, description, start date, and end date.
5. Save the activity as a draft.
6. Open the activity again when ready and submit it for review.

Program Heads can only manage activities in their assigned program. Activity dates cannot exceed the cycle compliance deadline.

### Activity States

| State | Meaning |
| --- | --- |
| Draft | Activity is being prepared by the Program Head |
| Submitted | Activity is waiting for Internal Accreditor review |
| Under review | An Internal Accreditor is reviewing the activity |
| Needs revision | The activity was returned with requested changes |
| Approved | The activity was approved |
| Completed | The activity is finished |

When an activity is returned for revision, update it and submit it again. Only draft and needs-revision activities can be edited by a Program Head.

## Program Head: Upload Evidence

1. Open the cycle and expand the relevant area.
2. Find the activity.
3. Select its evidence upload action.
4. Choose a file or drag one into the upload area.
5. Optionally enter a display name.
6. Select the upload button.

Accepted file types are PDF, JPEG, PNG, Word, Excel, and PowerPoint files. The maximum file size is 20 MB.

Evidence is stored privately and can only be downloaded by a user with access to the related cycle. The system records the authenticated uploader.

## Internal Accreditor: Review Activities

1. Open a cycle that contains an area assigned to you.
2. Expand the assigned area.
3. Select **IA Review**.
4. Review submitted activities.
5. Move an activity into review, approve it, or return it for revision.
6. Add the area-level decision and comments.
7. Save the review.

Internal Accreditors can only review areas assigned to their account. They cannot create new activities.

## Compliance Schedule

Program Heads and administrators can open **Schedule** from a cycle detail page.

The Gantt chart displays only approved and completed activities. Use the chart view controls to change the time scale. If no activities have reached an approved or completed state, the schedule is empty.

## Read-Only Oversight

Presidents, Campus Directors, VPAAs, and Deans use the Dashboard and cycle detail pages to monitor progress. Their role is read-only for compliance records. They should contact QUAMC for corrections, cycle launches, account changes, or assignments.

## Reports And Assignments

Report generation and Internal Accreditor assignment are protected backend capabilities. Dedicated frontend screens for these administrative actions are not currently included in the web interface. QUAMC administrators should use the available API or an approved administrative tool until those screens are added.

## Troubleshooting

### I cannot see a cycle

Your account may not be assigned to the relevant program, college, or IA area. Contact QUAMC.

### I cannot edit an activity

Only Program Heads assigned to the activity's program and QUAMC administrators can edit it. Activities that are submitted, under review, approved, or completed are not editable by Program Heads.

### My evidence upload fails

Check the file type and ensure the file is no larger than 20 MB. If the problem continues, confirm that you have access to the related program.

### The dashboard or review page does not load

Refresh the page once. If the problem continues, sign out and sign in again, then report the issue to QUAMC with the affected cycle and activity.

## Security Practices

- Do not share your password or JWT/session information.
- Do not use seeded development credentials in production.
- Sign out on shared computers.
- Upload only official accreditation evidence.
- Report incorrect access or unexpected data immediately to QUAMC.