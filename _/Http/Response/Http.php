<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Http\Response;

    class Http {

        Const
            CONTINUE = 100,
            SWITCHING_PROTOCOLS = 101,
            PROCESSING = 102,
            OK = 200,
            CREATED = 201,
            ACCEPTED = 202,
            NON_AUTHORITATIVE_INFORMATION = 203,
            NO_CONTENT = 204,
            RESET_CONTENT = 205,
            PARTIAL_CONTENT = 206,
            MULTI_STATUS = 207,
            ALREADY_REPORTED = 208,
            IM_USED = 226,
            MULTIPLE_CHOICES = 300,
            MOVED_PERMANENTLY = 301,
            FOUND = 302,
            SEE_OTHER = 303,
            NOT_MODIFIED = 304,
            USE_PROXY = 305,
            TEMPORARY_REDIRECT = 307,
            PERMANENT_REDIRECT = 308,
            BAD_REQUEST = 400,
            UNAUTHORIZED = 401,
            PAYMENT_REQUIRED = 402,
            FORBIDDEN = 403,
            NOT_FOUND = 404,
            METHOD_NOT_ALLOWED = 405,
            NOT_ACCEPTABLE = 406,
            PROXY_AUTHENTICATION_REQUIRED = 407,
            REQUEST_TIMEOUT = 408,
            CONFLICT = 409,
            GONE = 410,
            LENGTH_REQUIRED = 411,
            PRECONDITION_FAILED = 412,
            PAYLOAD_TOO_LARGE = 413,
            URI_TOO_LONG = 414,
            UNSUPPORTED_MEDIA_TYPE = 415,
            RANGE_NOT_SATISFIABLE = 416,
            EXPECTATION_FAILED = 417,
            IM_A_TEAPOT = 418,
            UNPROCESSABLE_ENTITY = 422,
            LOCKED = 423,
            FAILED_DEPENDENCY = 424,
            RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425,
            UPGRADE_REQUIRED = 426,
            PRECONDITION_REQUIRED = 428,
            TOO_MANY_REQUESTS = 429,
            REQUEST_HEADER_FIELDS_TOO_LARGE = 431,
            UNAVAILABLE_FOR_LEGAL_REASONS = 451,
            INTERNAL_SERVER_ERROR = 500,
            NOT_IMPLEMENTED = 501,
            BAD_GATEWAY = 502,
            SERVICE_UNAVAILABLE = 503,
            GATEWAY_TIMEOUT = 504,
            HTTP_VERSION_NOT_SUPPORTED = 505,
            VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506,
            INSUFFICIENT_STORAGE = 507,
            LOOP_DETECTED = 508,
            NOT_EXTENDED = 510,
            NETWORK_AUTHENTICATION_REQUIRED = 511;

        Const STATUS_TEXT = [
            self::CONTINUE                                                  => 'Continue',
            self::SWITCHING_PROTOCOLS                                       => 'Switching Protocols',
            self::PROCESSING                                                => 'Processing',
            self::OK                                                        => 'OK',
            self::CREATED                                                   => 'Created',
            self::ACCEPTED                                                  => 'Accepted',
            self::NON_AUTHORITATIVE_INFORMATION                             => 'Non-Authoritative Information',
            self::NO_CONTENT                                                => 'No Content',
            self::RESET_CONTENT                                             => 'Reset Content',
            self::PARTIAL_CONTENT                                           => 'Partial Content',
            self::MULTI_STATUS                                              => 'Multi-Status',
            self::ALREADY_REPORTED                                          => 'Already Reported',
            self::IM_USED                                                   => 'IM Used',
            self::MULTIPLE_CHOICES                                          => 'Multiple Choices',
            self::MOVED_PERMANENTLY                                         => 'Moved Permanently',
            self::FOUND                                                     => 'Found',
            self::SEE_OTHER                                                 => 'See Other',
            self::NOT_MODIFIED                                              => 'Not Modified',
            self::USE_PROXY                                                 => 'Use Proxy',
            self::TEMPORARY_REDIRECT                                        => 'Temporary Redirect',
            self::PERMANENT_REDIRECT                                        => 'Permanent Redirect',
            self::BAD_REQUEST                                               => 'Bad Request',
            self::UNAUTHORIZED                                              => 'Unauthorized',
            self::PAYMENT_REQUIRED                                          => 'Payment Required',
            self::FORBIDDEN                                                 => 'Forbidden',
            self::NOT_FOUND                                                 => 'Not Found',
            self::METHOD_NOT_ALLOWED                                        => 'Method Not Allowed',
            self::NOT_ACCEPTABLE                                            => 'Not Acceptable',
            self::PROXY_AUTHENTICATION_REQUIRED                             => 'Proxy Authentication Required',
            self::REQUEST_TIMEOUT                                           => 'Request Timeout',
            self::CONFLICT                                                  => 'Conflict',
            self::GONE                                                      => 'Gone',
            self::LENGTH_REQUIRED                                           => 'Length Required',
            self::PRECONDITION_FAILED                                       => 'Precondition Failed',
            self::PAYLOAD_TOO_LARGE                                         => 'Payload Too Large',
            self::URI_TOO_LONG                                              => 'URI Too Long',
            self::UNSUPPORTED_MEDIA_TYPE                                    => 'Unsupported Media Type',
            self::RANGE_NOT_SATISFIABLE                                     => 'Range Not Satisfiable',
            self::EXPECTATION_FAILED                                        => 'Expectation Failed',
            self::IM_A_TEAPOT                                               => 'I\'m a teapot',
            self::UNPROCESSABLE_ENTITY                                      => 'Unprocessable Entity',
            self::LOCKED                                                    => 'Locked',
            self::FAILED_DEPENDENCY                                         => 'Failed Dependency',
            self::RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL => 'Reserved for WebDAV advanced collections expired proposal',
            self::UPGRADE_REQUIRED                                          => 'Upgrade Required',
            self::PRECONDITION_REQUIRED                                     => 'Precondition Required',
            self::TOO_MANY_REQUESTS                                         => 'Too Many Requests',
            self::REQUEST_HEADER_FIELDS_TOO_LARGE                           => 'Request Header Fields Too Large',
            self::UNAVAILABLE_FOR_LEGAL_REASONS                             => 'Unavailable For Legal Reasons',
            self::INTERNAL_SERVER_ERROR                                     => 'Internal Server Error',
            self::NOT_IMPLEMENTED                                           => 'Not Implemented',
            self::BAD_GATEWAY                                               => 'Bad Gateway',
            self::SERVICE_UNAVAILABLE                                       => 'Service Unavailable',
            self::GATEWAY_TIMEOUT                                           => 'Gateway Timeout',
            self::HTTP_VERSION_NOT_SUPPORTED                                => 'HTTP Version Not Supported',
            self::VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL                      => 'Variant Also Negotiates (Experimental)',
            self::INSUFFICIENT_STORAGE                                      => 'Insufficient Storage',
            self::LOOP_DETECTED                                             => 'Loop Detected',
            self::NOT_EXTENDED                                              => 'Not Extended',
            self::NETWORK_AUTHENTICATION_REQUIRED                           => 'Network Authentication Required'
        ];

    }
