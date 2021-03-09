
<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;

    class AliasableTest
    extends TestCase {

        public function testPrimitive() {
            $primitive = new Value( true );
            $primitive->addAlias( new Identifier( 'testing' ) );

            $this->assertSame( 'true AS testing', $primitive->stringify() );
        }

    }
    