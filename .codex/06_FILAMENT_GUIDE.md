# Filament Guide

## Version

Use Filament v5.

## Resource Rules

- Resource/Page/Form/Table/Infolist/Action are presentation concerns.
- Every business action calls a Service.
- No complex query/business rule in Resource or Blade.
- Visibility and authorization use Policies and Role/Permission.

## Forms

- Use Sections for grouped data.
- Use responsive Grid.
- Placeholders must help data entry.
- Readonly/disabled states follow business rules.
- Validation is enforced in Form Request/Service, not only UI.

## Tables

- Searchable, sortable, filterable.
- Pagination/lazy loading for large data.
- Exportable where required.
- Bulk actions only when business-safe.
- Eager load required relationships.

## Actions

Actions such as Submit, Approve, Reject, Revision, Cancel, Validate, and Export must call services and surface business errors cleanly.

## Dashboard

Minimum dashboard domains:

- KPI.
- My approvals.
- Recent activity.
- Charts.
- Quick actions.

## Navigation

Use consistent grouping: Dashboard, Master Data, SPPB, Workflow, Monitoring, Laporan, Pengaturan.

