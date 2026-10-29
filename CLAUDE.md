# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

eszleles.hu — "VCSE Észlelések", a Hungarian astronomy observation-sharing site. Built on the Yii 2 Basic Project Template (PHP, Bootstrap 4, PostgreSQL). All user-facing text, URL slugs, validation messages and flash messages are in Hungarian; keep new UI strings in Hungarian.

## Commands

```bash
composer install                      # install dependencies (vendor/ is gitignored)
docker-compose up -d                  # local stack: php/apache (port 80), postgres, memcached
php yii migrate                       # apply DB migrations (migrations/)
php yii migrate/create <name>         # new migration
php yii seed                          # import seed/megfigyelesek.csv as deepsky observations
php yii sitemap                       # generate sitemap
php yii starters                      # one-off: emails every user the launch announcement — do not run casually

vendor/bin/codecept run               # all test suites
vendor/bin/codecept run unit          # one suite
vendor/bin/codecept run unit models/UserTest           # one test file
vendor/bin/codecept run unit models/UserTest:testFindUserById  # one test method
```

Tests are the stock Yii template tests (login/contact/user) and `config/test_db.php` still points to a MySQL `yii2basic_test` DB, so they are not maintained for this app's PostgreSQL schema. No linter is configured.

## Local config (gitignored, required)

- `config/db_local.php` — merged over `config/db.php` in `config/web.php`; template in `sample/db_local.php`.
- `config/smtp.php` — mailer transport, required by both web and console config; template in `sample/smtp.php`.

Note the console config (`config/console.php`) uses `db.php` only, not `db_local.php`. Dev home URL is `http://obs.test` (`YII_ENV_DEV`); debug and gii modules are enabled in dev.

## Architecture

**Observation types share one table.** Every observation (deepsky, moon, planet, meteor, landscape, comet, sun, eclipse) is a row in `observations`, discriminated by the `type` column (`Observe::TYPE_*` constants, Hungarian labels in `Observe::getAllTypes()`). `models/Observe.php` holds the union of all type-specific columns (`moon_phase`, `meteor_membership`, `coverage`, …). Adding a type or type-specific field touches all of these:

- `models/Observe.php` — constant, `getAllTypes()`, rules/labels for new columns, plus a migration.
- `models/observations/<Type>.php` — thin subclass of `Observe` that only overrides `getVisibleAttributes()` (which fields show on the detail view).
- `models/forms/<Type>Form.php` — extends `AbstractObserveForm`; its `register()` builds a plain `Observe`, copies fields over, saves it and calls `uploadImage()` in a transaction. Updates bypass the form and load/save the `Observe` AR directly.
- `controllers/<Type>Controller.php` — near-identical CRUD controllers (duplicated per type). `index` filters `ObserveSearch` by type; `view`/`create`/`update` render the shared `views/observe/*` templates (`//observe/view`, etc.); only `index` and `_form` live in `views/<type>/`.
- `config/url_rules.php` — Hungarian slug routes per type (`hold`, `melyeg`, `bolygo`, `ustokos`, `nap`, `fogyatkozas`, `asztrotajkep`, `meteor`), each with `/<id>`, `/feltoltes` (create), `/modositas` (update).

`Observe::getViewUrl()` routes by `"/{type}/view"`, so the controller ID must equal the type constant's value.

**Query classes.** ActiveRecords return custom query classes (`ObservesQuery`, `UsersQuery`, …) with chainable scopes like `ofId()`, `ofUser()`.

**Images.** `AbstractObserveForm::uploadImage()` writes the original to `web/uploads/{id}.{ext}` and a thumbnail to `web/uploads/thumbnails/` (both gitignored), creating two `Image` rows distinguished by `size` (`SIZE_ORIGINAL` / `SIZE_THUMBNAIL`); `Observe::getImage()`/`getThumbnail()` filter on that. Image annotations/tags (`ImageTag`, `TagController`) use Annotorious (`web/annotorius`, `web/js/annotate*.js`, `image-tag*.js`).

**Access control.** Controllers use `AccessControl`: create/update require login; delete requires `User::isAdmin()` (the `is_admin` column). `AdminController` is the admin panel.

**Helpers.** `components/Flash` (flash messages), `components/Email` (templated mail from `mail/`), `components/Data` (deepsky object-type code lists), `components/Helper` (accent stripping for URLs).

**Frontend assets.** Bootstrap 4, Font Awesome, lightgallery and JS are committed directly under `web/` (no build step); app styles in `web/css/site.css`.

**Deploy.** `Dockerfile` builds on `yiisoftware/yii2-php:8.3-apache` (docker-compose's dev image is still PHP 7.4).
