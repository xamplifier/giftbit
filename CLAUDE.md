# Project Handbook — giftbit

Cold-boot for any Claude session working here. Written 2026-09-16 against
`master` @ `b117da9`.

**Read the Branches section before you write a line of code.** The branch this
handbook is on is *not* the branch production builds from, and that is deliberate.

## Purpose

`xamplifier/giftbit` is a thin PHP Composer library wrapping the
[Giftbit](https://www.giftbit.com/) Partner REST API (`papi/v1`) — digital gift
cards. It exposes six static-method facades (brands, campaigns, gifts, funds,
regions, embedded gifts) over Guzzle. There is no state, no storage, no CLI and
no HTTP surface of its own; it is a request builder plus a response unwrapper.

Its business role: **it is how Layline sends people gift cards.** It is consumed
by `api.vip.xamplifier.com` — the Laravel backend behind `vip.xamplifier.com`
and `admin.vip.xamplifier.com`, both of which had successful production deploys
on 2025-11-10. Calls made through this library move real money.

It is a **public** repository (`isPrivate: false`, verified via the GitHub API
2026-09-16). Anything committed here is world-readable, permanently. See
Landmine 9.

## Production status

**Load-bearing but unmaintained.** Last commit to `master` is 2019-11-27; last
commit anywhere is 2022-01-28. Not archived.

Evidence it is load-bearing, from `repo-triage-2026-09-03.md`:
`api.vip.xamplifier.com/composer.json` declares `"xamplifier/giftbit":
"dev-php8.0"` — a **branch ref, not a version pin**. Its build resolves this
repo live. See Landmine 1.

Evidence it is unmaintained: zero PRs have ever been opened against this repo
(`gh pr list --state all` returns empty, 2026-09-16); neither branch is
protected; the only CI that has ever run is Dependabot, and **all 11 of its runs
failed** (see Landmine 8).

*Not established:* whether `api.vip` is itself still serving traffic. Nobody in
this estate has DNS/HTTP/GoCD access, and `api.vip` deploys via GoCD rather than
Actions, so there is no deploy history to read. The check that would settle it is
an HTTP probe of the API host or GoCD pipeline history for `api.vip`.

## Branches — resolve this before you commit

| Branch | Tip | Date | Role |
|---|---|---|---|
| `master` | `b117da9` | 2019-11-27 | **GitHub default.** Where a fresh clone lands. Where this handbook lives. |
| `php8.0` | `1328e4f` | 2022-01-28 | **What production actually builds from** (`api.vip` pins `dev-php8.0`). |

Measured 2026-09-16 after `git fetch`: `master` is **0 ahead, 1 behind**
`php8.0`. `php8.0` is exactly `master` plus one commit, and that commit
(`1328e4f`) touches **only `composer.json` constraints** — `php ^7.0`→`^8.0`,
`guzzle ^6.3`→`^6.5.5`, `phpunit ^7.5`→`^9.5`. **Every `.php` file is
byte-identical on both branches** (`git diff master origin/php8.0` shows one
file). So every code claim in this handbook holds on both.

**The contradiction, stated plainly, and how to handle it:**

- A cold-booting session clones and lands on `master`. If the handbook is not on
  `master` it is invisible to the reader who needs it most.
- But a code change merged to `master` **does not reach production**. `php8.0`
  does not track `master`; it is a sibling that happens to contain it.

So:

- **Documentation-only changes → PR to `master`.** That is where this handbook
  was seeded, for discoverability.
- **Any change to `src/`, `composer.json` or `config.php` → PR to `php8.0`.**
  That is the only branch with a consumer. A `src/` fix that lands solely on
  `master` is dead on arrival — it will never be installed by anything.
- Because `master` is a strict ancestor of `php8.0`, a `master → php8.0` merge
  is conflict-free. **This handbook should be forwarded to `php8.0`** so both
  branches carry it; that was deliberately left as a follow-up rather than done
  as a drive-by push to the production-consumed branch. See LEDGER 2026-09-16.

**Open question:** the real fix is to make `php8.0` the GitHub default branch, so
that the branch production consumes is also the branch a clone lands on. That is
a repo-settings change with a blast radius outside this repo (it changes what
`api.vip` contributors see, and what any future `dev-master` consumer would get)
and is not a worker's call. Raised for a human.

**Pushing to `php8.0` does not itself deploy anything** — Composer resolves
`dev-php8.0` at *consumer build time*, not on push. But there is no version gate:
the next `composer update` in `api.vip` picks up whatever `php8.0`'s tip is at
that moment. Treat a `php8.0` merge as "armed," not "fired."

## Stack

- **PHP.** `composer.json` declares `^7.0` on `master`, `^8.0` on `php8.0`. The
  code itself uses only PHP 7.0-era features (`declare(strict_types=1)`,
  typed properties absent, `??` operator) and runs under PHP 8.3 with one
  deprecation — see Landmine 4.
- **`guzzlehttp/guzzle`** — the only runtime dependency. `^6.3` / `^6.5.5`.
- **`phpunit/phpunit`** + **`larapack/dd`** — dev only.
- **No `composer.lock`** (gitignored). Correct for a library: the consumer's lock
  governs what actually installs. It also means *this repo cannot tell you what
  version of Guzzle production is running.* Read `api.vip.xamplifier.com/composer.lock`
  for that.
- **StyleCI** (`.styleci.yml`, `preset: recommended`) — a hosted service; no
  evidence it is still wired up.

## Entry points

There is no `main`, no route table, no cron. The entry points are **static
methods**, all of which funnel into `Giftbit::execute()`.

| Class | File | Methods | Giftbit endpoint |
|---|---|---|---|
| `Giftbit` | `src/Giftbit.php` | `ping()` ⚠️ broken — Landmine 3 | `GET ping` |
| `Brands` | `src/Brands.php` | `getListBrands()`, `fetchBrand($brandCode)` | `GET brands`, `GET brands/{code}` |
| `Campaigns` | `src/Campaigns.php` | `createCampaign()` ⚠️ Landmine 5, `getCampaign($id)` | `POST campaign`, `GET campaign/{id}` |
| `Gifts` | `src/Gifts.php` | `getListGifts()` ⚠️ Landmine 6, `getGift($uuid)`, `resendGift($uuid)`, `deleteGift($uuid)` | `GET/PUT/DELETE gifts[/{uuid}]` |
| `Funds` | `src/Funds.php` | `getFunds()` | `GET funds` |
| `Regions` | `src/Regions.php` | `getListRegions()` | `GET regions` |
| `Embedded` | `src/Embedded.php` | `createEmbedded()` | `POST embedded` |

**The single chokepoint is `Giftbit::execute()` (`src/Giftbit.php:24-45`).** Every
subclass method constructs `new Giftbit` and calls it. Read those 22 lines first;
they contain three of the nine landmines below.

Return shape is uniform: `['success' => bool, 'data' => array]`
(`src/Giftbit.php:69-78`).

## External dependencies

| Service | Configured by | Where |
|---|---|---|
| Giftbit Partner API — testbed | global constant **`ENVIROMENT`** (sic) set to the string `'TEST_URL'` | `src/Giftbit.php:10,28` → `https://api-testbed.giftbit.com/papi/v1/` |
| Giftbit Partner API — **production** | global constant **`ENVIROMENT`** set to the string `'PROD_URL'` | `src/Giftbit.php:11,28` → `https://api.giftbit.com/papi/v1/` |
| Giftbit bearer token | global constant **`GIFTBIT_TOKEN`** | `src/Giftbit.php:32` |

**Both constants are defined by the consuming application, not by this repo.**
`grep -rn 'define(' src tests config.php` returns nothing. They must exist in
`api.vip.xamplifier.com` (e.g. a Laravel config/bootstrap file). This repo cannot
tell you where — that file is not readable from here.

## Runtime

Runs in-process inside the consumer's PHP request. No daemon, no container, no
deploy target of its own. "Deploying" this library means merging to `php8.0` and
letting `api.vip`'s next build resolve it.

## Coding conventions

- One class per Giftbit resource in `src/`, extending `Giftbit`, with a
  `protected const OBJECT_NAME` holding the URL path segment.
- **All public methods are `static`** and internally do `$gb = new Giftbit;` —
  note they instantiate the *base* class, not `static`/`self`. Harmless today
  because `execute()` is defined on the base and reads no subclass state, but it
  means `OBJECT_NAME` is always passed in explicitly rather than resolved
  polymorphically. Follow the existing pattern; don't "fix" it piecemeal.
- `declare(strict_types = 1);` at the top of every `src/` file. Tests do not have it.
- Return type `:array` on every public method. Docblocks on every method.
- Tests live in `tests/`, named `<Resource>Tests.php` (plural "Tests") — except
  `AuthenticationTest.php` (singular). Class names match filenames.
- `.gitignore` excludes `build`, `composer.lock`, `docs`, `vendor`, `coverage`.

## Landmines

**1. The production consumer depends on a branch ref, not a version.**
`api.vip.xamplifier.com` declares `"xamplifier/giftbit": "dev-php8.0"`. There is
no tag, no release, no semver gate — this repo has **zero git tags**. A commit
pushed to `php8.0` is picked up by `api.vip`'s next `composer update` with
nothing in between. Why it bites: you can break a production gift-card flow from
a repo that looks abandoned, without touching the consumer, and without any CI
anywhere noticing. This is a recurring pattern across the estate — `etl`,
`reviews` and `php-graph-sdk` have the same shape.

**2. `config.php` is dead code. Setting the token there does nothing.**
`src/Giftbit.php:27` does `$config = include('../config.php');` and then **never
uses `$config`**. Commit `635a55a` (2019-11-26, "changing configuration
constants") is the smoking gun: it replaced `'Bearer ' . $config['token']` with
`'Bearer ' . GIFTBIT_TOKEN` and left the `include` line behind. Why it bites: the
first thing a new contributor does is open `config.php`, see
`'token' => 'ENTER YOUR TOKEN'`, fill it in, and get 401s forever. The token comes
from the `GIFTBIT_TOKEN` global constant. Corollary: the include path `'../config.php'`
is relative to the *process CWD*, not to `src/` — verified, `include_path` is
`.:/usr/share/php`, so it resolves to the parent of wherever PHP was started. In a
vendored install it will not find the file. That is non-fatal (`include` warns and
returns `false`; verified by running it) **but it emits two E_WARNINGs into the
consumer's logs on every single API call.**

**3. `Giftbit::ping()` is broken and always has been.** `src/Giftbit.php:57`
calls `$gb->execute('ping', $data, "GET")` with three arguments;
`execute()` requires four. Verified by execution on PHP 8.3:
`ArgumentCountError: Too few arguments to function
Xamplifier\Giftbit\Giftbit::execute(), 3 passed ... and exactly 4 expected`.
Why it bites: `ping()` is the obvious "is my auth working?" smoke test, and
`tests/AuthenticationTest.php` — the one test that looks like a health check —
can never pass. Fix is one word (`, 'query'`), but it lands on `php8.0`, not here.

**4. `execute()` has a required parameter after an optional one.**
`src/Giftbit.php:24`: `execute(string $url, array $data = [], string $requestMethod, string $requestType)`.
PHP 8 emits `Deprecated: Optional parameter $data declared before required
parameter $requestType is implicitly treated as a required parameter` — verified
by running `php -d error_reporting=E_ALL -r 'require "src/Giftbit.php";'` on PHP
8.3.6. Why it bites: the `php8.0` branch bumped the *constraint* to `^8.0` and
fixed none of the actual PHP 8 issues. Reflection confirms all four params are
required (`getNumberOfRequiredParameters()` = 4), which is why Landmine 3 exists.

**5. `Campaigns::createCampaign()` throws away the array you pass it.**
`src/Campaigns.php:25-35` ignores the caller's `$data` structure and rebuilds it,
reading exactly five keys: `templateId`, `firstName`, `lastName`, `email`,
`amount`. Pass Giftbit's own field names (`gift_template`, `price_in_cents`,
`contacts`) and you get undefined-array-key warnings and a malformed POST. The
campaign `id` is hardcoded to `"xamplifier_" . uniqid()` (line 33) — that is what
commit `b117da9` "changing the id structure in the campaigns" did. Note
`tests/CampaignsTests.php:22` still passes an `"expiry"` key that the method
silently drops (commit `be23a5e` removed it).

**6. `Gifts::getListGifts()` sends all 21 filter parameters, empty-string-valued,
on every call.** `src/Gifts.php:26-49` builds the full filter array with
`?? ''` defaults and passes the lot as a query string. Why it bites: you cannot
tell from the call site which filters are actually in play, and any Giftbit-side
validation change on an empty-string filter breaks every caller at once. There is
a `@todo modulirize the filter array` on line 18 acknowledging this.

**7. The test suite is a destructive live-API integration suite wearing unit-test
clothing.** There are no mocks, no fixtures, no HTTP stubs — every test in
`tests/` performs a real call against whatever `ENVIROMENT` points at.
Specifically: `tests/CampaignsTests.php:26` **creates a campaign**;
`tests/GiftsTests.php:45` **deletes a gift**. If `ENVIROMENT` is `'PROD_URL'`
when you run `phpunit`, you spend real money and destroy real records. Also:
- `tests/FundsTests.php:15` contains a stray `dd($funds);` — `larapack/dd`'s `dd`
  **dies**, so this aborts the PHPUnit process, not just the test.
- `tests/BrandsTests.php` asserts on the literal brand `'nike'` and
  `tests/GiftsTests.php` on a hardcoded gift UUID from 2019 — both will fail on
  data drift regardless of code correctness.
- Test files hardcode a developer's real name and email address
  (`tests/CampaignsTests.php:18-21`). Not customer data, but don't propagate it.

**8. Dependabot is the only CI, it has never succeeded, and it cannot succeed.**
There is no `.github/` directory on either branch; Dependabot runs from
org/repo-level settings. All 11 Actions runs failed, most recently 2026-08-05,
all on `master` — **it never targets `php8.0`**, the branch production uses.
Meanwhile there are **10 open Dependabot security alerts** (2 high): nine against
`guzzlehttp/guzzle`, one against `phpunit/phpunit`. Why it cannot self-heal: every
Guzzle advisory's first patched version is in the **7.x** line (7.12.1 through
7.15.2), and both branches constrain Guzzle to `^6.x`. **No version satisfying the
declared constraint is patched for any of them.** Remediation requires a major
bump to Guzzle 7 plus whatever code changes that implies — not a Dependabot patch.
`.travis.yml` is also present and tests PHP 7.1/7.2/7.3; travis-ci.org was shut
down in 2021, and its `after_script` invokes `ocular` which is not in
`require-dev`. Treat `.travis.yml` as an artifact, not as CI.

