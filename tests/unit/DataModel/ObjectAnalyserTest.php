<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\DataModel\DataModel;

    class Dummy
    extends DataModel {

        public ?int $id;
        public ?int $attrib;

        public function getId(): ?int {
            return $this->id;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setAttrib( ?int $attrib ) {
            $this->attrib = $attrib;
            return $this;
        }

    }

    class ObjecetAnalyserTest
    extends TestCase {

        public function testAttributes() {
            $analyser = new Wrapped\_\ObjectAnalyser( new Dummy );

            // only one, because the second getter is missing
            $this->assertSame( 1, count( $analyser->fetchAttributes() ) );
        }

    }
