<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Template\Template;

    class foo {

        public function bar(): string {
            return "epic";
        }

    }

    class TemplateCallbackTest
    extends TestCase {

        private Template $tpl;

        public function testClousure(): void {
            $this->tpl = new Template;
            $this->tpl->setRawTemplate( '{{ testingVar }}' );

            $this->tpl->set( "testingVar", function () {
                return "epic";
            } );

            static::assertSame( $this->tpl->run(), "epic" );
        }

        public function testShouldNotCallFunctions(): void {

            $this->tpl = new Template;
            $this->tpl->setRawTemplate( '{{ testingVar }}' );

            $this->tpl->set( "testingVar", "system" );

            static::assertSame( $this->tpl->run(), "system" );
        }

    }
