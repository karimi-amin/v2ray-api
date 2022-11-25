#!/bin/bash

modifiedFile=$(find config.json -mmin -2)

length=${#modifiedFile}

if ((length > 0)); then
    x-ui restart
fi
