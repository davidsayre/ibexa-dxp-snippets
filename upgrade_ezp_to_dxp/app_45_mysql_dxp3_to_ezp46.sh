#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.latest-to-4.0.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.0.0-to-4.1.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.0.3-to-4.0.4.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.1.0-to-4.1.1.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.1.5-to-4.1.6.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.1.latest-to-4.2.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.2.2-to-4.2.3.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.2.latest-to-4.3.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.4.latest-to-4.5.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.1-to-4.5.2.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.2-to-4.5.3.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.3-to-4.5.4.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.5-to-4.5.6.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.latest-to-4.6.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.1-to-4.6.2.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.20-to-4.6.21.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.21-to-4.6.22.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.23-to-4.6.24.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.24-to-4.6.25.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.3-to-4.6.4.sql"
echo "recommend mysqldump after completed"

# TODO 4.5 to 4.6
