#!/bin/bash

source ./init.sh
url="$URL/system/user/logoff"
curl -i -H "$CONT" -X GET $url

