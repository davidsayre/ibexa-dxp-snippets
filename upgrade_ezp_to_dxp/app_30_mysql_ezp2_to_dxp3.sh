#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ezplatform-2.5-to-ibexa-3.3.0.sql"
# NO: docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-2.5.latest-to-4.0.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.0-to-3.3.1.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.1-to-3.3.2.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.6-to-3.3.7.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.8-to-3.3.9.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.24-to-3.3.25.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.33-to-3.3.34.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < /app/vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.34-to-3.3.35.sql"

echo "recommend mysqldump after completed"
# PAUSE here to run richtext conversion
