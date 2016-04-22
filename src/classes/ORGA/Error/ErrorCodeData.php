<?php
declare(strict_types=1);
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * This file defines the ErrorCode class.
 */

namespace ORGA;

/**
 * Definition of an ORGA API error code.
 */
class ErrorCodeData
{
    /**
     * The ORGA error code.
     */
    public $code;

    /**
     * A human readable text for the error message.
     */
    public $text;

    /**
     * The HTTP status code to use when reporting this error.
     */
    public $httpStatusCode;

    /**
     * Initializes an ErrorCodeData instance.
     */
    public function __construct($code, $text, $httpStatusCode)
    {
        $this->code = $code;
        $this->text = $text;
        $this->code = $httpStatusCode;
    }

    /**
     * Creates the response data for this error code.
     * @param description  The error description.
     * @return The data array for the API response.
     */
    public function toResponseData(string $description) : array
    {
        return array(
            "code" => $this->code,
            "http_status_code" => $this->httpStatusCode,
            "error" => $this->text,
            "description" => $description,
        );
    }
}

?>
