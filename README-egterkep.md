# Égtérkép (sky map)

The goal is a public all-sky map that shows which parts of the sky the community has observed. For this, every
deep sky observation has to be connected to catalog objects that have coordinates. The title of an observation is
free text ("Trapézium (M42 Orion-köd)"), so most of the work is finding out what it is about.

## Status

| Step | Status |
|---|---|
| 1. Catalog import, title matcher, dry run | done |
| 2. Storing the objects of the observations, upload form, admin pages, tags on the observation page | done |
| 3. The map at `/egterkep`, with statistics | done |
| 4. More catalogs | Sharpless, Barnard, LDN, LBN and vdB are done. Next: Abell, open clusters (Collinder, Stock, Trumpler, Berkeley). VizieR has them, its Harvard mirror is `vizier.cfa.harvard.edu` |

## How it works

### In short

1. The catalog gives names and coordinates to the objects.
2. When a deep sky observation is uploaded, the objects are found in its title. The uploader sees and can correct them
   before sending the form.
3. Sure matches and the ones approved by a person are public: they are tags on the observation page and dots on the map.
   Unsure ones wait for an admin.
4. The map counts the observations of every object. Its data is cached, the cache is cleared when anything changes.

```
seed/sky/*.csv ──sky/import──> sky_objects, sky_object_aliases
                                          │
title of an observation ──SkyMatcher──────┤
                                          │
uploader, admin ────────────SkyLinker─────┴──> observation_sky_objects ──> tags, map
```

### Catalog

`php yii sky/import` loads the files of `seed/sky/` (see its README for the sources and the license):

- `sky_objects`: about 16,200 objects with RA and Dec in degrees (J2000), constellation, type, size, Messier number and
  Hungarian name.
- `sky_object_aliases`: every key an object can be found by. A catalog number is stored like `NGC 1976`, a nickname
  without accents, spaces and dashes, like `orionkod`. A nickname may belong to several objects (`Leo Triplet`).

The import can be run again any time: the ids of the objects do not change, the aliases are rebuilt. Objects that are
no longer in the files are not deleted.

`aliases.csv` is ours and wins over OpenNGC. For example `M 102` is NGC 5866, because that is what our observers mean by
it, while OpenNGC says it is M 101.

### Matching

`components/SkyMatcher.php` gets a title (and the constellation, if there is one) and returns the objects and a status.
It does not need a database, only a function that looks up aliases.

- **Catalog numbers** are trusted: `NGC 7000`, `Ngc-7000`, `M42DF`, `Sh 2-155`, `Caldwell 9`.
- **Lists** work for most catalogs: `NGC 3185, 3187`, `M42-43`, `NGC 869+884`. The further numbers count only if they
  are within 8° of the first one, so dates are not taken for objects.
- **Nicknames** are used only if there is no known catalog number in the title. Common suffixes are dropped
  (`Szélkerék-galaxisban`).
- **Look-alikes** are skipped: comets (`C/2022 E3`) and Minkowski numbers (`M1-92`).

| Status | When |
|---|---|
| `auto` | a catalog number was found and nothing contradicts it |
| `review` | only a nickname was found, or the constellation does not match, or the nickname means an object somewhere else (`Észak-Amerika-köd NGC700`), or the only two objects are more than 45° apart (`LDN 845 és IC 444`) |
| `none` | nothing was found, catalog numbers we do not know are reported |

### Storing

`observation_sky_objects` connects observations and objects. An observation can have any number of objects.

| Status | Meaning | Public |
|---|---|---|
| `auto` | found by the matcher | yes |
| `review` | found by the matcher, waits for an admin | no |
| `confirmed` | approved or chosen by the uploader or an admin | yes |
| `rejected` | removed by the uploader or an admin | no |

`components/SkyLinker.php` writes the table:

- `rematch()` is the machine. It only touches `auto` and `review` rows. `rejected` rows are kept, so it never adds back
  what a person has removed.
- `confirm()` is the person. What they chose becomes `confirmed`, everything else becomes `rejected`.

`designation` holds the number used in the title if the catalog prefers another one: NGC 2244 is listed as NGC 2239, but
we display NGC 2244.

`observations.has_no_sky_object` means that an admin has decided there is nothing to find (the title is "Hold"). It is
cleared if the title changes, or if someone adds an object.

The title of the observation (`object_name`) is never changed.

### Uploading and editing

`Observe::afterSave()` takes care of the deep sky observations, so it works for new uploads and edits too.

