# Észlelési tervek (observing plans)

User-made observing programs: a user names a list of sky objects, and tracks which ones they have observed, the same
way as in the built-in observing programs (`/eszleloprogramok/<key>`, `components/SkyPrograms.php`). A plan can be
private or public; public plans can be viewed and copied by anyone.

## Status: hidden behind a feature flag

The feature is **built but switched off**. Every page and every link is hidden; the tables, models and controllers stay
in the code base so the work can be picked up later.

```php
// config/params.php
'features' => [
    'observingPlans' => false,   // true shows everything below
],
```

Checked with `Features::isEnabled(Features::OBSERVING_PLANS)` (`components/Features.php`). While it is off:

| Where | Hidden by |
|---|---|
| Every `PlanController` action (plan pages, form, JSON endpoints) | `PlanController::beforeAction()` throws 404 |
| Plan objects on the sky map (`/egterkep/terv/<id>`) | `SkyMapController::actionPlan()` throws 404 |
| `?terv=<id>` on `/egterkep` | ignored in `SkyMapController::actionIndex()` |
| "Észlelési tervek" section of `/eszleloprogramok` | `$showsPlans` in `ProgramController::actionIndex()` / `views/program/index.php` (the queries are skipped too) |
| "Észlelési tervek" section of the profile | `$showsPlans` in `UserController` / `views/user/view.php` |
| Bookmark (save) button in the object panel of the sky map | `data-saving` attribute is not rendered in `views/sky-map/_map.php`, `sky-map.js` checks it |

Not hidden, because it does not belong to the plans: the "Észlelőprogram" navbar link and the built-in programs. The
navbar keeps `plan/*` routes in its active list; that is harmless as the routes return 404.

The migrations still have to run everywhere (they are part of `php yii migrate`), the tables just stay unused.

| Phase | Status |
|---|---|
| 1. Schema, create/edit/delete, objects, plan page with automatic progress, public plans | done (19ae967) |
| 2. Marking observations by hand, save button on the sky map, plans on the profile | done (fd1e80b) |
| 3a. Plan on the sky map, copying a plan, copy counter | done, behind the flag |
| 3b. Copying a built-in program into a plan | not started |

## How it works

### Data

```
observing_plans                     one row per plan
  id, user_id, name, description,
  is_public, created_at, updated_at,
  copied_from_id  -> observing_plans.id (SET NULL): the plan it was copied from
  copy_count      how many times others copied it (deleted copies too, own copies not)

observing_plan_items                the objects of a plan
  id, plan_id, sky_object_id (NOT NULL), designation, position, created_at
  unique (plan_id, sky_object_id)

observing_plan_item_observations    observations attached by hand
  id, item_id, observation_id, created_at
  unique (item_id, observation_id)
```

- Only catalog objects (`sky_objects`) can be in a plan, no free text items. `designation` keeps the catalog number the
  user added it by when the object is known by several (e.g. NGC 1976 for M 42).
- At most `ObservingPlan::ITEM_LIMIT` (500) objects per plan.
- Item order is the order they were added (the form keeps the order of the picker). There is no drag reordering.

Migrations: `m261126_140000_create_observing_plans_tables`, `m261126_150000_create_observing_plan_item_observations_table`,
`m261126_160000_add_copied_from_id_to_observing_plans`.

### Progress

`components/ObservingPlans.php`. An object counts as done for a user when

1. the user has an observation identified as that object (`observation_sky_objects` with
   `ObservationSkyObject::VISIBLE_STATUSES`, the same rule as `SkyPrograms`), or
2. the user attached one of their own observations to the item by hand ("Észleltem" button, `plan-attach.js`).

**Progress is always the viewer's own**, also on someone else's public plan. Exception: the plan cards on a profile show
the progress of the profile's owner, like the badges.

### Visibility and permissions

- Private plan: only the owner sees it (`ObservingPlan::isVisible()`), others get 404.
- Public plan: anyone sees it; logged-in viewers can mark their own observations on it and copy it.
- Only the owner can edit, delete or save objects into the plan (`PlanController::findOwnModel()`).
- Only own observations can be attached, and only the observer can detach them.

### Pages and endpoints