**9. This repo is public.** Verified 2026-09-16 via the GitHub API. Anything
committed is world-readable and survives deletion in the git history. The estate
triage explicitly checked and cleared `config.php` — it holds the literal
placeholder `ENTER YOUR TOKEN`, not a real secret — and that must stay true. The
`GIFTBIT_TOKEN` design (constant supplied by the consumer) is what keeps it true;
do not "helpfully" wire the real token back into `config.php`.

**10. `composer.json` has three structural defects.** All verified by parsing it:
- `autoload.psr-4` maps `Xamplifier\Config\` → `config.php`, a **file**. PSR-4
  requires a directory, so this mapping can never resolve a class. Nothing uses
  that namespace (`grep -rn 'Xamplifier\\Config' src tests` → zero hits), so it
  is inert, but it will confuse you.
- `autoload-dev.psr-4` maps `Xamplifier\Giftbit\Test\` (singular) → `tests`, but
  every test file declares `namespace Xamplifier\Giftbit\Tests;` (**plural**).
  The dev autoloader therefore does not resolve any test class; PHPUnit finds them
  only via its own directory scan.
- `authors` is nested **inside** `autoload-dev` instead of at the top level, so
  Composer ignores it.
- The `description` reads *"Giftbit library for Google APIs"* — leftover from
  whatever template this was scaffolded from. It is not a Google APIs library.

**11. `README.md` is one line.** It says `# Giftbit's RESTFul API Library` and
nothing else. It is not wrong, just empty. Do not expect it to orient you.

