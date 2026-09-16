# Recipes — giftbit

Copy-paste-literal commands. All run from the repo root unless noted. If a
command here fails, that's a P1 bug on this file.

Every command is tagged `[verified 2026-09-16]` (it was actually run in
`layline-sbx` on PHP 8.3.6) or `[unverified — …]` with what would confirm it.
**Nothing here was inferred and presented as fact.**

> ⚠️ **Before you run anything in the Test section, read
> [`CLAUDE.md`](./CLAUDE.md) Landmine 7.** The test suite makes real Giftbit API
> calls — it creates campaigns and deletes gifts. Against `PROD_URL` that spends
> real money.

## Environment as found in the sandbox

`[verified 2026-09-16]`

```bash
php --version        # PHP 8.3.6 (cli) (built: Sep  2 2026 12:56:02) (NTS)
command -v composer  # not installed in layline-sbx
```

**There is no Composer and no `vendor/` in this sandbox.** Everything below that
needs Composer is therefore unverified, and is marked as such. Installing
Composer needs egress to `getcomposer.org` / `packagist.org`, neither of which is
on the sandbox baseline allowlist — that would be a `context-request`, not a
workaround.

## Local setup

`[unverified — would be confirmed by running it on a host with Composer and network access]`

```bash
composer install
```

Then — and this is the part that is **not** in the README — the library reads two
**global constants that this repo does not define**. You must define them before
calling anything (`src/Giftbit.php:28` and `:32`):

```php
<?php
// In your bootstrap, BEFORE any Xamplifier\Giftbit call.
define('ENVIROMENT', 'TEST_URL');   // note the spelling: ENVIROMENT, not ENVIRONMENT
                                    // 'TEST_URL' -> https://api-testbed.giftbit.com/papi/v1/
                                    // 'PROD_URL' -> https://api.giftbit.com/papi/v1/   (real money)
define('GIFTBIT_TOKEN', getenv('GIFTBIT_TOKEN'));  // never hardcode; this repo is public
```

**Do not put the token in `config.php`.** That file is dead code — see
[`CLAUDE.md`](./CLAUDE.md) Landmine 2. Its `'token'` key has not been read by
anything since commit `635a55a` (2019-11-26).

`[verified 2026-09-16]` — what happens if you forget `ENVIROMENT`:

```bash
php -r 'class P { protected const TEST_URL = "x"; function go(){ return constant("self::".ENVIROMENT); } } (new P)->go();'
# PHP Fatal error: Uncaught Error: Undefined constant "ENVIROMENT"
```

On PHP 7 this was a warning that silently fell through to a bad URL; on PHP 8 it
is a hard `Error`. The PHP 8 behaviour is the better one — you find out immediately.

## Run (dev)

There is nothing to run. This is a library with no CLI, no server and no
executable entry point. Exercise it from a consuming app, or from a one-off
script:

`[unverified — needs `vendor/autoload.php`, i.e. a Composer install]`

```bash
cat > /tmp/gb-smoke.php <<'PHP'
<?php
require __DIR__ . '/vendor/autoload.php';
define('ENVIROMENT', 'TEST_URL');
define('GIFTBIT_TOKEN', getenv('GIFTBIT_TOKEN'));
var_dump(\Xamplifier\Giftbit\Regions::getListRegions());   // read-only, safe
PHP
php /tmp/gb-smoke.php
```

`Regions::getListRegions()` and `Brands::getListBrands()` are the two read-only,
non-destructive calls — prefer them for smoke-testing. **Do not** use
`Giftbit::ping()` for this; it is broken (Landmine 3).

## Syntax check / static verification (no Composer needed)

`[verified 2026-09-16]` — this is the most useful thing you *can* actually run
in the sandbox, and it caught two real bugs.

```bash
# Lint every tracked PHP file. All 15 pass on PHP 8.3.6.
for f in $(git ls-files '*.php'); do printf '%-30s ' "$f"; php -l "$f"; done
```

