<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\DateTime\DateTime;

    class ValueTest
    extends TestCase {

        public function testInit() {
            new Value( null );
        }

        public function testWrapping() {

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
                $this->assertSame( (string) $exp, (new Value( $input ) )->stringify() );
            }
        }

    }
