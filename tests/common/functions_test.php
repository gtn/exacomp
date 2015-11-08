<?php

require_once __DIR__.'/inc.php';

class block_exacomp_common_functions_testcase extends basic_testcase {
    public function test_t() {
        global $SESSION;
        $SESSION->forcelang = 'de';
        
        $this->assertEquals('Some String', block_exacomp\common\t('Some String'));
        $this->assertEquals('xxx arg xxx', block_exacomp\common\t('de:xxx {$a} xxx', 'arg'));
        $this->assertEquals('xxx', block_exacomp\common\t('de:xxx'));
        $this->assertEquals('xxx', block_exacomp\common\t('id', 'de:xxx'));
        $this->assertEquals('xxx', block_exacomp\common\t('id', ['de:xxx', 'en:yyy']));
        $this->assertEquals('yyy', block_exacomp\common\t('id', ['en:xxx', 'de:yyy']));
        $this->assertEquals('xxx arg xxx', block_exacomp\common\t('de:xxx {$a} xxx', 'arg'));
        $this->assertEquals('xxx arg xxx', block_exacomp\common\t('id', 'de:xxx {$a} xxx', 'arg'));
        $this->assertEquals('xxx arg xxx', block_exacomp\common\t('id', ['de:xxx {$a} xxx', 'en:xxx {$a} xxx'], 'arg'));
        $this->assertEquals('xxx test xxx', block_exacomp\common\t('id', 'de:xxx {$a->arg} xxx', ['arg' => 'test']));
        $this->assertEquals('xxx test xxx', block_exacomp\common\t('id', ['de:xxx {$a->arg} xxx', 'en:yyy {$a->arg} yyy'], ['arg' => 'test']));

        // other language
        $this->assertEquals('asdf', block_exacomp\common\t('id', 'fr:asdf'));
    }
}