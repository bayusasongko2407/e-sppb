# Codex Knowledge Index

Use this index after `AGENTS.md`. The `/docs` directory remains the source of truth; `.codex` files are operational guides for agents.

When resuming an interrupted session, read `memory/continuation-log.md` immediately after this index.

## Task Routing

| Task | Load |
|---|---|
| Project orientation | `01_PROJECT.md`, `12_PROMPT_GUIDE.md` |
| Architecture/design | `02_ARCHITECTURE.md`, `13_CHECKLIST.md` |
| Business rules | `03_BUSINESS_RULES.md`, then `/docs/03_BUSINESS_REQUIREMENT.md`, `/docs/04_FUNCTIONAL_REQUIREMENT.md` if needed |
| Database/schema | `04_DATABASE_RULES.md`, then `/docs/08_DATABASE_MASTER_PLAN.md`, `docs/Old Blueprint/*.yaml` |
| Laravel implementation | `05_LARAVEL_GUIDE.md`, `15_CODE_STYLE.md`, relevant business/database guides |
| Filament UI | `06_FILAMENT_GUIDE.md`, `08_UI_GUIDE.md`, relevant service guide |
| Design/UI system | `.design/DESIGN_SYSTEM.md`, `.design/UX_GUIDELINE.md`, `.design/COMPONENT_LIBRARY.md`, `.design/PAGE_TEMPLATE.md`, `.design/DESIGN_CHECKLIST.md` |
| REST API | `07_API_GUIDE.md`, `09_SECURITY_GUIDE.md`, relevant services |
| Security | `09_SECURITY_GUIDE.md` |
| Testing | `10_TESTING_GUIDE.md` |
| Git/PR/release | `11_GIT_WORKFLOW.md`, `19_RELEASE_GUIDE.md` |
| AI behavior/prompting | `12_PROMPT_GUIDE.md`, `14_AI_RULES.md` |
| Deployment/ops | `16_DEPLOYMENT.md`, `18_DEBUG_GUIDE.md`, `20_TROUBLESHOOTING.md` |
| Performance | `17_PERFORMANCE.md` |

## Source Document Map

- Governance: `/docs/00_DOCUMENT_INDEX.md` to `/docs/05_NON_FUNCTIONAL_REQUIREMENT.md`.
- Architecture: `/docs/06_SYSTEM_ARCHITECTURE.md` to `/docs/12_EVENT_CATALOG.md`.
- Development standards: `/docs/13_DEVELOPMENT_ROADMAP.md` to `/docs/19_GIT_WORKFLOW.md`.
- Operations: `/docs/20_DEPLOYMENT_PLAN.md` to `/docs/23_BACKUP_DISASTER_RECOVERY.md`.
- Quality: `/docs/24_TESTING_STRATEGY.md` to `/docs/28_PROJECT_CHECKLIST.md`.
- Detailed blueprint: `/docs/Old Blueprint/*.yaml`.
- Enterprise design system: `.design/*.md`.

## Non-Negotiable Rules

- Documentation first.
- Database schema frozen.
- Business logic frozen.
- Service Layer first.
- API first.
- Security by design.
- Testability and observability are required.
- Every UI must follow `.design/`.
- Never delete documentation.
- Never invent business rules.
