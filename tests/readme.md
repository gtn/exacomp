SET PATH=%PATH%;d:\xampp\php
php -r "readfile('https://getcomposer.org/installer');" | php
#php composer.phar install --prefer-source

$CFG->phpunit_prefix = 'phpu_';
$CFG->phpunit_dataroot = 'C:\\Drives\\Programme\\xampp\\moodledata29\\phpunit';

php admin/tool/phpunit/cli/init.php

run all tests
vendor/bin/phpunit -c blocks/exacomp/tests/phpunit.xml

run just one
vendor/bin/phpunit block_exacomp_common_db_testcase blocks/exacomp/tests/common/db_test.php