## What's forbidden here

- Workers **do not** commit to `master`, `php8.0`, or any pre-existing branch.
  Fresh `layline-worker/<slug>` branch + a PR. See the Branches section for which
  base to target — it depends on what you changed.
- Workers **do not** run the test suite. It is a destructive live-API suite
  (Landmine 7). If you need to validate a change, add a mocked test; do not run
  the existing ones.
- Workers **do not** put a real credential in `config.php` or anywhere else in
  this repo. It is public (Landmine 9).
- Workers **do not** merge to `php8.0` without saying so explicitly in the PR —
  that branch is what a production consumer resolves (Landmine 1).
- Workers **do** run inside `layline-sbx`. If a host isn't on the baseline
  allowlist, surface a `context-request`; don't work around it.

## Related

- [`LEDGER.md`](./LEDGER.md) — outcome log
- [`RECIPES.md`](./RECIPES.md) — build / test commands, with verification tags
- Consumer: `api.vip.xamplifier.com` (Laravel; declares `dev-php8.0`) — not
  readable from this scope; see `repo-triage-2026-09-03.md` for the dependency edge.
- Upstream API docs: <https://www.giftbit.com/giftbitapi/> (not fetched from this
  sandbox — unverified URL, cited from `src/Giftbit.php`'s base URIs).
