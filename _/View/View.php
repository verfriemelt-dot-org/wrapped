<?php

    namespace Wrapped\_\View;

    use \Exception;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\Output\Viewable;
    use \Wrapped\_\Template\Template;

    abstract class View
    implements Viewable {

        public $tplPath;

        /** @var Template */
        public $tpl;

        abstract function getTemplatePath(): string;

        public function __construct() {

            if ( empty( $this->tplPath ) ) {
                throw new Exception( "unset Template Path in view " . static::class );
            }

            $this->tpl = ( new Template )->parseFile(
                $this->getTemplatePath() . $this->tplPath
            );
        }

        /**
         * @return static
         */
        public static function create( ... $params ) {
            return (new static( ... $params ));
        }

        /**
         *
         * @param mixed $params
         * @return static
         */
        public static function make( ... $params ): string {
            return self::create( ... $params )->getContents();
        }

        public function writeDataModelProperties( $prefix, DataModel $object, $context = null ) {

            $getters = $object::fetchAnalyserObject()->fetchColumnsWithGetters();
            $context = $context ?: $this->tpl;

            foreach ( $getters as $getter ) {
                $context->set( $prefix . ucfirst( $getter["column"] ), $object->{$getter["getter"]}() );
            }
        }

        /**
         * this functions prepares the template, so that we can move the heavy
         * lifting out of the constructor
         */
        abstract protected function prepare();

        public function getContents() {
            $this->prepare();
            return $this->tpl->run();
        }

        public function yieldContents() {
            $this->prepare();
            yield $this->tpl->yieldRun();
        }

    }
