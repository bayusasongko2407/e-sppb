# Domain Knowledge

## Core Domains

SPPB, Workflow, Asset.

## Supporting Domains

Organization, Security, Attachment, Notification, Reporting, Audit, Validation.

## Generic Domains

Configuration, Running Number, Logging, File Storage.

## Aggregate Roots

- SPPB: SPPB Header.
- Workflow: Workflow Instance.
- Asset: Asset.
- Organization: Plant.
- Security: User.

## Invariants

- SPPB number is unique.
- Workflow is created on submit.
- Approval cannot be skipped.
- Audit is mandatory.
- Database follows frozen design.

