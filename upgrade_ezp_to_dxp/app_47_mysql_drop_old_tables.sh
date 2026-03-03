#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < db/dxp4/47_dxp4_cleanup_old_tables.sql"
