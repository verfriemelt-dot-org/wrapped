<?php

    namespace Wrapped\_\Http\Request;

    use \Wrapped\_\Http\ParameterBag;

    class Request {

        /**
         * @var ParameterBag
         */
        private $request, $query, $attributes, $cookies, $server, $files, $content;
        protected static $instance;

        public function __construct(
        array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null
        ) {

            $this->initialize( $query, $request, $attributes, $cookies, $files, $server, $content );
        }

        private function initialize(
        array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null
        ) {

            $this->request    = new ParameterBag( $request );
            $this->query      = new ParameterBag( $query );
            $this->attributes = new ParameterBag( $attributes );
            $this->cookies    = new ParameterBag( $cookies );
            $this->server     = new ParameterBag( $server );
            $this->files      = new ParameterBag( $files );

            $contents = json_decode( $content );

            if ( json_last_error() !== JSON_ERROR_NONE ) {
                parse_str( $content, $contents );
            }

            $this->content = new ParameterBag( (array)$contents );
        }

        /**
         * get parameters
         * @return ParameterBag
         */
        public function query() {
            return $this->query;
        }

        /**
         * post parameters
         * @return ParameterBag
         */
        public function request() {
            return $this->request;
        }

        /**
         * cookies
         * @return ParameterBag
         */
        public function cookies() {
            return $this->cookies;
        }

        /**
         * $_SERVER variable
         * @return ParameterBag
         */
        public function server() {
            return $this->server;
        }

        /**
         * $_FILES
         * @return ParameterBag
         */
        public function files() {
            return $this->files;
        }

        /**
         * rawdata; $_POST
         * @return ParameterBag
         */
        public function content() {
            return $this->content;
        }

        /**
         *
         * @return static
         */
        public static function createFromGlobals() {
            return new static(
                $_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, file_get_contents( "php://input" )
            );
        }

        /**
         *
         * @return static
         */
        public static function getInstance() {

            if ( self::$instance === null ) {
                self::$instance = static::createFromGlobals();
            }

            return self::$instance;
        }

        public static function overrideInstance( Request $request ) {
            return static::$instance = $request;
        }

        /**
         * tells wether this is an https request
         * @return bool
         */
        public function secure() {
            return $this->server()->is( "HTTPS", "on" );
        }

        /**
         *
         * @return string
         */
        public function uri() {
            return $this->server->get( "REQUEST_URI" );
        }

        public function uriWithoutQueryString() {
            return explode( "?", $this->uri() )[0];
        }

        public function pathInfo() {
            return $this->server->get( "PATH_INFO" );
        }

        public function referer() {
            return $this->server->get( "HTTP_REFERER" );
        }

        public function hostname() {
            return $this->server->get( "HTTP_HOST" );
        }

        public function ajax() {
            return strtolower( $this->server->get( "HTTP_X_REQUESTED_WITH" ) ) == "xmlhttprequest" || $this->query->has( "ajax" );
        }

        public function requestMethod() {
            return $this->server->get( "REQUEST_METHOD" );
        }

        public function remoteIp() {
            return $this->server->get( "REMOTE_ADDR", "127.0.0.1" );
        }

        public function remoteHost() {
            if ( $ip = $this->remoteIp() ) {
                return gethostbyaddr( $ip );
            }

            return null;
        }

        public function userAgent() {
            return $this->server->get( "HTTP_USER_AGENT" );
        }

        public function setAttributes( array $attributes ) {
            $this->attributes = new ParameterBag( $attributes );
            return $this;
        }

        /**
         *
         * @return ParameterBag
         */
        public function attributes() {
            return $this->attributes;
        }

        /**
         * merges the input vectors in that order of priority
         * GET > POST > COOKIE > RAWINPUT
         * @return ParameterBag
         */
        public function aggregate() {
            return new ParameterBag(
                $this->query->all() + $this->request->all() + $this->cookies->all() + $this->content->all()
            );
        }

        public function destroy() {
            static::$instance = null;
        }

    }
