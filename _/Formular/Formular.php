<?php

    namespace Wrapped\_\Formular;

    use \Wrapped\_\Exception\Input\InputException;
    use \Wrapped\_\Formular\FormTypes\Button;
    use \Wrapped\_\Formular\FormTypes\Checkbox;
    use \Wrapped\_\Formular\FormTypes\Hidden;
    use \Wrapped\_\Formular\FormTypes\Password;
    use \Wrapped\_\Formular\FormTypes\Select;
    use \Wrapped\_\Formular\FormTypes\Text;
    use \Wrapped\_\Formular\FormTypes\Textarea;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Input\Filter;
    use \Wrapped\_\Output\Viewable;
    use \Wrapped\_\Session\Session;
    use \Wrapped\_\Template\Template;

    class Formular
    implements Viewable {

        const METHOD_POST = "POST";
        const METHOD_GET  = "GET";

        /** @var Filter */
        private $filter;
        private $elements          = [];
        private $method            = SELF::METHOD_POST;
        private $cssClass          = "";
        private $cssId             = "";
        private $action;
        private $formname;
        private $csrfTokenName;
        private $storeValuesOnFail = false;

        private function generateCSRF() {

            $token = md5( rand( 1, 10000000 ) . $this->formname . microtime( 1 ) );
            Session::getInstance()->set( $this->csrfTokenName, $token );

            return $token;
        }

        public function __construct( string $name, Filter $filter = null, Template $template = null, Session $session = null ) {

            $this->formname      = $name;
            $this->filter        = $filter ?? new Filter( "Form-" . $this->formname );
            $this->csrfTokenName = "csrf-" . md5( $this->formname );

            if ( !Session::getInstance()->get( $this->csrfTokenName ) ) {
                $token = $this->generateCSRF();
            } else {
                $token = Session::getInstance()->get( $this->csrfTokenName );
            }

            $this->addHidden( "_csrf", $token );
            $this->addHidden( "_form", $this->formname );

            if ( !$template ) {
                $this->tpl = new Template();
                $this->tpl->parseFile( __DIR__ . "/Template/Formular.tpl.php" );
            } else {
                $this->tpl = $template;
            }

            $this->action = Request::getInstance()->uri();
        }

        public function setCssClass( $cssClass ): Formular {
            $this->cssClass = $cssClass;
            return $this;
        }

        public function setCssId( $id ): Formular {
            $this->cssId = $id;
            return $this;
        }

        public function action( $path ): Formular {
            $this->action = $path;
            return $this;
        }

        public function addText( $name, $value = null ): Text {

            $input = new Text( $name );
            $input->setValue( $value );
            $input->setFilterItem( $this->filter->request()->has( $name ) );

            $this->elements[$name] = $input;

            return $input;
        }

        public function addPassword( $name, $value = null ) {
            $input = new Password( $name );
            $input->setValue( $value );
            $input->setFilterItem( $this->filter->request()->has( $name ) );

            $this->elements[$name] = $input;

            return $input;
        }

        public function addHidden( $name, $value = null ): Hidden {

            $filter = $this->filter->request()->has( $name );

            if ( $value !== null ) {
                $filter->allowedValues( [ $value ] );
            }

            $input = new Hidden( $name );
            $input->setValue( $value );
            $input->setFilterItem( $filter );

            $this->elements[$name] = $input;

            return $input;
        }

        public function addButton( $name, $value = null ) {

            $button                = new Button( $name );
            $button->setValue( $value );
            $this->elements[$name] = $button;

            $button->setFilterItem( $this->filter->request()->has( $name ) );

            return $button;
        }

        public function addCheckbox( $name, $value = null ) {

            $checkbox              = new Checkbox( $name, $value );
            $this->elements[$name] = $checkbox;

            $checkbox->setFilterItem( $this->filter->request()->has( $name ) );

            return $checkbox;
        }

        public function addSelect( $name, $value = null ) {

            $select = new Select( $name, $value );
            $select->setFilterItem( $this->filter->request()->has( $name ) );

            $this->elements[$name] = $select;

            return $select;
        }

        public function addTextarea( $name, $value = null ) {

            $input = new Textarea( $name );
            $input->setValue( $value );
            $input->setFilterItem( $this->filter->request()->has( $name ) );

            $this->elements[$name] = $input;

            return $input;
        }

        public function addSubmit( $value ) {
            return $this->addButton( "submit", $value )->type( "submit" )->setOptional();
        }

        public function storeValuesOnFail( $bool = true ): Formular {
            $this->storeValuesOnFail = $bool;
            return $this;
        }

        public function fetchHtml(): string {

            $r = $this->tpl->createRepeater( "elements" );
            $this->tpl->set( "method", $this->method );
            $this->tpl->set( "action", $this->action );
            $this->tpl->set( "cssClass", $this->cssClass );
            $this->tpl->set( "cssId", $this->cssId );

            foreach ( $this->elements as $element ) {
                $r->set( "element", $element->fetchHtml() );
                $r->save();
            }

            return $this->tpl->run();
        }

        /**
         * switches between post and get method
         * @param type $method
         * @return Formular
         */
        public function setMethod( $method ): Formular {

            if ( !in_array( $method, [ SELF::METHOD_GET, SELF::METHOD_POST ] ) ) {
                return false;
            }

            $this->method = $method;
            return $this;
        }

        public function isPosted() {
            return Request::getInstance()->request()->get( "_form" ) == $this->formname;
        }

        public function get( $name ) {

            $input = ($this->method == SELF::METHOD_POST ) ?
                Request::getInstance()->request() :
                Request::getInstance()->query();

            return $input->get( $name, null );
        }

        private function preFillFormWithSendData() {

            foreach ( $this->elements as $element ) {

                $data = $this->get( $element->name );

                if ( is_string( $data ) ) {
                    $element->setValue( $this->get( $element->name ) );
                }
            }

            return $this;
        }

        public function hasValidated() {
            $validated = false;

            if (
                $this->method == SELF::METHOD_POST && Request::getInstance()->requestMethod() == "POST" ||
                $this->method == SELF::METHOD_GET ) {

                $failed = false;

                foreach ( $this->elements as $element ) {

                    try {
                        $element->getFilterItem()->validate();
                    } catch ( InputException $e ) {
                        $failed = true;
                        $element->addCssClass( "input-error" );
                    }
                }

                if ( $this->storeValuesOnFail && $failed ) {
                    $this->preFillFormWithSendData();
                }

                $this->addHidden( "_csrf", $this->generateCSRF() );

                $validated = !$failed;
            }

            return $validated;
        }

        public function getContents(): string {
            return $this->fetchHtml();
        }

    }
