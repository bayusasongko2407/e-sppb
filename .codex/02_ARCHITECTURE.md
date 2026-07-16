# Architecture Guide

## Architecture Style

Use Clean Architecture, SOLID, modular boundaries, REST API first, Service Layer first, security by design, testability, scalability, and observability.

## Logical Flow

Users -> Browser/Flutter -> Presentation Layer (Filament/REST API) -> Application Layer (Actions/Services) -> Domain Layer (Business Rules) -> Infrastructure Layer (Repository/Storage/Queue) -> MariaDB/Redis/Filesystem.

## Layer Responsibilities

- Presentation: Filament UI and API controllers/resources only.
- Application: use-case orchestration through Actions/Services.
- Domain: business rules and invariants.
- Infrastructure: database, queue, storage, external services.
- Persistence: MariaDB.

## Request Lifecycle

Request -> Validation -> Policy -> Service Layer -> Transaction -> Event -> Response.

## Dependency Rules

- Presentation depends on Application.
- Application depends on Domain.
- Infrastructure implements Domain contracts.
- No reverse dependencies.
- No circular module dependencies.
- No Resource-to-Resource dependency.

## Modules

Organization, Security, Asset, Workflow, SPPB, Attachment, Notification, Reporting, Audit, Validation, API.

## Implementation Order

Organization -> Security -> Asset -> Workflow -> SPPB -> Attachment -> Validation -> Notification -> Audit -> Reporting -> API.

## Service Catalog

- `AuthenticationService`: login/session.
- `UserService`: user management.
- `OrganizationService`: company, plant, department, unit, position, location.
- `AssetService`: item and asset management.
- `WorkflowService`: workflow generation.
- `ApprovalService`: approval process.
- `SppbService`: SPPB lifecycle.
- `AttachmentService`: file management.
- `ValidationService`: document validation.
- `NotificationService`: notification delivery.
- `ReportingService`: dashboard and reports.
- `AuditService`: audit trail.
- `RunningNumberService`: document numbering.

## Events

Business-driven events include `SppbCreated`, `SppbSubmitted`, `WorkflowGenerated`, `ApprovalRequested`, `ApprovalApproved`, `ApprovalRejected`, `RevisionRequested`, `AttachmentUploaded`, `NotificationQueued`, `DocumentValidated`, and `AuditLogged`.

Listeners handle side effects only. Main domain logic stays in services.

