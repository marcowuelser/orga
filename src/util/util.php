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

    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
