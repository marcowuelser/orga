#!/bin/bash

curl -i -H "Content-Type: application/json" -X GET -u $1 http://localhost/src/orga_server/src/public/api/v1/system/user/login

