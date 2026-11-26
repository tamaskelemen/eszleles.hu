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
  | `palomar.csv` | VII/202, Harris W.E., 1996, AJ 112, 1487 (2010 edition), the Palomar globular clusters | 15 |
  | `abell.csv` | V/84, Acker A. et al., 1992, Strasbourg-ESO Catalogue of Galactic Planetary Nebulae, the Abell planetary nebulae | 86 |
  | `aco.csv` | VII/110A, Abell G.O., Corwin H.G., Olowin R.P., 1989, ApJS 70, 1, tables 3 and 4 (the southern supplement is left out) | 4076 |

  - Optional columns for the recommender: `magnitude`, `surface_brightness` (mag/arcsec², estimated from the
    magnitude and the axes if empty) and `min_axis` (arc minutes). None of the files above has them yet.
  - LDN and LBN have cross-references, `same_as` is from these. If the two positions are far from each other, the reference
    is probably a typo, so they are separate objects. The rest was chosen by hand.
  - The size of a Palomar cluster is twice its half-light radius (`Rh`). Pal 7, 9, 11 and 12 are in OpenNGC already
    (IC 1276, NGC 6717, MWSC 3171, MWSC 3561), their `same_as` was found by position.
  - Abell is two catalogs. The planetary nebulae are `Abell 1`…`Abell 86`, the clusters of galaxies are `ACO 1`…`ACO 4076`,
    and above 86 `Abell <number>` is an alias of the cluster (`aliases` column). Of the 86 "planetary nebulae" 7 are not
    in the Strasbourg-ESO catalog, these are from SIMBAD with its type (Abell 11, 64, 76 are galaxies, Abell 85 is the
    supernova remnant CTB 1). The size is the optical diameter. `same_as` is from the identifiers of the Strasbourg-ESO
    catalog and by position. Abell 85 is not LBN 576, SIMBAD does not link them and they are 10' apart.
  - The size of a cluster of galaxies is the Abell diameter, 2 × 1.72' / z. If z is unknown, it is estimated from the
    distance class (1: 0.028, 2: 0.04, 3: 0.058, 4: 0.079, 5: 0.131, 6: 0.198), so it is only a rough guide. The type is
    `GCluster`, which OpenNGC does not have.
  - The two positions of some LBN rows contradict. The galactic one is used for LBN 142 and LBN 590, the equatorial one for
    the others. LBN 601 is left out, we could not decide it.
- `aliases.csv` – our own nicknames (`alias;objects`, objects are separated by a comma). Accents, letter case, spaces
  and dashes do not matter. These win over the names in OpenNGC, and the first nickname of an object is the one we
  display. An alias can be a catalog number as well (see `M 102`).

- `lists/` – observing programs, our own format (`number;objects`), made on 2026-11-25:
  - `herschel400.csv` from the list of the
    [Astronomical League](https://www.astroleague.org/herschel-400-observing-program/) in NGC order. It has NGC 2372 where
    Wikipedia has NGC 650, the League accepts either of its two versions.
  - `rasc.csv` from the Finest NGC checklist (version 2.1a) of the [RASC](https://www.rasc.ca), the combined objects are
    chosen by hand.

## Adding a catalog

1. Make a file with the columns `name;type;ra;dec;constellation;size;same_as`: type code of OpenNGC, degrees (J2000), arc
   minutes. An optional `aliases` column has other designations of the object, separated by a comma.
2. `same_as` is the name of the object if another catalog has it already (more objects are separated by a comma). It is
   important, without it the same nebula is on the map twice. Clusters and the nebulae around them are kept apart.
3. Add the file to `SkyController::EXTRA_FILES`, and the catalog to `SkyMatcher::CATALOGS` if it is not there.
4. Run `php yii sky/import`, then `php yii sky/match --save`.
