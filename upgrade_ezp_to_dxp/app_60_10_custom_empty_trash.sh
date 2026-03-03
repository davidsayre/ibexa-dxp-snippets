#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "DJS app:empty-trash"
docker compose exec -u www-data app /bin/bash -c "bin/console app:empty-trash --limit=9999 --save=1"
