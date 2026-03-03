#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "insert missing tables BEFORE upgrade to dxp5"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/dxp5/52_dxp5_missing_tables.sql"
