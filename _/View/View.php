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
        public static $isCacheable = false;

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
        public static function make( ... $params ) {
            return (new static( ... $params ) )->getContents();
        }

        public function writeDataModelProperties( $prefix, DataModel $object, $context = null ) {

            $getters = $object::fetchAnalyserObject()->fetchColumnsWithGetters();
            $context = $context ?: $this->tpl;

            foreach ( $getters as $getter ) {
                $context->set( $prefix . ucfirst( $getter["column"] ), $object->{$getter["getter"]}() );
            }
        }

        public function getContents() {
            return $this->tpl->run();
        }

        public function yieldContents() {
            yield $this->tpl->yieldRun();
        }

    }
