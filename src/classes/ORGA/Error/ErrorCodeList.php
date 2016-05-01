<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * This file defines the ErrorCodeList class.
 */

declare(strict_types=1);
namespace ORGA\Error;

use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Provides all error codes of the ORGA API.
 */
class ErrorCodeList
{
    public function __construct()
    {
        $this->unexpected = new ErrorCodeData(
            ErrorCode::UNEXPECTED,
            "Unexpected",
            HTTPStatusCode::INTERNAL_SERVER_ERROR);

        $this->add(
            ErrorCode::NOT_FOUND,
            "Specific entry not found",
            HTTPStatusCode::NOT_FOUND);
        $this->add(
            ErrorCode::NO_DATA,
            "No data found",
            HTTPStatusCode::NOT_FOUND);
        $this->add(
            ErrorCode::INVALID_REQUEST,
            "Invalid request",
            HTTPStatusCode::BAD_REQUEST);
        $this->add(
            ErrorCode::DB_ERROR,
            "Database exception",
            HTTPStatusCode::INTERNAL_SERVER_ERROR);
        $this->add(
            ErrorCode::UNIMPLEMENTED,
            "Unimplemented",
            HTTPStatusCode::INTERNAL_SERVER_ERROR);
        $this->add(
            ErrorCode::AUTHENTICATION_FAILED,
            "Authentication failed",
            HTTPStatusCode::UNAUTHORIZED);
        $this->add(
            ErrorCode::AUTHORIZATION_FAILED,
            "Authorization failed",
            HTTPStatusCode::FORBIDDEN);
    }

    /**
     * Adds a code to the list.
     * Replaces the data for an existing code.
     * @param $errorCode       The error code.
     * @param $text            The error text to return.
     * @param $httpStatusCode  The HTTP status to return.
     * @return void
     */
    public function add(int $errorCode, string $text, int $httpStatusCode)
    {
        $this->list[$errorCode] = new ErrorCodeData(
            $errorCode, $text, $httpStatusCode);
    }

    /**
     * Returns the data for the given error. If the
     * error code is not found the data for an unexpected error is returned.
     * @param $code  The error code to get the data for.
     * @return       The data for the given error code.
     */
    public function getData(int $code) : ErrorCodeData
    {
        if (array_key_exists($code, $this->list))
        {
            return $this->list[$code];
        }

        return $this->unexpected;
    }

    /**
     * Creates a SLIM response with the data for the given error code.
     * Creates a copy of the slim response with the data, the original
     * is not changed.
     * @param $response     The SLIM response to use as base.
     * @param $code         The error code to add.
     * @param $description  A text to add to the response together with the error.
     * @return              The SLIM response with the error data.
     */
    public function createResponse(
        Response $response,
        int $code,
        string $description) : Response
    {
        $data = $this->getData($code)->toResponseData($description);
        return responseWithJson($response, $data);
    }

    /**
     * The list of error codes.
     */
    private $list = array();

    /**
     * The data to return for not found codes.
     */
    private $unexpected;
}

?>
