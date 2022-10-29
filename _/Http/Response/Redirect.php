<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

    class Redirect extends Response
    {
        private $destination;

        public function __construct($path = null)
        {
            $this->destination = $path;
            $this->temporarily();
        }

        public function send(): Response
        {
            $this->addHeader(new HttpHeader('Location', $this->destination));
            return parent::send();
        }

        /**
         * returns http 301
         *
         * @return \verfriemelt\wrapped\_\Response\Redirect
         */
        public function permanent(): Redirect
        {
            $this->setStatusCode(Http::MOVED_PERMANENTLY);
            return $this;
        }

        /**
         * @return \verfriemelt\wrapped\_\Response\Redirect
         */
        public function temporarily(): Redirect
        {
            $this->setStatusCode(Http::TEMPORARY_REDIRECT);
            return $this;
        }

        public function seeOther($to): Redirect
        {
            $this->setStatusCode(Http::SEE_OTHER);
            $this->destination = $to;
            return $this;
        }

        public function setDestination(string $path): Redirect
        {
            $this->destination = $path;
            return $this;
        }

        public static function to($path): Redirect
        {
            return new self($path);
        }
    }
