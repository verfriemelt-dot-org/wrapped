
<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Primitive;

    class AliasableTest
    extends TestCase {

        public function testPrimitive() {
            $primitive = new Primitive( true );
            $primitive->addAlias( new Identifier( 'testing' ) );

            $this->assertSame( 'true AS testing', $primitive->stringify() );
        }
    }
