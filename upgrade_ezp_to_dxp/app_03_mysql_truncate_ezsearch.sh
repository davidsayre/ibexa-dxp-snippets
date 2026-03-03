#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "search tables are large, and this reduced the size of DB during upgrade"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/ez4/truncate_ezsearch.sql"
