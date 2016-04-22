<?php
declare(strict_types=1);
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * This file defines the ErrorCodeList class.
 */

namespace ORGA;

/**
 * Provides all error codes of the ORGA API.
 */
class ErrorCodeList
{
    public function __construct()
    {
        add(ErrorCode::NOT_FOUND, "Specific entry not found", HTTPStatusCode::NOT_FOUND);
        add(ErrorCode::NO_DATA, "No data found", HTTPStatusCode::NOT_FOUND);
        add(ErrorCode::INVALID_REQUEST, "Invalid request", HTTPStatusCode:: BAD_REQUEST);

        add(ErrorCode::DB_ERROR, "Database exception", HTTPStatusCode::INTERNAL_SERVER_ERROR);
        add(ErrorCode::UNIMPLEMENTED, "Unimplemented", HTTPStatusCode::INTERNAL_SERVER_ERROR);

        add(ErrorCode::AUTHENTICATION_FAILED, "Authentication failed", HTTPStatusCode::UNAUTHORIZED);
        add(ErrorCode::AUTHORIZATION_FAILED, "Authorization failed", HTTPStatusCode::FORBIDDEN);
    }

    public function add(int $code, string $text, int $httpStatusCode)
    {
        $this->list[$errorCode] = new ErrorCodeData($code, $text, $httpStatusCode);
    }

    public function getData(int $code) : ErrorCodeData
    {
        if (array_key_exists($code, $this->list))
        {
            return $this->list[$code];
        }

        return $this->unexpected;
    }

    public function createResponse(int $code, string $description) : array
    {
        return $this->getData($code)->createResponseData($description);
    }

    public function createResponseData(Response $response, int $code, string $description) : array
    {
        return responseWithJson($response, $this->createResponse($code, $description));
    }

    private $list = array();

    private $unexpected = new ErrorCodeData(
        ErrorCode::UNEXPECTED,
        "Unexpected",
        HTTPStatusCode::INTERNAL_SERVER_ERROR);
}

?>
