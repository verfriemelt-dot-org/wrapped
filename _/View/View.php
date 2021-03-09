<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\View;

    use \Exception;
    use \verfriemelt\wrapped\_\DataModel\DataModel;
    use \verfriemelt\wrapped\_\Output\Viewable;
    use \verfriemelt\wrapped\_\Template\Template;

    abstract class View
    implements Viewable {

        public string $tplPath;

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

        public function fetchTemplateSource(): string {
            return file_get_contents( $this->getTemplatePath() . $this->tplPath );
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

            $properties = $object::createDataModelAnalyser()->fetchProperties();
            $context    = $context ?? $this->tpl;

            foreach ( $properties as $prop ) {
                $context->set( $prefix . ucfirst( $prop->getName() ), $object->{$prop->getGetter()}() );
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
