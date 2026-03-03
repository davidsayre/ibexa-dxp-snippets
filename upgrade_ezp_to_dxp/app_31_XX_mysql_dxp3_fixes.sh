
#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

echo "backup database before and after .. major changes"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/dxp3/31_10_dxp3_fix_var_site_storage.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/dxp3/31_20_dxp3_disable_unsupported_types.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/dxp3/31_30_dxp3_add_missing_tables.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/dxp3/31_40_dxp3_fix_ezlocation.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/dxp3/31_50_dxp3_fix_richtext.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp < upgrade/db/dxp3/31_60_dxp3_fix_eztags_keywords.sql"
