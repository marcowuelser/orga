<?php
/**
 * ORGA Server
 * @link https://github.com/marcowuelser/orga_server
 * @copyright (c) 2016 @author Marco Wuelser @license MIT (see /LICENSE)
 *
 * This file contains global utility methods.
 *
 * TODO: Move the response and request utilities to a dedicated header.
 */

declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Prints the contents of the variable recursive.
 * Places the output inside a HTML \<pre\> tag.
 * @param $val  The variable to dump.
 * @return void
 */
function print_r2($val)
{
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
}

/**
 * Adds the given value to the string.
 * Adds comas to separate the entries.
 * @param  $str    The string to add the value to.
 * @param  $value  The value to add.
 * @return The concatenated string.
 */
function concatenate(string $str, string $value)
{
    if ($value == "")
    {
        return $str;
    }
    if ($str != "")
    {
        $str .= ", ";
    }
    $str .= $value;
    return $str;
}

/**
 * Checks for a parameter in the HTTP query string.
 * @param $request  The received request.
 * @param $name     The name of the parameter.
 * @return True if the parameter is set in the request, false otherwise.
 */
function isRequestParameter(Request $request, string $name) : bool
{
    $allGetVars = $request->getQueryParams();
    foreach($allGetVars as $key => $param)
    {
        if ($key == $name)
        {
            return true;
        }
    }
    return false;
}

/**
 * Gets a parameter from the HTTP query string.
 * @param $request  The received request.
 * @param $name     The name of the parameter.
 * @return The value of the parameter or an empty string.
 */
function getRequestParameter(Request $request, string $name) : string
{
    $allGetVars = $request->getQueryParams();
    foreach($allGetVars as $key => $param)
    {
        if ($key == $name)
        {
            return $param;
        }
    }
    return "";
}

/**
 * Gets the show_deleted parameter from the HTTP query string.
 * @param $request  The received request.
 * @return True if the parameter is set in the request, false otherwise.
 */
function getShowInactiveParam(Request $request) : bool
{
    return isRequestParameter($request, "show_deleted");
}

/**
 * Gets the max_count parameter from the HTTP query string.
 * @param $request  The received request.
 * @param $maxCount  The value is limited to this value.
 * @return A value between 1 and maxCount.
 */
function getMaxCountParam(Request $request, $maxCount = 100) : int
{
    $value = getRequestParameter($request, "max_count");
    if ($value == "")
    {
        return $maxCount;
    }
    $c = intval($param);
    if ($c <= 0 || $c > $maxCount)
    {
        return $maxCount;
    }
    return $c;
}

/**
 * Gets the parent_id parameter from the HTTP query string.
 * @param $request  The received request.
 * @return The parent_id or -1 if none.
 */
function getParentParam(Request $request) : int
{
    $value = getRequestParameter($request, "parent_id");
    if ($value == "")
    {
        return -1;
    }
    $c = intval($param);
    if ($c < 0)
    {
        return -1;
    }
    return $c;
}

/**
 * Creates a JSON response.
 * @param $response      The response object (read only).
 * @param $data          The data array to add to the response as body.
 * @param $okStatusCode  The HTTP status code to use if the data does not
 *                       contain an "error" and "http_status_code" field.
 * @return The changed response object.
 */
function responseWithJson(Response $response, array $data, int $okStatusCode = 200) : Response
{
    $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');

    if (isErrorResponse($data))
    {
        $response = $response->withStatus($data["http_status_code"]);
    }
    else
    {
        $response = $response->withStatus($okStatusCode);
    }

    $body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($body);
    return $response;
}

/**
 * Checks if the array represents an error response.
 * @param $data  The data array to check.
 * @return True if the data array contains and "error" field, false otherwise.
 */
function isErrorResponse(array $data) : bool
{
    if (is_array($data))
    {
        if (array_key_exists("error", $data))
        {
            return true;
        }
    }
    return false;
}

?>
