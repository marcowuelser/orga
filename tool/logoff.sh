#!/bin/bash

source ./init.sh
url="$URL/user/logoff"
curl -i -H "$CONT" -X GET $url

