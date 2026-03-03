#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "DJS app:delete-content-missing-location"
docker compose exec -u www-data app /bin/bash -c "bin/console app:delete-content-missing-location --delete-confirm 1 --limit 9999"
