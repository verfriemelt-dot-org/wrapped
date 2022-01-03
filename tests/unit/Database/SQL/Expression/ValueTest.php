<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;
    use \verfriemelt\wrapped\_\DateTime\DateTime;

    class ValueTest
    extends TestCase {

        public function testWrapping(): void {

            $time = new DateTime;

            $tests = [
                "1"                              => 1,
                "'5'"                            => "5",
                "NULL"                           => null,
                "false"                          => false,
                "true"                           => true,
                "{}"                             => [],
                "{1,2,3}"                        => [ 1, 2, 3 ],
                "{'1','2','3'}"                  => [ "1", "2", "3" ],
                "'{$time->dehydrateToString()}'" => $time,
                "{true}"                         => [ true ]
            ];

            foreach ( $tests as $exp => $input ) {
                static::assertSame( (string) $exp, (new Value( $input ) )->stringify() );
            }
        }

    }