The forms use `widgets/SkyObjectInput.php` and `web/js/sky-objects.js`. While the title is typed, the recognized objects
appear as removable items, and others can be searched for. Saving the form counts as approval.

The form also posts the title the items belong to (`skyObjectTitle`):

- it is the same as the saved title: the list is final, see `confirm()`
- it is different, because the form was sent too quickly: the listed objects are confirmed, then the title is matched
- it is missing, because there is no JavaScript: the title is matched if it has changed

`SkyController` (web) serves the widget: `/sky/match` and `/sky/search`, only for logged in users.

### Admin pages

**Azonosítás** (`/admin/azonositas`) lists the observations of every type by tabs: waiting for review, not identified,
identified, has no object, all. Rows can be approved, or marked as having no object, in bulk.

`/admin/azonositas/<id>` edits the objects of any observation, not only deep sky ones. Admins have a link to it on the
observation page. Saving an empty list means that the observation has no object. Edits are in the activity log.

### Observation page

The `auto` and `confirmed` objects are displayed as tags (`.sky-tag`) under "Azonosított objektum". They are not
clickable yet.

### The map

`/egterkep` is public, it is in the main menu, marked as beta. Only `auto` and `confirmed` objects count.

**Data.** `components/SkyMap.php` collects the observed objects and the statistics, and caches them for 10 minutes. The
cache is cleared when an observation is uploaded, edited or deleted, and when an identification changes
(`SkyMap::clearCache()`), so these show up at once. A page that is already open has to be reloaded.

`/egterkep/adatok` gives the objects to the page, `/egterkep/objektum/<id>` gives the latest 12 observations of an object.

**Drawing.** `web/js/sky-map.js` draws on a canvas with D3: Mollweide projection, equatorial coordinates, east is on the
left. Everything is served from `web/skymap/` (see its README for the sources and the licenses), nothing is loaded from
other sites.

- The heatmap is logarithmic, otherwise M 42 and M 31 would hide everything else. It can show the number of observations
  or of observers. Zoomed in, it fades and the labelled objects take over.
- The edge of the map is at 14h40m, between Virgo and Hercules, where the fewest objects are cut apart.
- The sky below -43° never rises in Hungary: it is shaded, but observations made elsewhere are shown.
- The mouse wheel only zooms with Ctrl, or after a click on the map, so the page can be scrolled over it. On touch screens
  it needs two fingers, one is enough in full screen.
- Every object is in a table below the map as well, the canvas can not be read by screen readers.

**Statistics.** Coverage is the share of the sky above -43° that is within 3° of an observed object, sampled at 6,000
points. White spots are the largest circles above -25° without any object, their constellation is that of the nearest
object of the catalog. A Caldwell number counts if any of its objects was observed (C 14 is NGC 869 and NGC 884).

The table at the bottom lists the catalogs we do not have, with the number of not identified observations that have such a
number.

### Badges and observing programs

The profiles have badges (Jelvények) for uploads, comments, types of observations and constellations, and for observing
programs (Észlelőprogramok): Messier, Caldwell, Herschel 400, RASC Finest NGC, NGC, Sharpless, Barnard and vdB.

- `components/Badges.php` counts the badges of a user, it is slow (15-35 ms, 8 queries). The date of a badge is the date of
  the item that reached the level.
- `user_badges` stores the earned ones, so they can be displayed anywhere: `$user->badges`, use `with('badges')` for more
  users. `UserBadge::filterHighest()` gives the highest one of every category, see the observation page.
- `components/BadgeSync.php` keeps the table up to date. Whatever changes something that counts calls
  `BadgeSync::markChanged()`, the marked users are counted again once, at the end of the request or the command, and the
  differences are stored. Deleting a comment or correcting an identification takes the badge back too.
- The models mark the users themselves. **`updateAll()`, `deleteAll()` and plain SQL do not**, call it by hand there. If
  something is missed, the owner repairs it by opening their profile, or run `php yii badges/sync`.
- Only the owner gets the missing badges and the progress, these are counted when their profile is opened. Everybody else
  gets the stored ones. This is why the Messier, Caldwell and constellation tiles are not on the profiles.
- The user is told about the new badges in a flash message, if it was their own request. A notification for the ones earned
  by the approval of an admin may come later.
- `components/SkyPrograms.php` describes the programs. Their items are in `sky_list_items`, written by `sky/import`: from the
  aliases, or from `seed/sky/lists/`. An item is done if any of its objects is publicly identified.
