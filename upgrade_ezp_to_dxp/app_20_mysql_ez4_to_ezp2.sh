#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-5.4.0-to-6.13.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-5.4.0-to-6.4.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.1.0-to-6.4.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.10.0-to-6.11.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.11.0-to-6.12.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.13.0-to-7.5.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.13.3-to-6.13.4.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.3.0-to-6.4.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.5.0-to-6.6.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.7.0-to-6.8.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-6.7.7-to-6.7.8.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.1.0-to-7.2.0-dfs.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.1.0-to-7.2.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.2.0-to-7.3.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.4.0-to-7.5.0.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.4.4-to-7.4.5.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.5.2-to-7.5.3.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.5.4-to-7.5.5.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/ezp2/dbupdate-7.5.6-to-7.5.7.sql"

echo "recommend mysqldump after completed"
