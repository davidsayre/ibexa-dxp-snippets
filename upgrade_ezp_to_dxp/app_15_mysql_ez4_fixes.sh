#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < db/ez4/ez4_fixes.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < db/ez4/ez4_fixes_ezuser.sql"
