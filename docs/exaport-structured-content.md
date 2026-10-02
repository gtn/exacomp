# Exaport structured item-content integration

Exacomp treats `block_exaportitemblock` content as the only active content of an
Exaport item. Parent `url` and `attachment` values and the legacy `item_file`
area are intentionally ignored, even when no structured content exists.

## Active external-function inventory

The registered services affected by this boundary are:

* `block_exacomp_get_item_for_example` (structured projection, with its
  pre-existing single-file response limited to the first canonical file);
* `block_exacomp_submit_example`, `dakora_submit_example`, and
  `diggrplus_submit_item` (structured link/file writes);
* `dakora_get_example_information` (structured URL and flat file projection);
* `diggrplus_get_examples_and_items`,
  `diggrplus_get_teacher_examples_and_items`,
  `dakoraplus_get_example_and_item`, and
  `dakoraplus_get_teacher_example_and_item` (all use the shared item-detail
  projection);
* `block_exacomp_delete_item` (structured-aware deletion).

Exaport's compatibility serializer remains responsible for canonical block and
file ordering, selecting the first non-empty link, flattening file blocks, and
generating token-authorised `webservice/pluginfile.php` URLs. Exacomp does not
reimplement those rules.

For file removal, `studentfiles[].id` and `studentfiles[].fileindex` represent
the stored-file ID, never a parent item ID, content-block ID, or list position.
The current serializer does not include that ID, so Exacomp resolves it from
Exaport's structured block/file helpers in the serializer's canonical order and
verifies each filename and MIME type before returning it. Removal is then scoped
to the selected item's file blocks and the owner's user context.

The two singular submission APIs replace all structured blocks when updating,
matching their former replace-one-link/file semantics. DiggrPlus historically
appended files to an in-progress submission, so an update adds a new structured
file block without deleting existing blocks. Creation always starts with one
new content block.

Install the Exaport structured-content migration and access fixes before
deploying this Exacomp integration. Exaport remains responsible for preserving
shared-view, teacher/trainer, private-PDF, embedded-text-asset, and legacy
download authorization; Exacomp does not broaden those file-access policies.
