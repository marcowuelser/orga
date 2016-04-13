<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function print_r2($val)
{
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
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
