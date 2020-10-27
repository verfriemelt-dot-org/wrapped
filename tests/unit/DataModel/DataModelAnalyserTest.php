<?php

    namespace extraNamespace;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\DataModelAnalyser;

    class Example
    extends DataModel {

        public ?int $id = null;

        public ?string $complexFieldName = null;

        public ?\Wrapped\_\DateTime\DateTime $typed = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getComplexFieldName(): ?string {
            return $this->complexFieldName;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setComplexFieldName( ?string $complexFieldName ) {
            $this->complexFieldName = $complexFieldName;
            return $this;
        }

        public function getTyped(): ?\Wrapped\_\DateTime\DateTime {
            return $this->typed;
        }

        public function setTyped( ?\Wrapped\_\DateTime\DateTime $typed ) {
            $this->typed = $typed;
            return $this;
        }

    }

    class FunctionDataModelAttributeTest
    extends TestCase {

        public function testNames() {
            $analyser = new DataModelAnalyser( new Example );

            $this->assertSame( 'Example', $analyser->getBaseName() );
            $this->assertSame( 'extraNamespace\\Example', $analyser->getStaticName() );
        }

        public function testAttributes() {
            $analyser = new DataModelAnalyser( new Example );

            $this->assertSame( 3, count( $analyser->fetchPropertyAttributes() ), 'three valid attributes' );

            // default naming convention all lower case
            $this->assertSame( 'id', $analyser->fetchPropertyAttributes()[0]->getNamingConvention()->getString() );
            $this->assertSame( 'complexfieldname', $analyser->fetchPropertyAttributes()[1]->getNamingConvention()->getString() );
        }

        public function testTypedAttributes() {

            $analyser = new DataModelAnalyser( new Example );
            $this->assertSame( 'typed', $analyser->fetchPropertyAttributes()[2]->getNamingConvention()->getString() );
            $this->assertSame( 'Wrapped\\_\\DateTime\\DateTime', $analyser->fetchPropertyAttributes()[2]->getType() );


            $this->assertTrue( class_exists( $analyser->fetchPropertyAttributes()[2]->getType() ) );
        }

    }
