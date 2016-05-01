<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * This file defines the HTTP status codes used in ORGA.
 */

declare(strict_types=1);
namespace ORGA\Error;

/**
 * All HTTP status codes that can be returned by the ORGA API.
 * @see http://www.restapitutorial.com/httpstatuscodes.html
 */
class HTTPStatusCode
{
    // Informational

    // CONTINUE 100
    // ...

    // Success

    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    // NON_AUTHORITATIVE_INFORMATION 203
    const NO_CONTENT = 204;
    // RESET_CONTENT 205
    // ...

    // Redirection

    // MULTIPLE_CHOICES 300
    // MOVED_PERMANENTLY 301
    // FOUND 302
    // SEE_OTHER 303
    const NOT_MODIFIED = 304;
    // USE_PROXY 305
    // ...

    // Client Error

    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    // PAYMENT_REQUIRED 402
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    // METHOD_NOT_ALLOWED 405
    // NOT_ACCEPTABLE 406
    // PROXY_AUTHENTICATION_REQUIRED 407
    // REQUEST_TIMEOUT 408
    const CONFLICT = 409;
    // GONE 410
    // LENGTH_REQUIRED 411
    // PRECONDITION_FAILED 412
    // REQUEST_ENTITY_TOO_LARGE 413
    // ...

    // Server Error

    const INTERNAL_SERVER_ERROR = 500;
    // ...
}
