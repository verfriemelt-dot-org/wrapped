<?php

    namespace extraNamespace;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\DataModelAnalyser;

    class LowerDummy
    extends DataModel {

        public ?int $id = null;

        public ?string $complexFieldName = null;

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

    }

    class FunctionDataModelAttributeTest
    extends TestCase {

        public function testNames() {
            $analyser = new DataModelAnalyser( new LowerDummy );

            $this->assertSame( 'LowerDummy', $analyser->getBaseName() );
            $this->assertSame( 'extraNamespace\\LowerDummy', $analyser->getStaticName() );
        }

        public function testAttributes() {
            $analyser = new DataModelAnalyser( new LowerDummy );

            $this->assertSame( 2, count( $analyser->fetchPropertyAttributes() ), 'two valid attributes' );

            // default naming convention all lower case

            $this->assertSame( 'id', $analyser->fetchPropertyAttributes()[0]->getNamingConvention()->getString() );
            $this->assertSame( 'complexfieldname', $analyser->fetchPropertyAttributes()[1]->getNamingConvention()->getString() );
        }

    }
