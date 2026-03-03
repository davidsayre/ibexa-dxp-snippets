
#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/dxp5/56_10_dxp5_align_schema.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/dxp5/56_20_dxp5_rebuild_ibexa_workflow_tables.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/dxp5/56_30_dxp5_rebuild_object_states.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/dxp5/56_40_dxp5_misc_fixes.sql"
docker compose exec -u www-data app /bin/bash -c "mysql --ssl=false -h db dxp -udxp -pdxp --force < upgrade/db/dxp5/56_50_dxp5_cleanup_roles.sql"
