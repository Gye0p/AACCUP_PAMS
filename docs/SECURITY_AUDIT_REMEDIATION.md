# Security Audit Remediation

This record summarizes the implementation and security work completed against the AACCUP PAMS development walkthrough.

## Authorization And Data Scope

- Removed self-service user PATCH access so users cannot change their own role, program, or college assignments.
- Added an authenticated user checker that rejects inactive accounts during password and JWT authentication.
- Enforced Program Head ownership for activity and evidence writes.
- Enforced Internal Accreditor area assignment checks for activity review transitions.
- Restricted workflow transitions by role: Program Heads submit, assigned Internal Accreditors review, and administrators retain full control.
- Added role-aware scope checks to dashboard, Gantt, PDF report, and evidence download controllers.
- Added program, college, and IA-assignment scopes for monitoring report collections.
- Restricted direct monitoring report creation to QUAMC administrators.
- Restricted API CORS responses to the configured development origins.

## Evidence And Audit Trail

- Moved evidence storage from the public web root to `backend/var/uploads/evidence`.
- Added an authenticated, scope-checked evidence download endpoint.
- Record the authenticated uploader during Doctrine persistence.

## Frontend Workflow Fixes

- Activity edit pages now load and hydrate existing activity data.
- Activity edits preserve the existing area assignment.
- Removed the Internal Accreditor "New Activity" action that the backend rejected.
- Prevented IA review form state from resetting while asynchronous data loads.

## Dependency And Platform Upgrades

- Upgraded API Platform from `3.4.17` to `4.3.21` to resolve the reported security advisories.
- Removed the API Platform 3-only `keep_legacy_inflector` option.
- Added Symfony Form `6.4.47`, required by the configured Vich Uploader services.
- Updated the Composer lockfile and restored successful Symfony container compilation.

## Validation

- `composer validate --strict`
- `composer audit --abandoned=ignore`: no security vulnerability advisories
- Symfony container lint
- Symfony YAML lint
- PHP syntax validation
- OpenAPI export
- Frontend production build
- Git whitespace validation

## Remaining Maintenance Note

Composer reports `doctrine/cache` as abandoned. It has no active advisory in the current audit output, but it should be removed or replaced when the dependency tree permits.