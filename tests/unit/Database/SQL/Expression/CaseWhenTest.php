<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\CaseWhen;
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
            $when->when( new Value( true ), new Value( false ) );
            $this->assertSame( 'CASE WHEN true THEN false END', $when->stringify() );
        }

        public function testMinimalElse() {
            $when = new CaseWhen();
            $when->when( new Value( true ), new Value( false ) );
            $when->else( new Value( NULL ) );
            $this->assertSame( 'CASE WHEN true THEN false ELSE NULL END', $when->stringify() );
        }

        public function testMultipleWhen() {
            $when = new CaseWhen();
            $when->when( new Value( true ), new Value( false ) );
            $when->when( new Value( false ), new Value( true ) );
            $when->else( new Value( NULL ) );
            $this->assertSame( 'CASE WHEN true THEN false WHEN false THEN true ELSE NULL END', $when->stringify() );
        }

        public function testSwitchStyle() {
            $when = new CaseWhen( new Value( 1 ) );
            $when->when( new Value( 1 ), new Value( false ) );
            $when->when( new Value( 2 ), new Value( true ) );
            $this->assertSame( 'CASE 1 WHEN 1 THEN false WHEN 2 THEN true END', $when->stringify() );
        }

        public function testSwitchStyleElse() {
            $when = new CaseWhen( new Value( 1 ) );
            $when->when( new Value( 1 ), new Value( false ) );
            $when->when( new Value( 2 ), new Value( true ) );
            $when->else( new Value( NULL ) );
            $this->assertSame( 'CASE 1 WHEN 1 THEN false WHEN 2 THEN true ELSE NULL END', $when->stringify() );
        }

    }
