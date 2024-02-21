#!/bin/bash
EZ_USER=$1
WEB_USER=$2
if [ -z $EZ_USER ]; then echo "missing EZ_USER param(1)"; exit 1; fi
if [ -z $WEB_USER ]; then echo "missing WEB_USER param(2)"; exit 1; fi

sudo chown $EZ_USER var -R
sudo su $EZ_USER -c 'export SYMFONY_ENV=prod && php -d memory_limit=1536M bin/console ezxmltext:convert-to-richtext --concurrency 2 -v'
sudo chown $WEB_USER var -R