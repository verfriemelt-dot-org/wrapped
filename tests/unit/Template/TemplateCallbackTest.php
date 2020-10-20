<?php

    class foo {

        public function bar() {
            return "epic";
        }

    }

    class TemplateCallbackTest extends \PHPUnit\Framework\TestCase {

        private $tpl;

        public function testClousure() {
            $this->tpl = new \Wrapped\_\Template\Template;
            $this->tpl->setRawTemplate('{{ testingVar }}');

            $this->tpl->set("testingVar", function () {
                return "epic";
            });

            $this->assertEquals($this->tpl->run(), "epic");
        }

        public function testShouldNotCallFunctions() {

            $this->tpl = new \Wrapped\_\Template\Template;
            $this->tpl->setRawTemplate('{{ testingVar }}');

            $this->tpl->set("testingVar", "system");

            $this->assertEquals($this->tpl->run(), "system");
        }

    }
