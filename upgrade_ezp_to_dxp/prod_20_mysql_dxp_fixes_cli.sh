

#!/bin/bash

echo "run from <app root>"
echo "Requires <database> param"

DATABASE=$1
if [ -z "$DATABASE" ]; then
  echo "missing database param"
  exit 1
fi
echo $DATABASE

PARAMS="--force"

sudo su root -c "mysql $DATABASE $PARAMS < upgrade_ezp_to_dxp/db/mysql/ezp5_dxp_db_fixes.sql"
sudo su root -c "mysql $DATABASE $PARAMS < upgrade_ezp_to_dxp/db/mysql/ezp5_dxp_db_fixes_attributes.sql"
sudo su root -c "mysql $DATABASE $PARAMS < upgrade_ezp_to_dxp/db/mysql/ezp5_dxp_db_fixes_ezrichtext.sql"
sudo su root -c "mysql $DATABASE $PARAMS < upgrade_ezp_to_dxp/db/mysql/ezp5_dxp_db_fixes_ezuser.sql"