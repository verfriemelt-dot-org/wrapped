<?php

    namespace extraNamespace;

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
    use \verfriemelt\wrapped\_\DataModel\DataModel;
    use \verfriemelt\wrapped\_\DataModel\DataModelAnalyser;
    use \verfriemelt\wrapped\_\DateTime\DateTime;

    class Example
    extends DataModel {

        public ?int $id = null;

        public ?string $complexFieldName = null;

        public ?DateTime $typed = null;

        #[ SnakeCase ]
        public $complexFieldNameSnakeCase = null;

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

        public function getTyped(): ?DateTime {
            return $this->typed;
        }

        public function setTyped( ?DateTime $typed ) {
            $this->typed = $typed;
            return $this;
        }

        public function getComplexFieldNameSnakeCase(): ?string {
            return $this->complexFieldNameSnakeCase;
        }

        public function setComplexFieldNameSnakeCase( ?string $complexFieldNameSnakeCase ) {
            $this->complexFieldNameSnakeCase = $complexFieldNameSnakeCase;
            return $this;
        }

    }

    class DataModelAnalyserTest
    extends TestCase {

        public function testNames() {
            $analyser = new DataModelAnalyser( new Example );

            $this->assertSame( 'Example', $analyser->getBaseName() );
            $this->assertSame( 'extraNamespace\\Example', $analyser->getStaticName() );
        }

        public function testAttributes() {
            $analyser = new DataModelAnalyser( new Example );

            $this->assertSame( 4, count( $analyser->fetchProperties() ), 'four valid attributes' );

            // default naming convention snake case
            $this->assertSame( 'id', $analyser->fetchProperties()[0]->getNamingConvention()->getString() );
            $this->assertSame( 'complex_field_name', $analyser->fetchProperties()[1]->getNamingConvention()->getString() );
        }

        public function testTypedAttributes() {

            $analyser = new DataModelAnalyser( new Example );
            $this->assertSame( 'typed', $analyser->fetchProperties()[2]->getNamingConvention()->getString() );
            $this->assertSame( DateTime::class, $analyser->fetchProperties()[2]->getType() );

            $this->assertTrue( class_exists( $analyser->fetchProperties()[2]->getType() ) );
        }

        public function testSnakeCaseConvention() {

            $analyser = new DataModelAnalyser( new Example );
            $this->assertSame( 'complex_field_name_snake_case', $analyser->fetchProperties()[3]->getNamingConvention()->getString() );
        }

    }
