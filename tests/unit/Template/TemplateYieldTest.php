<?php

    class TemplateYieldTest
    extends \PHPUnit\Framework\TestCase {

        private $tpl;

        public function testLoadTemplateFile() {
            $this->tpl = new \Wrapped\_\Template\Template;
            $this->tpl->parseFile( __DIR__ . "/templateTests/repeater.tpl" );

            $r          = $this->tpl->createRepeater( "r" );
            $testString = "";
            for ( $i = 0; $i < 9; ++$i ) {
                $testString .= $i;
                $r->set( "i", $i )->save();
            }

            $output = "";

            foreach ( $this->tpl->yieldRun() as $tmp ) {
                $output .= $tmp;
            }

            $this->assertEquals( $output, "012345678" );
        }

    }
