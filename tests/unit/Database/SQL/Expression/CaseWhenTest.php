<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\CaseWhen;
    use \Wrapped\_\Database\SQL\Expression\Primitive;
    use \Wrapped\_\Database\SQL\Expression\Value;

    class CaseWhenTest
    extends TestCase {

        public function testInit() {
            new CaseWhen();
        }

        public function testEmpty() {
            $this->expectExceptionObject( new Exception( 'empty' ) );
            $when = new CaseWhen();
            $when->stringify();
        }

        public function testMinimal() {
            $when = new CaseWhen();
            $when->when( new Primitive( true ), new Primitive( false ) );
            $this->assertSame( 'CASE WHEN true THEN false END', $when->stringify() );
        }

        public function testMinimalElse() {
            $when = new CaseWhen();
            $when->when( new Primitive( true ), new Primitive( false ) );
            $when->else( new Primitive( null ) );
            $this->assertSame( 'CASE WHEN true THEN false ELSE null END', $when->stringify() );
        }

        public function testMultipleWhen() {
            $when = new CaseWhen();
            $when->when( new Primitive( true ), new Primitive( false ) );
            $when->when( new Primitive( false ), new Primitive( true ) );
            $when->else( new Primitive( null ) );
            $this->assertSame( 'CASE WHEN true THEN false WHEN false THEN true ELSE null END', $when->stringify() );
        }

        public function testSwitchStyle() {
            $when = new CaseWhen( new Value( 1 ) );
            $when->when( new Value( 1 ), new Primitive( false ) );
            $when->when( new Value( 2 ), new Primitive( true ) );
            $this->assertSame( 'CASE 1 WHEN 1 THEN false WHEN 2 THEN true END', $when->stringify() );
        }

        public function testSwitchStyleElse() {
            $when = new CaseWhen( new Value( 1 ) );
            $when->when( new Value( 1 ), new Primitive( false ) );
            $when->when( new Value( 2 ), new Primitive( true ) );
            $when->else( new Primitive( null ) );
            $this->assertSame( 'CASE 1 WHEN 1 THEN false WHEN 2 THEN true ELSE null END', $when->stringify() );
        }

    }