- `/eszleloprogramok` lists the programs and their objects. Everybody sees only their own observations there.

"Visible from Hungary" means north of -35° here (`SkyPrograms::VISIBLE_DEC`): these rise to 7.5° at least in Budapest. It is
stricter than the horizon of the map (-43°), where the objects barely rise.

| | Visible | All |
|---|---|---|
| Messier | 110 | 110, and a separate badge for 109 without the disputed M 102 |
| Caldwell | 67 | 109 |
| NGC | 6,415 | 7,575, because duplicates and not existing objects of the 7,840 are left out |
| Constellations | 63 | 88 |

To add a program: a csv in `seed/sky/lists/` (`number;objects`), a constant, an entry in `FILES` and in `getAll()`, then
`sky/import`.

### Tags of the images

`ImageTag` calls `SkyLinker::syncTags()` when a tag is saved or deleted, for every type of observation. The whole tag has to
be the name of an object, or its parts separated by `/`, `,`, `;`, ` - ` or parentheses.

- a catalog number is `auto`, a nickname is `review`
- objects farther than 8° from the ones in the title are `review`
- what the title or a person has linked, or a person has rejected, is left alone

`observation_sky_objects.source` is `title` or `tag`: a change of one does not remove what was found in the other.

## Setup

Needs PHP 7.4 or newer and PostgreSQL 9.4 or newer. There is nothing to configure and nothing is downloaded: the catalogs,
the background of the map and D3 are in the repository (`seed/sky/`, `web/skymap/`).

```bash
php yii migrate              # tables of the catalog, the identifications and the observing programs
php yii sky/import           # load the catalog, about 6 seconds, needs about 100 MB of memory
php yii sky/match            # optional dry run, see below
php yii sky/match --save     # identify the existing observations
```

With Docker: `docker exec -w /app eszleleshu-php-1 php yii sky/import` and so on.

| Command | What it does |
|---|---|
| `php yii sky/import` | loads or refreshes the catalog, can be repeated any time |
| `php yii sky/match` | dry run, writes `runtime/sky-match.csv` (opens in Excel), saves nothing |
| `php yii sky/match --input=titles.csv` | dry run on a csv (`object_name,constellation,count`) instead of the database |
| `php yii badges/sync` | counts the badges of every user again, can be repeated any time |
| `php yii sky/match --save` | stores the matches, can be repeated any time, it does not touch the decisions of people |
| `vendor/bin/codecept run unit components` | tests |

## Deploying

The live site is on a VPS without Docker, in `/var/www/html/eszleleshu`. Run the commands as the user of the web server
(usually `www-data`), otherwise it may not be able to write the files they create in `runtime/`, and they may not be able to clear
the cache.

Before the first time:

