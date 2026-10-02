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

The serializer's flat `files` entries must expose the stored-file `id`, URL,
filename, MIME type, and `isimage` flag. Exacomp returns that stored-file ID as
both the DiggrPlus deletion identity (`studentfiles[].id`) and `fileindex`.
Parent item IDs, content-block IDs, and stored-file IDs are not interchangeable.
Deleting or replacing content passes the complete, trusted parent item to
Exaport so it can resolve the owner's user context and remove every structured
file area belonging to the blocks.

The two singular submission APIs replace all structured blocks when updating,
matching their former replace-one-link/file semantics. DiggrPlus historically
appended files to an in-progress submission, so an update adds a new structured
file block without deleting existing blocks. Creation always starts with one
new content block.

## Deployment contract

Deploy the matching Exaport structured-content release before this Exacomp
release. Exacomp deliberately fails if Exaport's structured serializer, block
creation/import, or deletion helpers are unavailable; there is no fallback to
legacy parent fields or the `item_file` area. DiggrPlus can then be deployed (or
left unchanged) because its existing `studentfiles[].id` removal request is the
stored-file ID expected by Exacomp.
