<?php

function print_r2($val)
{
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
}

function responseWithJson($response, $data, $okStatusCode = 200)
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

function isErrorResponse($data)
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
