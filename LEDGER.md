# Ledger — giftbit

Outcome log. Newest entries at the top. Append-only — never rewrite a past
entry; correct one by adding a new entry that supersedes it and cross-links the
superseded entry's date.

```
## YYYY-MM-DD — Short title (author)
- **What changed:** files, symbols, behavior.
- **Why:** trigger — bug, feature, refactor, migration.
- **Lessons:** what you'd tell the next person who touches this area.
- **References:** PR number, ticket, related LEDGER entries.
```

---

## 2026-09-16 — Handbook bootstrapped (worker hb-giftbit-01)

- **What changed:** added `CLAUDE.md`, `RECIPES.md` and this `LEDGER.md` at the
  repo root, off the `layline-orchestrator` handbook template. **Documentation
  only — no `src/`, `tests/`, `composer.json` or `config.php` file was touched.**
  The repo had none of the three; a cold-booting session had a one-line
  `README.md` and nothing else.
- **Why:** the fleet-wide handbook project. This repo looks abandoned (last code
  commit 2019-11-27) but is a declared Composer dependency of
  `api.vip.xamplifier.com` via the branch ref `dev-php8.0`, so it is load-bearing
  and needed a handbook before anyone is dispatched into it.
- **Base branch:** PR targets **`master`**, the GitHub default — that is where a
  fresh clone lands, so it is the only place the handbook is discoverable. This
  is *deliberately not* where code changes belong: production builds from
  `php8.0`. The contradiction is resolved in `CLAUDE.md` § Branches rather than
  buried here, because that is where the reader is standing when the instruction
  matters. **Follow-up:** forward this handbook to `php8.0` (a conflict-free
  merge — `master` is a strict ancestor). Deliberately not done as a drive-by
  push to the production-consumed branch.
- **Lessons — what was misleading during the survey:**
  - **`config.php` is the trap this repo is built around.** It looks like the
    configuration entry point and is named like one. It is dead code: commit
    `635a55a` (2019-11-26) moved the token to a `GIFTBIT_TOKEN` global constant
    and left the `include` behind at `src/Giftbit.php:27`, where `$config` is
    assigned and never read. The 2026-09-03 estate triage correctly cleared it as
    a placeholder, but "not a secret" and "not used" are different facts and only
    the first was on record.
  - **The `php8.0` branch is a constraint bump, not a port.** Its one commit
    changes `composer.json` only — `php ^7.0`→`^8.0` and friends. Every `.php`
    file is byte-identical to `master`. The actual PHP 8 problems
    (required-param-after-optional at `src/Giftbit.php:24`) were not fixed. Don't
    read the branch name as "PHP 8 compatible."
  - **Two bugs are provable with zero infrastructure.** `Giftbit::ping()` throws
    `ArgumentCountError` (3 args passed to a 4-required method), and `execute()`
    emits a PHP 8 deprecation — both reproduced with a bare `php -r` one-liner, no
    Composer, no `vendor/`, no network. Those one-liners are in `RECIPES.md`.
    Reach for `php -l` and reflection before assuming a library needs a full
    install to inspect.
  - **`git log` told the story the code didn't.** `635a55a`'s diff is what proved
    the `config.php` deadness rather than merely suggesting it, and `b117da9`
    explains the `"xamplifier_" . uniqid()` campaign id. On an eight-commit repo,
    reading every diff is cheap and was the highest-yield hour of the survey.
  - **Dependabot's failure is structural, not flaky.** All 11 runs failed; the
    open alerts all have first-patched versions in Guzzle **7.x** while both
    branches constrain `^6.x`, so no satisfiable version is patched. It also only
    ever targets `master`, never the branch production consumes.
  - **`README.md`, `.travis.yml` and the `composer.json` description are all
    misleading in the same direction** — they describe a maintained open-source
    library. The description literally says "Giftbit library for **Google APIs**."
    Scaffold residue; do not orient off any of them.
  - **SSH does not work inside `layline-sbx`** (no `~/.ssh`), and `origin` is an
    SSH remote. HTTPS with the `gh` token as an inline credential helper works for
    both fetch and push — exact command in `RECIPES.md` § Common workflows.
- **References:** handbook template at
  `_handbook-mirror/_templates/`; dependency edge from
  `repo-triage-2026-09-03.md` (giftbit row, and the `api.vip.xamplifier.com` row).
  Open security alerts and the `php8.0` default-branch question are raised for a
  human in the PR body.
