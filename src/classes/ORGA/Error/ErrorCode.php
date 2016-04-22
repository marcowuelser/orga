<?php
declare(strict_types=1);
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * This file defines the ORGA error codes.
 */

namespace ORGA;

/**
 * All error codes that can be returned by the ORGA API.
 */
class ErrorCode
{
    const NOT_FOUND = 1001;
    const NO_DATA = 1002;
    const INVALID_REQUEST = 1003;

    const DB_ERROR = 2001;
    const UNIMPLEMENTED = 2002;

    const AUTHENTICATION_FAILED = 3001;
    const AUTHORIZATION_FAILED = 3002;

    const UNEXPECTED = 9999;
}
