<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Http\Request;

    use \verfriemelt\wrapped\_\Http\ParameterBag;

    class Request {

        /**
         * @var ParameterBag
         */
        private ParameterBag $request;

        private ParameterBag $query;

        private ParameterBag $attributes;

        private ParameterBag $cookies;

        private ParameterBag $server;

        private ParameterBag $files;

        private ParameterBag $content;

        private ParameterBag $header;

        public function __construct(
            array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], string $content = null
        ) {

            $this->initialize( $query, $request, $attributes, $cookies, $files, $server, $content );
        }

        private function initialize(
            array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], string $content = null
        ) {

            $this->request    = new ParameterBag( $request );
            $this->query      = new ParameterBag( $query );
            $this->attributes = new ParameterBag( $attributes );
            $this->cookies    = new ParameterBag( $cookies );
            $this->server     = new ParameterBag( $server );
            $this->files      = new ParameterBag( $files );

            if ( $content !== null ) {
                $contents = json_decode( $content );

                if ( json_last_error() !== JSON_ERROR_NONE ) {
                    parse_str( $content, $contents );
                }

                $this->content = new ParameterBag( (array) $contents );
                $this->content->setRawData( $content );
            } else {
                $this->content = new ParameterBag( [] );
            }

            $header = [];

            foreach ( $_SERVER as $key => $value ) {

                if ( substr( $key, 0, 5 ) !== 'HTTP_' ) {
                    continue;
                }

                $header[$key] = $value;
            }

            $this->header = new ParameterBag( $header );
        }

        /**
         * get parameters
         * @return ParameterBag
         */
        public function query(): ParameterBag {
            return $this->query;
        }

        /**
         * post parameters
         * @return ParameterBag
         */
        public function request(): ParameterBag {
            return $this->request;
        }

        /**
         * cookies
         * @return ParameterBag
         */
        public function cookies(): ParameterBag {
            return $this->cookies;
        }

        /**
         * $_SERVER variable
         * @return ParameterBag
         */
        public function server(): ParameterBag {
            return $this->server;
        }

        /**
         * $_FILES
         * @return ParameterBag
         */
        public function files(): ParameterBag {
            return $this->files;
        }

        /**
         * rawdata; $_POST
         * @return ParameterBag
         */
        public function content(): ParameterBag {
            return $this->content;
        }

        /**
         * HTTP_ header parsed from server
         * @return ParameterBag
         */
        public function header(): ParameterBag {
            return $this->header;
        }

        /**
         *
         * @return static
         */
        public static function createFromGlobals() {
            return new self(
                $_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, file_get_contents( "php://input" )
            );
        }

        /**
         * tells wether this is an https request
         * @return bool
         */
        public function secure() {
            return $this->server()->is( "HTTPS", "on" );
        }

        /**
         * The URI which was given in order to access this page; for instance, '/index.html'.
         * @return string
         */
        public function uri() {
            return $this->server->get( "REQUEST_URI" );
        }

        public function uriWithoutQueryString() {
            return explode( "?", $this->uri() )[0];
        }

        /**
         * Contains any client-provided pathname information trailing the actual
         * script filename but preceding the query string, if available. For instance,
         * if the current script was accessed via the
         * URL http://www.example.com/php/path_info.php/some/stuff?foo=bar,
         * then $_SERVER['PATH_INFO'] would contain /some/stuff
         *
         * @return type
         */
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

    }
