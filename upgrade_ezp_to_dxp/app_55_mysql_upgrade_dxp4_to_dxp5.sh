#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "SQL upgrade to DXP 5 [begin]"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.latest-to-5.0.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-5.0.2-to-5.0.3.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-5.0.4-to-5.0.5.sql"
echo "SQL upgrade to DXP 5 [end]"
echo "recommend mysqldump after completed"




