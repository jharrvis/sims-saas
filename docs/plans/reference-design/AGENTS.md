# Instructions for AI Agents

This folder is the authoritative SIMS UI reference bundle.

Before implementing or reviewing any PWA, authentication, dashboard, loading, chart, or responsive UI work:

1. Read `../PWA-THEME-IMPLEMENTATION-PLAN.md`.
2. Read `README.md` and `DESIGN-SYSTEM.md` in this folder.
3. Inspect the relevant files under `source/` and the matching baseline under `previews/`.
4. Port the design into the existing Laravel/Livewire architecture; do not replace server-side authentication, authorization, tenancy, validation, or audit logic with demo JavaScript.
5. Preserve the verified login behavior: one column below 900px and two columns at 900px and above.
6. Test 390×844, 768×1024, and 1920×1080 before completion.

The files in `source/` are reference snapshots, not production runtime files. Production changes belong in the normal application directories.