| URL | Action | What |
|---|---|---|
| `/eszleloprogramok` | `program/index` | own plans + the 30 latest public plans of others (`_plans.php` cards) |
| `/eszleloprogramok/terv/uj` | `plan/create` | form; `?objektum=<id>` prefills one object (from the sky map) |
| `/eszleloprogramok/terv/<id>` | `plan/view` | object table, Összes / Megvan / Hiányzik filter, attaching |
| `/eszleloprogramok/terv/<id>/modositas` | `plan/update` | form |
| `/eszleloprogramok/terv/<id>/torles` | `plan/delete` | POST |
| `/eszleloprogramok/terv/<id>/masolas` | `plan/copy` | POST, copy to own plans |
| `/eszleloprogramok/terv/hozzarendeles` (`/torles`) | `plan/attach`, `plan/detach` | POST, mark / unmark an own observation |
| `/eszleloprogramok/terv/eszleleseim` | `plan/observations` | JSON, own observations for the attach dialog |
| `/eszleloprogramok/terv/objektum/<id>` | `plan/object-plans` | JSON, own plans + whether the object is in them (sky map save menu) |
| `/eszleloprogramok/terv/mentes` | `plan/toggle-object` | POST JSON, add/remove an object from an own plan |
| `/egterkep/terv/<id>` | `sky-map/plan` | JSON, objects of a visible plan for the map layer |
| `/egterkep?terv=<id>` | `sky-map/index` | the map opens with the plan shown |

The plan routes are placed before `eszleloprogramok/<key>` in `config/url_rules.php`.

### Sky map

- **Save button**: a bookmark icon next to the object name in the object panel (`saveButton()` in `sky-map.js`). It
  opens a menu of the user's plans with checkboxes, and "Új terv ezzel az objektummal". Guests get a login prompt.
- **Showing a plan**: the plan page's "Mutasd az égtérképen" button opens `/egterkep?terv=<id>`. `_map.php` gets a
  `program` parameter (`url`, `name`, `param`), `sky-map.js` loads it as the same layer the built-in programs use, and
  "Elrejtés" removes `?terv=` from the URL. On a profile the plan cards have a globe button, like the program badges.

### Copying

"Másolás a saját terveim közé" (someone else's plan) or "Másolat készítése" (own plan) on the plan page:

- The copy is private, keeps name, description, objects, order and designations. A copy of an own plan gets
  " (másolat)" appended.
- The copier's own hand-attached observations are carried over to the new items, other people's are not.
- The copy shows "Másolat: <original> (<author>)" while the original is visible.
- `copy_count` of the original goes up by one when someone else copies it. The plan page and the cards show it as a
  chart icon with a tooltip (`views/plan/_copyCount.php`), on every public plan, also at zero.

## Files

```
components/Features.php                  feature flag
components/ObservingPlans.php            progress queries
controllers/PlanController.php           all plan actions
controllers/ProgramController.php        plans on /eszleloprogramok
controllers/SkyMapController.php         actionPlan, ?terv=
controllers/UserController.php           plans on the profile
models/ObservingPlan.php, ObservingPlansQuery.php
models/ObservingPlanItem.php, ObservingPlanItemsQuery.php
models/ObservingPlanItemObservation.php
models/forms/ObservingPlanForm.php       create/update with the object picker (sky-objects.js)
views/plan/*                             view, form, create, update, _copyCount
views/program/_plans.php                 plan cards
views/program/_guestCta.php              login prompt (also used by the built-in programs)
web/js/plan-attach.js                    "Észleltem" dialog
web/js/sky-map.js                        save button, plan layer
web/css/site.css                         .plan-*, .plans__*, .profile-plans, .skymap__save*
```

## Known gaps and next steps

- The sky map JavaScript of phase 3a (plan layer from `?terv=`, globe buttons on profile cards) has not been tried
  in a browser yet.
- No copying of a built-in program into a plan (phase 3b).
- No reordering of the objects, no notes per object.
- `copy_count` counts copies, not distinct users: one user copying a plan twice counts twice.
- Before switching the flag on, check the guest flows again (login prompt on the sky map, copy button → login → back
  to the plan) and the empty states.
- The untracked `Collection*` files and the `m240319` collections migration are an older, unrelated concept (grouping
  observations), not part of this feature.
