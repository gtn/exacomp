# Safe activity competence REST integration

Addresses #57 on `experimental-exaport-item-rework`. These new services do not
call either existing material editor. No Exaport content API is involved.

## Existing API findings

`block_exacomp_get_descriptors_for_example` uses the topic-filtered relation
helper; it is not a complete preservation snapshot. Material list endpoints
filter by descriptors, topics, visibility or users, and
`dakora_get_example_information` requires an already known material ID.
`block_exacomp_update_example` edits metadata and replaces relations when `comps`
is supplied. `block_exacomp_create_or_update_example` and its Diggrplus variant
also update creator/timestamp/source/activity fields, insert relations and write
visibility. None provides this additive contract.

Exacomp regenerates `db/services.php` from `@ws-type-read/write` annotations in
`classes/externallib`. The new methods carry those annotations, and the generator
now preserves optional `@ws-capabilities` metadata. Runtime authorization is
still enforced independently. The new implementation uses Moodle's autoloaded
`core_external` API with a fallback for pre-4.2 Moodle. Existing editor and
submission contracts, and the branch's Exaport item-content integration, remain
compatible.

## Contracts

All calls require access to the requested course/activity context and
`block/exacomp:teacher` in that course. Moodle validates the token's context
restriction as well as the user's capability; service registration capability
metadata is not used as a substitute for these runtime checks.

| Function (prefix `block_exacomp_`) | Type | Parameters | Result |
| --- | --- | --- | --- |
| `get_course_competences` | read | `courseid` | All descriptors directly assigned to configured course topics, plus descendants at every depth, with parent IDs, level IDs, complete descriptor and level records |
| `get_activity_competences` | read | `courseid`, `cmid` | `missing`, `unique` or `ambiguous`; every material whose `activityid` matches, complete material records, all relation rows, descriptor records for descriptor links, all material/solution visibility rows and material file metadata |
| `plan_activity_competences` | read | `courseid`, `cmid`, `exampleid`, `descriptorids[]` | Validated requested links split into `existingids` and `missingids`, empty `addedids`, inspection `state`; no writes, including no lock acquisition |
| `add_activity_competences` | write | Same as plan, plus `confirm=true` | `existingids`, `missingids` at execution time, links inserted by this call in `addedids`, resulting inspection `state` |

Snapshot `metadata`, relation `record`/`descriptor`, `niveau`, `visibility`,
`solutionvisibility` and `files` are JSON strings. Decode them before comparison.
They preserve DB nulls and raw fields without formatting or filtering. Missing
relation descriptors are represented as JSON `null`; dangling relations are
still returned. DB drivers can serialize numeric values as strings; compare IDs
numerically across installations. File metadata covers the system-context
`example_task`, `example_solution` and `example_completefile` areas only and does
not expose download URLs or file contents.

Course competence scope is defined by `block_exacompcoutopi_mm`,
`block_exacompdescrtopic_mm` and the transitive descriptor `parentid` tree.
Visibility, materials, creator, and user assessment do not reduce this catalog.
Descriptors outside configured topics remain visible in existing material
relations, but cannot be requested as new links until the course is configured
for them. This does not mean historical relations will be removed.

## Target and preservation rules

- `cmid` must be a real course module in `courseid`. Reads include all matching
  materials, including hidden, unlinked, differently authored, and inconsistent
  legacy `courseid` records.
- Writes and plans require **exactly one** material for that activity, an explicit
  matching `exampleid`, and a matching material `courseid`. Ambiguity is rejected
  even if an example ID was supplied. Resolve it deliberately using the normal
  material administration workflow before retrying.
- No material: inspection returns `missing` and an empty list; planning and
  writing fail. No material is created. Create/associate one deliberately through
  the existing Moodle UI, inspect again, then plan.
- Every requested descriptor must be positive, exist, and belong to the course
  competence scope. The whole request is validated before any insert. Empty
  lists are permitted and change nothing. Duplicate input IDs are normalized.
- Existing legacy `descrid` links and generic `table_foreign='descr'` /
  `id_foreign` links count as present. Other relation types are preserved.
- The only application-data mutation is insertion of missing legacy descriptor
  relation rows, with `sorting=0`. No parent is automatically added. Existing
  material fields, timestamps, creators, relation sorting, files, visibility,
  evaluations, and student submissions are never updated or deleted.
- `confirm` defaults to false; false/omitted confirmation fails. The caller is
  responsible for obtaining human authorization for the exact descriptor IDs.
  A previous plan is informational: the write revalidates the current state.
- A delegated transaction makes a batch atomic. A Moodle Lock API lock keyed by
  activity serializes concurrent calls to **this new write API** and protects its
  idempotence. Use a lock backend shared across all Moodle workers. Legacy
  editors/imports do not acquire this lock and can still introduce duplicates;
  this change does not alter their contracts or deduplicate historical records.

## REST acceptance procedure (test instance)

Upgrade the plugin to register the functions. Enable Moodle web services and
REST, add these functions to a service available to an enrolled course teacher,
and issue that teacher a token. The bundled `exacompservices` includes them;
a dedicated service can expose only the three read functions for dry-run tools.

POST form-encoded parameters to `/webservice/rest/server.php` with `wstoken`,
`moodlewsrestformat=json`, and `wsfunction`. Use `descriptorids[0]=...`,
`descriptorids[1]=...` for arrays. Do not put tokens in command history or URLs.

1. Read `get_course_competences(courseid)`; verify configured main competencies,
   unlinked children, hidden children, multi-level descendants, parents and levels.
2. Read `get_activity_competences(courseid, cmid)`. Save its complete result. Test
   hidden/unlinked/differently authored materials, historical inactive-topic
   descriptor links, and generic cross/topic/subject relation rows.
3. If status is missing/ambiguous, stop. Verify plan and write fail without creating
   a material or relationship. Resolve the material state explicitly in Moodle.
4. Call `plan_activity_competences` with the unique `exampleid` and exact desired
   descriptor IDs. Save the plan; inspect again and confirm no state changes.
5. Obtain authorization for those exact additions; call
   `add_activity_competences` with the same IDs and `confirm=1`.
6. Read the activity again. Check `addedids` equals the plan's missing IDs if no
   intervening changes occurred. Compare decoded material metadata, visibility,
   solution visibility, files, and all pre-existing relation rows by row ID;
   only the explicitly requested missing descriptor rows may be new.
7. Repeat the write and confirm empty `addedids`, unchanged relation count and
   state. Run two identical concurrent requests: their combined `addedids` must
   contain each missing descriptor exactly once.
8. Try omitted confirmation, invalid descriptor/material IDs, an out-of-course
   descriptor, an activity from another course, a student token, a teacher token
   from another course and a context-restricted token. Verify rejection and no
   application-data changes.

Do not substitute either existing update/create-or-update material service for
step 5: they can replace relations or change material metadata/visibility.

## Automated validation

From an initialized Moodle installation with this plugin in `blocks/exacomp`:

```sh
vendor/bin/phpunit public/blocks/exacomp/tests/activity_competences_test.php
```

For layouts without `public/`, remove that path prefix. Tests exercise external
return-value cleaning as well as service implementation methods. Concurrent
worker and actual token/REST acceptance need the separate procedure above.

References: [issue #57](https://github.com/gtn/exacomp/issues/57),
[Moodle external service security](https://moodledev.io/docs/5.2/apis/subsystems/external/security),
[Moodle Lock API](https://moodledev.io/docs/5.1/apis/core/lock).
