<?php

include_once('util.php');

function getErrorName($errorCode)
{
    $errorCodes = array(
                1001 => "Specific entry not found",
                1002 => "No data found",
                1003 => "Invalid request",

                2001 => "Database Exception",
                2002 => "Unimplemented",

                3001 => "Login failed",
                3002 => "Not Authorized",
    );

    if (array_key_exists($errorCode, $errorCodes))
    {
        return $errorCodes[$errorCode];
    }

    return "Unknown error";
}

// http://www.restapitutorial.com/httpstatuscodes.html
function getHttpStatusCode($errorCode)
{
    $errorCodes = array(
                1001 => 404,
                1002 => 404,
                1003 => 400, // Client Error

                2001 => 500, // Internal Server Error
                2002 => 500, // Internal Server Error

                3001 => 401, // Unauthorized
                3002 => 403, // Forbidden
    );

    if (array_key_exists($errorCode, $errorCodes))
    {
        return $errorCodes[$errorCode];
    }

    return 500;
}

function createErrorResponse($errorCode, $description)
{
    $data = array(
        "code" => $errorCode,
        "http_status_code" => getHttpStatusCode($errorCode),
        "error" => getErrorName($errorCode),
        "description" => $description,
    );
    return $data;
}

function responseWithJsonError($response, $errorCode, $description)
{
    $data = createErrorResponse($errorCode, $description);
    return responseWithJson($response, $data);
}

?>
