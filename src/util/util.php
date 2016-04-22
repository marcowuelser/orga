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
    if ($str != "")
    {
        $str .= ", ";
    }
    $str .= $value;
    return $str;
}

function getShowInactiveParam(Request $request) : bool
{
    $allGetVars = $request->getQueryParams();
    foreach($allGetVars as $key => $param)
    {
        if ($key == "show_deleted")
        {
            return true;
        }
    }
    return false;
}

function getMaxCountParam(Request $request) : int
{
    $maxCount = 100;
    $allGetVars = $request->getQueryParams();
    foreach($allGetVars as $key => $param)
    {
        if ($key == "max_count")
        {
            $c = intval($param);
            if ($c > 0 && $c <= $maxCount)
            {
                return $c;
            }
        }
    }
    return $maxCount;
}

function getParentParam(Request $request) : int
{
    $allGetVars = $request->getQueryParams();
    foreach($allGetVars as $key => $param)
    {
        if ($key == "parent")
        {
            $c = intval($param);
            if ($c > 0)
            {
                return $c;
            }
        }
    }
    return -1;
}

function responseWithJson(Response $response, array $data, int $okStatusCode = 200) : Response
{
    if (isErrorResponse($data))
    {
        $response = $response->withStatus($data["http_status_code"]);
    }
    else
    {
        $response = $response->withStatus($okStatusCode);
    }

    $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
    $body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($body);
    return $response;
}

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
