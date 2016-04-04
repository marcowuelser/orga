#!/bin/bash

source ./init.sh
url="$URL/system/user/login"
curl -i -H "$CONT" -X GET -u $1 $url

echo "Copy auth field to $TOKEN (export TOKEN=12345)"

