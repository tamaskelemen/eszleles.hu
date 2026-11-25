# Sky catalog

Data of the sky map, imported by `php yii sky/import`.

- `NGC.csv`, `addendum.csv` – unmodified files of [OpenNGC](https://github.com/mattiaverga/OpenNGC) by Mattia Verga,
  commit `be3cd6c5498806a2fbb2f7e0452ef802dbb02be0`, licensed under
  [CC-BY-SA-4.0](https://creativecommons.org/licenses/by-sa/4.0/). The page that shows the data has to credit it.
- Our own format, made from [VizieR](https://vizier.cds.unistra.fr) (CDS, Strasbourg) through its Harvard mirror on
  2026-11-25. Positions are for J2000, computed by VizieR from the old ones, so they are accurate to a few arc minutes. We
  added the constellation, the type and `same_as`:

  | File | Catalog | Rows |
  |---|---|---|
  | `sharpless.csv` | VII/20, Sharpless S., 1959, ApJS 4, 257 | 313 |
  | `barnard.csv` | VII/220A, Barnard E.E., 1927, Carnegie Institution of Washington | 349 |
  | `ldn.csv` | VII/7A, Lynds B.T., 1962, ApJS 7, 1 | 1787 |
  | `lbn.csv` | VII/9, Lynds B.T., 1965, ApJS 12, 163 | 1124 |
  | `vdb.csv` | VII/21, van den Bergh S., 1966, AJ 71, 990 | 158 |

  - LDN and LBN have cross-references, `same_as` is from these. If the two positions are far from each other, the reference
    is probably a typo, so they are separate objects. The rest was chosen by hand.
  - The two positions of some LBN rows contradict. The galactic one is used for LBN 142 and LBN 590, the equatorial one for
    the others. LBN 601 is left out, we could not decide it.
- `aliases.csv` – our own nicknames (`alias;objects`, objects are separated by a comma). Accents, letter case, spaces
  and dashes do not matter. These win over the names in OpenNGC, and the first nickname of an object is the one we
  display. An alias can be a catalog number as well (see `M 102`).

## Adding a catalog

1. Make a file with the columns `name;type;ra;dec;constellation;size;same_as`: type code of OpenNGC, degrees (J2000), arc
   minutes.
2. `same_as` is the name of the object if another catalog has it already (more objects are separated by a comma). It is
   important, without it the same nebula is on the map twice. Clusters and the nebulae around them are kept apart.
3. Add the file to `SkyController::EXTRA_FILES`, and the catalog to `SkyMatcher::CATALOGS` if it is not there.
4. Run `php yii sky/import`, then `php yii sky/match --save`.
