# Task Routing Decision Tree

1. Is the task Phase 0?
   - Yes: use `playbooks/phase-0.md`; do not create Laravel app code.
   - No: continue.
2. Is it business behavior?
   - Load `.codex/03_BUSINESS_RULES.md` and relevant `/docs`.
3. Is it database/schema?
   - Load `.codex/04_DATABASE_RULES.md` and blueprint YAML.
4. Is it Laravel backend?
   - Load `.codex/05_LARAVEL_GUIDE.md`, `15_CODE_STYLE.md`, and related business/database guides.
5. Is it Filament?
   - Load `.codex/06_FILAMENT_GUIDE.md`, `08_UI_GUIDE.md`.
6. Is it API?
   - Load `.codex/07_API_GUIDE.md`, `09_SECURITY_GUIDE.md`.
7. Is it deployment/debug/release?
   - Load `16_DEPLOYMENT.md`, `18_DEBUG_GUIDE.md`, `19_RELEASE_GUIDE.md`, or `20_TROUBLESHOOTING.md`.

