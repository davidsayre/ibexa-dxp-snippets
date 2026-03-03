#!/bin/bash

echo "run from <ez root>"

DATABASE=$1
if [ -z "$DATABASE" ]; then
  echo "missing database param"
  exit 1
fi
echo $DATABASE

PARAMS="--force"

sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ezplatform-2.5-to-ibexa-3.3.0.sql"
#sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-2.5.latest-to-4.0.0.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.0-to-3.3.1.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.1-to-3.3.2.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.6-to-3.3.7.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.8-to-3.3.9.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.24-to-3.3.25.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.33-to-3.3.34.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.34-to-3.3.35.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-3.3.latest-to-4.0.0.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.0.0-to-4.1.0.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.0.3-to-4.0.4.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.1.0-to-4.1.1.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.1.5-to-4.1.6.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.1.latest-to-4.2.0.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.2.2-to-4.2.3.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.2.latest-to-4.3.0.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.4.latest-to-4.5.0.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.1-to-4.5.2.sql"
sudo su root -c "mysql $DATABASE $PARAMS < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.2-to-4.5.3.sql"