# E-SPPB Enterprise Design System

## Purpose

This design system is mandatory for every E-SPPB UI: page, widget, dashboard, table, form, mobile view, component, and report. The application must feel like a modern enterprise ERP, not a set of generic CRUD screens.

## Design Identity

E-SPPB uses a calm, work-focused enterprise interface optimized for repeated daily use, approval workflows, status visibility, and dense operational information. Inspiration may come from modern ERP usability patterns such as Odoo, ERPNext, SAP Fiori, Microsoft Fluent, IBM Carbon, and Material Design 3, but never copy proprietary visual assets, branding, icons, layouts, or colors.

## Mandatory Loading Before UI Work

Before creating any page, review:

- `DESIGN_SYSTEM.md`
- `UX_GUIDELINE.md`
- `COMPONENT_LIBRARY.md`
- `PAGE_TEMPLATE.md`
- `DESIGN_CHECKLIST.md`

## Non-Negotiable Rules

- Never hardcode colors.
- Never hardcode spacing.
- Always use design tokens.
- Reuse components.
- Never duplicate UI patterns.
- Keep spacing, typography, color, state, and interaction consistent.
- Every action must provide meaningful feedback.
- Every page must support the user's workflow, not only data maintenance.

## Primary UI Goals

- Reduce clicks.
- Reduce scrolling.
- Reduce typing.
- Keep user context.
- Prevent mistakes.
- Surface workflow status clearly.
- Make frequent tasks fast.
- Preserve accessibility and keyboard use.

## Enterprise Page Structure

Every functional page should use:

1. Context header with title, breadcrumb, primary action, and status where relevant.
2. Task or data area optimized for the workflow.
3. Supporting panel for filters, history, summary, or actions when needed.
4. Empty, loading, error, and permission states.