```bash
# Surface the PHP 8 deprecation in Giftbit::execute() (CLAUDE.md Landmine 4).
php -d error_reporting=E_ALL -d display_errors=1 -r 'require "src/Giftbit.php";'
# Deprecated: Optional parameter $data declared before required parameter
# $requestType is implicitly treated as a required parameter in src/Giftbit.php on line 24
```

```bash
# Prove Giftbit::ping() is broken without any network or Composer
# (CLAUDE.md Landmine 3).
php -d error_reporting=0 -r '
require "src/Giftbit.php";
try { \Xamplifier\Giftbit\Giftbit::ping(); }
catch (\Throwable $e) { echo get_class($e).": ".$e->getMessage()."\n"; }'
# ArgumentCountError: Too few arguments to function
# Xamplifier\Giftbit\Giftbit::execute(), 3 passed ... and exactly 4 expected
```

```bash
# Check composer.json structure (CLAUDE.md Landmine 10). Valid JSON, wrong shape.
php -r '$j=json_decode(file_get_contents("composer.json"),true);
echo json_last_error_msg(), "\n";
echo "autoload-dev keys: ", implode(", ", array_keys($j["autoload-dev"])), "\n";'
# No error
# autoload-dev keys: psr-4, authors        <- "authors" does not belong here
```

## Test

> ⚠️ **These tests hit the live Giftbit API. They create campaigns and delete
> gifts. Read [`CLAUDE.md`](./CLAUDE.md) Landmine 7 first.** Never run them with
> `ENVIROMENT` set to `'PROD_URL'`.

`[unverified — Composer is not installed in `layline-sbx`; would be confirmed by
`composer install && vendor/bin/phpunit` on a host with network access and a
testbed token]`

```bash
composer install
export GIFTBIT_TOKEN='<testbed token — see the api.vip.xamplifier.com config, not this repo>'
vendor/bin/phpunit                       # uses phpunit.xml.dist
vendor/bin/phpunit --filter RegionsTests # single suite; Regions is read-only
```

Known problems with the suite, all verified by reading `tests/` on 2026-09-16 —
expect these, they are not your change breaking:

| File | Problem |
|---|---|
| `tests/FundsTests.php:15` | stray `dd($funds);` — `larapack/dd` **dies**, aborting the whole PHPUnit process |
| `tests/AuthenticationTest.php:13` | calls `Giftbit::ping()`, which throws `ArgumentCountError`. Cannot pass. |
| `tests/CampaignsTests.php:26` | **creates a real campaign** |
| `tests/GiftsTests.php:45` | **deletes a real gift** (hardcoded 2019 UUID) |
| `tests/BrandsTests.php:38,47` | asserts on the live `'nike'` brand — fails on upstream data drift |
| all of `tests/` | namespace is `Xamplifier\Giftbit\Tests` but `composer.json` `autoload-dev` maps `Xamplifier\Giftbit\Test\` (singular). PHPUnit's directory scan finds them anyway. |

`phpunit.xml.dist` sets `bootstrap="vendor/autoload.php"` and writes coverage +
TAP + JUnit into `build/` (gitignored). It also sets `DB_CONNECTION=testing`,
which nothing in this repo reads — leftover from a Laravel scaffold.

## Lint / format

`[unverified — StyleCI is a hosted service; no local runner is configured]`

`.styleci.yml` declares `preset: recommended`. There is no local lint command and
no `php-cs-fixer` / `phpcs` in `require-dev`. Whether the StyleCI integration is
still connected to this repo cannot be checked from here — the check that would
settle it is the repo's GitHub App/integration settings.

The best local substitute is `php -l` (see the syntax-check section above).

## Build (production artifact)

**There is none.** This is a source-only Composer library. There is no compile
step, no bundler, no artifact. "Building" it is what the *consumer's*
`composer install` does.

`.gitattributes` marks `/tests`, `/phpunit.xml.dist`, `/.travis.yml`,
`/.gitignore`, `/.gitattributes`, `/.scrutinizer.yml` and `/.editorconfig` as
`export-ignore`, so they are stripped from a `git archive` dist tarball. Note
`config.php` is **not** export-ignored — it ships to consumers. (The last two
paths listed don't exist in the tree.)

## Deploy

**There is no deploy command, and you should not try to invent one.**

This library is "deployed" by a consumer resolving it. `api.vip.xamplifier.com`
declares `"xamplifier/giftbit": "dev-php8.0"`, so:

1. A merge to the **`php8.0`** branch changes what the next `api.vip` build
   installs. There is no version pin and no tag in between — this repo has zero
   git tags.
2. Nothing fires on push. The pickup happens at `api.vip`'s next
   `composer update` / build.
3. `api.vip` deploys via **GoCD**, which is outside this scope, so its deploy
   history is not visible from here and this file cannot tell you when a giftbit
   change last actually shipped. The check that would settle it: GoCD pipeline
   history for `api.vip.xamplifier.com`, or that repo's `composer.lock`.

A merge to **`master`** deploys nowhere. See [`CLAUDE.md`](./CLAUDE.md) Branches.

## Common workflows

**Confirm which branch your change needs to be on** `[verified 2026-09-16]`:

```bash
git fetch origin
git rev-list --left-right --count master...origin/php8.0   # -> "0	1" as of 2026-09-16
git diff master origin/php8.0 --stat                        # -> composer.json only
```

If that second command ever shows more than `composer.json`, the two branches
have genuinely diverged and every code claim in `CLAUDE.md` needs re-checking
against `php8.0` specifically.

**Fetch inside `layline-sbx`** `[verified 2026-09-16]` — the `origin` remote is
`git@github.com:`, and there is no `~/.ssh` in the sandbox, so SSH fails. Use
HTTPS with the `gh` token as a credential helper:

```bash
git -c credential.helper='!f(){ echo username=x-access-token; echo "password=$(gh auth token)"; };f' \
    fetch https://github.com/xamplifier/giftbit.git '+refs/heads/*:refs/remotes/origin/*' --prune
