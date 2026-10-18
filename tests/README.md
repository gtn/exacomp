# PHP compatibility checks

From the plugin directory:

```sh
sh tests/check_php_compatibility.sh
```

This runs a fresh PHP compiler process for each changed production file with
all diagnostics enabled. It fails on warnings/deprecations even when `php -l`
returns exit status zero. It does not require Moodle or a database.

From an initialized Moodle PHPUnit checkout:

```sh
vendor/bin/phpunit --fail-on-deprecation public/blocks/exacomp/tests/nullable_compat_test.php
vendor/bin/phpunit --fail-on-deprecation public/blocks/exacomp/tests/calfileparser_test.php
```

For Moodle versions before the `public/` directory split, omit that path segment.
Use Moodle's dedicated PHPUnit database, never the normal site database.
The nullable tests preserve omitted/null argument behaviour; the compiler check
is the direct regression test for declaration-time deprecations.
The calendar fixture checks both the documented one-argument file call and the
three-argument inline-content call used by the import. Web-service tests use
PHPUnit process-isolation attributes for Moodle's legacy external API bridge
(Moodle 5.1 / PHPUnit 11).
