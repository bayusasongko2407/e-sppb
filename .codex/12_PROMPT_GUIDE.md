# Prompt Guide

## Implementation Prompt Minimum

Include:

- Goal.
- Relevant docs.
- Module.
- Database impact.
- API impact.
- Flutter impact.
- Security impact.
- Testing requirements.

## Agent Response Discipline

- Think before coding.
- Understand architecture first.
- Understand business rules first.
- Understand workflows first.
- Implement only after context is sufficient.
- Load only relevant documents.
- Do not summarize `/docs` as a replacement for source truth.

## Prompt Template

```text
Goal:
Module:
Phase:
Reference docs:
Business rules:
Database impact:
API impact:
Flutter impact:
Security impact:
Testing:
Constraints:
```

## Stop Conditions

Stop and ask for approval when:

- Frozen database changes are requested ambiguously.
- Frozen business rules would change.
- Workflow lifecycle/state changes are requested.
- Existing credentials/configuration would be overwritten.