- Check the [licence terms of VizieR](https://cds.unistra.fr/vizier-org/licences_vizier.html), the page was down when the
  catalogs were downloaded.
- Do not commit `output.csv`.

```bash
cd /var/www/html/eszleleshu
sudo -u postgres pg_dump <database> > ~/eszleles-before-egterkep.sql

git pull
sudo -u www-data php yii migrate      # right after the pull, see below
sudo -u www-data php yii sky/import
sudo -u www-data php yii sky/match    # dry run, compare it with the numbers below
sudo -u www-data php yii sky/match --save
```

Between the pull and the migration deep sky observations can not be saved, because they need the new tables.

Check after it:

1. `/egterkep` shows the objects, and the numbers below it are plausible.
2. An observation page has tags ("Azonosított objektum").
3. The upload form of deep sky observations recognizes "M 42" while it is typed.
4. `/admin/azonositas` opens, and the review tab has about 140 observations.

Then go through the review tab. These are wrong, do not approve them, edit them instead:

| Title | Matched to | Probably |
|---|---|---|
| Észak-Amerika-köd NGC700 (részlet) | NGC 700 | NGC 7000 |
| NGC 539 (559) | NGC 539 | NGC 559 |
| NGC 664 (663) | NGC 664 | NGC 663 |
| Déli Ebihal-köd és a GUM 37 ködösség | IC 410 | not in our nicknames |

Both of these have a typo: `M35 & NGC 6207` (NGC 6207 is next to M 13), `LDN 845 és IC 444` (LBN 845 is next to IC 444).
`A másik Kígyó-köd` is not B 72, it is the other one.

Check `NGC 6663` (marked Aql, but it is in Lyra) and `M15 GH (Per)` (probably Peg) as well.

### Later deploys

| What has changed | What to run after `git pull` |
|---|---|
| only code | nothing, except for `migrate` if there is a new migration |
| the badges (first deploy of them) | `migrate`, `sky/import`, `sky/match --save` for the old tags, then `badges/sync`. Run `migrate` right after the pull, without it the profiles and the observations do not open, and deep sky observations can not be saved. Until `sky/import` the programs are only hidden |
| anything in `seed/sky/` | `sky/import`, then `sky/match --save` |
| `components/SkyMatcher.php` | `sky/match --save` |
| the structure of the data in `components/SkyMap.php` | nothing, but change `SkyMap::CACHE_KEY` in the code |

### Undoing it

Reverting the code is enough, the old code ignores the new tables. To remove the tables too, run `php yii migrate/down 5`
before reverting the code, because it needs the migration files. **Every decision of the uploaders and the admins is lost
with them.** It undoes the last five migrations (two of the map, three of the badges), so check first with `php yii migrate/history` that they are these ones.

## Maintenance

- **Nickname:** add a line to `seed/sky/aliases.csv` (`alias;objects`, the objects by their name in `sky_objects`, separated
  by a comma), then run `sky/import` and `sky/match --save`. The first nickname of an object is the one we display.
- **Catalog:** see the README of `seed/sky/`. Remove it from `SkyMap::UNMAPPED_CATALOGS` as well.
- **One observation:** edit it on `/admin/azonositas`, that is faster than adding a nickname for it.
- **Review:** look at the review tab of `/admin/azonositas` from time to time, nobody is notified about it.

## Numbers

Dry run on the 2,254 deep sky observations of the live site (2026-11-25):

| Status | Observations |
|---|---|
| `auto` | 1,987 (88.2%) |
| `review` | 142 (6.3%) |
| `none` | 125 (5.5%) |

- About 37 of the `none` ones have a catalog number we do not have yet. Step 4 is about these.
- Abell is two catalogs: Abell 39 is a planetary nebula, Abell 1656 is a cluster of galaxies. Some of the planetary
  nebulae are Sharpless regions as well (Sh2-274, Sh2-290, Sh2-313), mind `same_as`.
- The rest have an unknown nickname, or are not about an object at all.
- An earlier rough count found about 680 different objects in 59 constellations, including every Messier object.

## Files

| File | Role |
|---|---|
| `seed/sky/` | catalog files and nicknames |
| `commands/SkyController.php` | import and match commands |
| `components/SkyMatcher.php` | title → objects |
| `components/SkyLinker.php` | stores the objects of an observation |
| `models/SkyObject.php` | catalog object, search, labels |
| `models/ObservationSkyObject.php` | object of an observation, statuses |
| `models/IdentificationSearch.php` | filters of the admin list |
| `controllers/SkyController.php` | JSON for the form |
| `controllers/AdminController.php`, `views/admin/identification.php`, `views/admin/identify.php` | admin pages |
| `widgets/SkyObjectInput.php`, `widgets/views/skyObjectInput.php`, `web/js/sky-objects.js` | form input |
| `migrations/m261125_100000_*`, `migrations/m261125_140000_*` | tables |
| `components/Badges.php`, `components/BadgeSync.php`, `models/UserBadge.php`, `commands/BadgesController.php`, `views/user/_badges.php`, `views/_common-items/_badge*.php` | badges |
| `components/SkyPrograms.php`, `controllers/ProgramController.php`, `views/program/`, `seed/sky/lists/` | observing programs |
| `components/SkyMap.php`, `controllers/SkyMapController.php`, `views/sky-map/` | data and page of the map |
| `web/js/sky-map.js`, `web/skymap/` | drawing, D3 and the background |
| `tests/unit/components/` | tests, no database needed |

## Known limits

- Titles are only matched automatically for deep sky observations. Other types can be identified by an admin.
- The matcher and the calculation of the coverage have automated tests. The linking, the forms, the admin pages and the
  map were tested by hand.
- The Moon, the Sun, planets, eclipses and meteors are not on the map. Comets may come later, they need an ephemeris.
- Some of the catalogs are partial: of UGC, PGC, Collinder, Melotte and Hickson we only have the numbers that
  OpenNGC lists. To add a catalog see the README of `seed/sky/`.
- If a catalog row gets a `same_as` later, its old object stays in `sky_objects` and keeps its confirmed links.
