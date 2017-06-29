<?php

    class TemplateRepeaterTest
    extends PHPUnit_Framework_TestCase {

        public $tpl;

        public function testBasicRepeater() {

            $this->tpl = new \Wrapped\_\Template\Template;
            $this->tpl->setRawTemplate( file_get_contents( __DIR__ . "/templateTests/repeater.tpl" ) );

            $r          = $this->tpl->createRepeater( "r" );
            $testString = "";
            for ( $i = 0; $i < 9; ++$i ) {
                $testString .= $i;
                $r->set( "i", $i )->save();
            }

            $this->assertEquals( $testString, $this->tpl->run() );
        }

        public function testNestedRepeater() {

            $this->tpl = new \Wrapped\_\Template\Template;
            $this->tpl->setRawTemplate( file_get_contents( __DIR__ . "/templateTests/nestedRepeater.tpl" ) );

            $k = $this->tpl->createRepeater( "k" );


            $testString = "";

            for ( $ki = 0; $ki < 3; ++$ki ) {

                $r = $k->createChildRepeater( "r" );

                for ( $ri = 0; $ri < 3; ++$ri ) {
                    $testString .= $ki .".". $ri;
                    $r->set( "i", $ri )->save();
                }

                $k->set( "j", $ki )->save();
            }

            $this->assertEquals( $testString, $this->tpl->run() );
        }

    }
