# Business Rules

## Frozen Governance

- Do not change frozen business logic without approval.
- Do not change frozen workflow without ADR.
- Do not remove audit trail.
- Do not move business logic to UI.
- Every implementation must trace to Business Requirement -> Functional Requirement -> Architecture -> Service Layer -> UI/API -> Testing.

## Core Business Process

Requester -> Create Draft -> Submit -> Workflow -> Approval -> Validation -> Completed.

Workflow lifecycle:

Draft -> Submit -> Generate Workflow -> Waiting Approval -> Approved -> Executed -> Closed.

Terminal states: Rejected, Cancelled.

## SPPB Rules

- SPPB document number is generated automatically.
- SPPB document number must be unique.
- Draft SPPB can be created by requester.
- SPPB Header and Detail are not deleted through normal CRUD.
- Submit must generate workflow.
- Submit must run in a database transaction.
- Total items, total quantity, and attachment count must remain consistent with details/attachments.
- Revision increments revision tracking and preserves history.

## Workflow Rules

- Workflow follows a configured template.
- Every step has an approver.
- Step cannot be skipped unless explicitly represented by workflow logic/status.
- Next step activates only after previous step is complete.
- Final approver changes status to Approved.
- Reject requires a reason and closes workflow as Rejected.
- Revision returns work to requester, then resubmission restarts workflow.
- Delegation is valid only while active and when primary approver is unavailable.
- Delegation still audits the acting delegated user.
- SLA is per workflow step, with reminders before due date and escalation after breach.
- Concurrent approval must be handled safely.

## Approval/Audit Rules

Every important action records:

- User.
- Time.
- Old status.
- New status.
- Notes/reason.

Approval history is mandatory. Audit trail is mandatory for login/logout, approvals, important data changes, security errors, workflow changes, and document validation.

## Functional Requirements

- Login using Email or NIK.
- Validate active user status.
- Manage master plant, department, asset/item, and related organization data.
- Create draft SPPB.
- Submit SPPB.
- Generate workflow approval.
- Approve, reject, and request revision.
- Upload attachment.
- Keep approval history.
- Provide executive dashboard.
- Export PDF/Excel where required.
- Provide REST API for all processes.

## Role Access Rules

- Admin manages master data.
- Requester creates SPPB.
- BAT and Manager approve.
- Warehouse views approved documents.
- All visibility/actions must be enforced by Policy/Role/Permission, not only UI hiding.

