<?php

	class TemplateIfElseTest extends \PHPUnit\Framework\TestCase {

        private $tpl;

        private $testCases = [
            [
                "name" => "standard if else",
                "tpldata" => '{{ if=\'test\' }}true{{ else=\'test\' }}false{{ /if=\'test\'}}',
                "tests" => [
                    [ "set" => true,  "expected" => "true" ],
                    [ "set" => false, "expected" => "false" ]
                ]
            ],
            [
                "name" => "negated standard if else",
                "tpldata" => '{{ !if=\'test\' }}true{{ else=\'test\' }}false{{ /if=\'test\'}}',
                "tests" => [
                    [ "set" => false, "expected" => "true" ],
                    [ "set" => true, "expected" => "false" ]
                ]
            ],
            [
                "name" => "standard if null",
                "tpldata" => '{{ if=\'test\' }}true{{ /if=\'test\' }}',
                "tests" => [
                    [ "set" => true, "expected" => "true" ],
                    [ "set" => false, "expected" => "" ]
                ]
            ],
            [
                "name" => "negated standard if null",
                "tpldata" => '{{ !if=\'test\' }}true{{ /if=\'test\' }}',
                "tests" => [
                    [ "set" => true, "expected" => "" ],
                    [ "set" => false, "expected" => "true" ]
                ]
            ],
            [
                "name" => "negated and standard if null",
                "tpldata" => '{{ if=\'test\' }}true{{ /if=\'test\' }}{{ !if=\'test\' }}true{{ /if=\'test\' }}',
                "tests" => [
                    [ "set" => true, "expected" => "true" ],
                ]
            ],
            [
                "name" => "negated and standard if null",
                "tpldata" => '{{ if=\'test\' }}false{{ else=\'test\'}}true{{ /if=\'test\' }}{{ !if=\'test\' }}true{{ /if=\'test\' }}',
                "tests" => [
                    [ "set" => false, "expected" => "truetrue" ],
                    [ "set" => true, "expected" => "false" ],
                ]
            ]
        ];

		public function test() {

            foreach($this->testCases as $cases) {
                $this->tpl = new \verfriemelt\wrapped\_\Template\Template;
                $this->tpl->setRawTemplate($cases["tpldata"]);

                foreach($cases["tests"] as $case) {
                    $this->tpl->setIf("test",$case["set"]);
                    $this->assertEquals($case["expected"],$this->tpl->run(),$cases["name"]);
                }
            }
        }

        public function testNestedEmpty() {
            $this->tpl = new \verfriemelt\wrapped\_\Template\Template;
            $this->tpl->setRawTemplate(file_get_contents(__DIR__ . "/templateTests/ifelseNested.tpl"));

            $this->assertEquals("",$this->tpl->run());
        }

        public function testNestedSetA() {
            $this->tpl = new \verfriemelt\wrapped\_\Template\Template;
            $this->tpl->setRawTemplate(file_get_contents(__DIR__ . "/templateTests/ifelseNested.tpl"));

            $this->tpl->setIf("a");

            $this->assertEquals("aa",$this->tpl->run());
        }

        public function testNestedSetB() {
            $this->tpl = new \verfriemelt\wrapped\_\Template\Template;
            $this->tpl->setRawTemplate(file_get_contents(__DIR__ . "/templateTests/ifelseNested.tpl"));

            $this->tpl->setIf("b");

            $this->assertEmpty($this->tpl->run());
        }

        public function testNestedSetAB() {
            $this->tpl = new \verfriemelt\wrapped\_\Template\Template;
            $this->tpl->setRawTemplate(file_get_contents(__DIR__ . "/templateTests/ifelseNested.tpl"));

            $this->tpl->setIf("a");
            $this->tpl->setIf("b");

            $this->assertEquals("aba",$this->tpl->run());
        }
	}