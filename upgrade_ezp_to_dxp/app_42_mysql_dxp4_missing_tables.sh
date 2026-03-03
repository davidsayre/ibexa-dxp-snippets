#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "insert missing tables BEFORE upgrade to dxp4"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/dxp4/42_dxp4_missing_tables.sql"

