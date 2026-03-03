## upgrade 4.5.3 to 4.6.1

### PROD/QA: Upgrade Node JS before proceeding or Yarn will fail

```
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | sudo gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

NODE_MAJOR=18
echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | sudo tee /etc/apt/sources.list.d/nodesource.list

sudo apt-get update
sudo apt-get install nodejs -y
```

### 1 -time
```
composer require ibexa/experience:4.5.5 --with-all-dependencies --no-scripts
composer require ibexa/experience:4.6.1 --with-all-dependencies --no-scripts
composer recipes:install ibexa/experience --force -v
yarn add @ckeditor/ckeditor5-alignment@^40.1.0 @ckeditor/ckeditor5-build-inline@^40.1.0 @ckeditor/ckeditor5-dev-utils@^39.0.0 @ckeditor/ckeditor5-widget@^40.1.0 @ckeditor/ckeditor5-theme-lark@^40.1.0 @ckeditor/ckeditor5-code-block@^40.1.0
```

https://doc.ibexa.co/en/latest/update_and_migration/from_4.5/update_from_4.5/#update-the-database

### PROD/QA: database updates
```
mysql -u <username> -p <password> <database_name> < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.3-to-4.5.4.sql
mysql -u <username> -p <password> <database_name> < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.5-to-4.5.6.sql
mysql -u <username> -p <password> <database_name> < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.5.latest-to-4.6.0.sql
mysql -u <username> -p <password> <database_name> < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.1-to-4.6.2.sql
mysql -u <username> -p <password> <database_name> < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.3-to-4.6.4.sql
```
* IMPORTANT! You will have to manually re-merge the packages.json
* IMPORTANT! You will have to manually re-merge the composer.json
* MUST CUSTOMIZE migration to eng-US before running
* IMPORTANT: edit the migrations to eng-US before running
* IMPORTANT: run the migrations on production FIRST so the new content is created (if the permissions fails that's fine)
* Once these are on prod the new content/location is special remote_id's should be there and then can be added into the ibexa_*.yaml

## PROD/QA: PAUSE: turn off elasticsearch!!!!!
```
nano .env.local
#SEARCH_ENGINE=elasticsearch
```

### 1-time deploy DXP 4.6 code and run composer
```
ssh prod
cd /var/www/dxp
git fetch origin dxp4.6:dxp4.6 dxp4.6
git checkout dxp4.6
composer install --nodev
```

### 1-Time Create migrations (DEV and git commit)

```
# ON DEV
sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:import vendor/ibexa/dashboard/src/bundle/Resources/migrations/structure.yaml --name=2023_09_23_14_15_dashboard_structure.yaml'
sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:import vendor/ibexa/dashboard/src/bundle/Resources/migrations/permissions.yaml --name=2023_10_10_16_14_dashboard_permissions.yaml'
```

## 1-Time "IMPORTANT!! edit the src/Migrations/Ibexa/migrations/*.yaml replace eng-GB with eng-US

```
# ON DEV
php bin/console ibexa:migrations:migrate --file=2023_09_23_14_15_dashboard_structure.yaml --file=2023_10_10_16_14_dashboard_permissions.yaml
```

### PROD/QA: try and run the migrations (eng-US)
NOTE: if this fails have to figure it out.

```
# ON PROD (already in src/Migrations/migrations
sudo rm var/cache/* -Rf
./dxp_scripts/redis_cache_clear.sh

# NOTE: this might not set the availableBlocks for the dashboard though it seems like it should - see README-dashboards.md
sudo su www-data -s bin/bash -c 'php bin/console ibexa:migrations:migrate --file=2023_09_23_14_15_dashboard_structure.yaml'

#SKIP and manually create a ROLE / assign
# sudo su www-data -s bin/bash -c 'php bin/console ibexa:migrations:migrate --file=2023_10_10_16_14_dashboard_permissions.yaml'

# NOTE: these do not have eng-GB to replace so OK to run
# Auto create dashboards but only works in a FRESH install
#sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:import vendor/ibexa/activity-log/src/bundle/Resources/migrations/dashboard_structure.yaml --name=2023_12_04_13_34_activity_log_dashboard_structure.yaml'
#sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:import vendor/ibexa/personalization/src/bundle/Resources/migrations/dashboard_structure.yaml --name=2023_12_05_17_00_personalization_dashboard_structure.yaml'
#sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:import vendor/ibexa/product-catalog/src/bundle/Resources/migrations/dashboard_structure.yaml --name=2023_11_20_21_32_product_catalog_dashboard_structure.yaml'
#sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:migrate --file=2023_12_04_13_34_activity_log_dashboard_structure.yaml'
#sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:migrate --file=2023_12_05_17_00_personalization_dashboard_structure.yaml'
#sudo su www-data -s /bin/bash -c 'php bin/console ibexa:migrations:migrate --file=2023_11_20_21_32_product_catalog_dashboard_structure.yaml'
```

### PROD/QA: Test the new Admin after compiling ENCORE

``` 
./dxp_scripts/encore_compile.sh
```

### PROD/QA: AFTER upgrading PROD servers, be sure to re-enable ELASTICSEARCH and override schema / reindex content

```
# re-enable Elastic search 
nano .env.local
SEARCH_ENGINE=elasticsearch
``` 
### PROD/QA: Check database to see if running search on legacy / db has caused any issues
    
> show full processlist;

- if there are any long running search related queries consider killing them
- empty these tables
```
> truncate ezsearch_object_word_link;
> truncate ezsearch_search_phrase;
> truncate ezsearch_word;
```

### PROD/QA: This can take a while
```
php bin/console ibexa:elasticsearch:put-index-template --overwrite
php bin/console ibexa:reindex
```

## PROD/QA: new Dashboard permissions must be assigned to the Editors / Admins

* grant content/read on subtree (dashboard folder)
* grant content/read on node (dashboard folder)
* grant content/read on subtree (user dashboard folder)
* grant content/read on node (user dashboard folder)
* grant content/versionread on subtree (user dashboard folder) where owner
* grant content/edit on subtree (user dashboard folder) where owner
* grant content/publish on subtree (user dashboard folder) where owner

NOTE: focus mode was turned on by default, may need to fix that

* View tab displaying site preview within the context
* Hidden Technical details tab
* Hidden Locations and Versions tabs in Content items


==================
 QA Upgrade steps

* ssh into QA

You will need to completely recreate the QA database to be sure all the script do indeed work

* ssh> mysqldump qa_dxp -h -u ** -p**** > qa_dxp-last.sql
* QA mysql> drop database qa_dxp;
* QA mysql> create database qa_dxp;
* #mysql permissions should work / remain

You will need to create a .ssh/config for 'prod' for this rsync to work. And have the SSH keys setup

```
rsync -av prod:/var/www/dxp/public/var/site/ qa:/var/www/dxp/public/var/site/*
``` 

``` 
cd /var/www/dxp
git pull origin dxp4.6
``` 

#### Now for the upgrade QA steps
* Review the steps above (start of file)
* SKIP anything with 1-time
* DO anything with PROD/QA: indicating step needs to be run (in sequence)
* Test the qa.massaudubon.org