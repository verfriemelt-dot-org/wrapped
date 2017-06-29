<?php

use \Wrapped\_\Template\Template;
use \Wrapped\_\Template\Variable;


	class TemplateBasicVarTest extends PHPUnit_Framework_TestCase {

        private $tpl;

        public function testsingleVar() {
            $this->tpl = new Template;
            $this->tpl->setRawTemplate('{{ var1 }}');

            $this->tpl->set("var1","test");

            $this->assertEquals($this->tpl->run(),"test");
        }

        public function testsingleVarWithFormat() {
            $this->tpl = new Template;
            $this->tpl->setRawTemplate('{{ var1|test}}');

            $this->tpl->set("var1","test");

            Variable::registerFormat("test", function ($input) {
                return "formatted";
            });

            $this->assertEquals($this->tpl->run(),"formatted");

            $this->tpl->setRawTemplate('{{ var1 }}');
            $this->assertEquals($this->tpl->run(),"test");
        }

        public function testsameVarTwice() {
            $this->tpl = new Template;
            $this->tpl->setRawTemplate('{{ var1 }} {{ var1 }}');

            $this->tpl->set("var1","test");

            $this->assertEquals($this->tpl->run(),"test test");
        }

        public function testTwoVars() {
            $this->tpl = new Template;
            $this->tpl->setRawTemplate('{{ var1 }} {{ var2 }}');

            $this->tpl->set("var1","test1");
            $this->tpl->set("var2","test2");

            $this->assertEquals($this->tpl->run(),"test1 test2");
        }

        public function testTwoVarsWithSetArray() {
            $this->tpl = new Template;
            $this->tpl->setRawTemplate('{{ var1 }} {{ var2 }}');

            $this->tpl->setArray(["var1" => "test1", "var2" => "test2"]);

            $this->assertEquals($this->tpl->run(),"test1 test2");
        }

        public function testSetArrayShouldOnlyWorkWithArrays() {
            $this->tpl = new Template;
            $this->assertEquals($this->tpl->setArray(false),false);
        }

        public function testOutputShouldBeEscaped() {

            $this->tpl = new Template;
            $this->tpl->setRawTemplate( '{{ var1 }}' );

            $this->tpl->set("var1","< > & ' \"");

            $this->assertEquals( $this->tpl->run(), "&lt; &gt; &amp; &#039; &quot;" );
        }

        public function testOutputCanBeUnescaped() {

            $this->tpl = new Template;
            $this->tpl->setRawTemplate( '{{ !var1 }}' );

            $this->tpl->set("var1","< > & ' \"");

            $this->assertEquals( $this->tpl->run(), "< > & ' \"" );
        }
	}