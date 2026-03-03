#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "DJS app:delete-untouched-drafts"
docker compose exec -u www-data app /bin/bash -c "bin/console app:delete-untouched-drafts --save=1 --limit=9999"


