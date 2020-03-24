<?php

	class TemplateTest extends \PHPUnit\Framework\TestCase {

        private $tpl;

        public function testLoadTemplateFile() {
            $this->tpl = new \Wrapped\_\Template\Template;
            $this->tpl->parseFile(__DIR__ . "/templateTests/testfile.tpl");

            $this->assertEquals($this->tpl->run(),"");
        }
	}