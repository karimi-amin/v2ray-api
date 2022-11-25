#!/bin/bash

modifiedFile=$(find /usr/local/x-ui/bin/config.json -mmin -2)

length=${#modifiedFile}

if ((length > 0)); then
    x-ui restart
fi
