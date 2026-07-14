# UI Guide

## UI Principles

Use enterprise consistency, minimal clicks, accessibility, responsive layout, reusable components, and clear status presentation.

## Terminology

Use domain terms consistently:

- SPPB.
- Draft.
- Submit.
- Approval.
- Revision.
- Reject.
- Approved.
- Closed.
- Attachment.
- Validation.
- Workflow.

## Required UX

- Status badges for workflow/SPPB state.
- Timeline for approval/status history where relevant.
- Attachments shown separately from main SPPB data.
- Dashboard shows KPI, pending approvals, recent activity, charts, quick actions.
- Large tables must have search/filter/sort/pagination.

## Guardrails

- Do not encode business rules only in UI state.
- Do not hide unauthorized actions without also enforcing policy.
- Do not perform workflow transitions directly from UI code.