```

**See the open security alerts** `[verified 2026-09-16]`:

```bash
gh api repos/xamplifier/giftbit/dependabot/alerts \
  --jq '.[] | select(.state=="open") | "\(.security_advisory.severity)\t\(.dependency.package.name)\tpatched=\(.security_vulnerability.first_patched_version.identifier)"'
# 10 open: 9 x guzzlehttp/guzzle (patched 7.12.1-7.15.2), 1 x phpunit/phpunit
```

## When things break

- **`Error: Undefined constant "ENVIROMENT"`** — you did not define the global
  constant, or you spelled it `ENVIRONMENT`. The code spells it **`ENVIROMENT`**
  (`src/Giftbit.php:28`). `[verified 2026-09-16]`

- **Every call returns 401 even though you set the token in `config.php`** —
  `config.php` is dead code. Define the `GIFTBIT_TOKEN` global constant instead.
  `CLAUDE.md` Landmine 2. `[verified 2026-09-16 — the `$config` variable assigned
  at `src/Giftbit.php:27` is never read anywhere in the file]`

- **`Warning: include(../config.php): Failed to open stream`** in the consumer's
  logs, twice per API call — expected and harmless. `src/Giftbit.php:27` includes
  a path relative to the *process CWD*, which almost never resolves in a vendored
  install. `include` warns and returns `false`; `$config` is unused, so nothing
  downstream breaks. `[verified 2026-09-16 — reproduced the warning pair and the
  `bool(false)` return]`

- **`ArgumentCountError ... Giftbit::execute(), 3 passed`** — you called
  `Giftbit::ping()`. It has been broken since 2019. `CLAUDE.md` Landmine 3.

- **PHPUnit exits partway with no failure summary** — `tests/FundsTests.php:15`
  has a `dd()` in it, which kills the process. Run with
  `--filter` to skip that file.

- **`Undefined array key "templateId"` from `Campaigns::createCampaign()`** —
  you passed Giftbit's API field names. That method wants
  `templateId` / `firstName` / `lastName` / `email` / `amount` and ignores
  everything else. `CLAUDE.md` Landmine 5.

- **A Dependabot run failed** — all 11 of them have, since 2026-01-28. Not new.
  It targets `master` only and cannot resolve the Guzzle advisories inside the
  declared `^6.x` constraint. `CLAUDE.md` Landmine 8.
