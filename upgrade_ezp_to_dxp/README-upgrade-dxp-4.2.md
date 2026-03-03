These are the steps to upgrade the DXP database. 

David B. ran the 4.2 upgrade scripts on the DB

see https://doc.ibexa.co/en/latest/update_and_migration/from_4.1/update_from_4.1/#remove-node_modules-and-yarnlock

== MAKE SURE eng-GB is not installed in any siteacess 'site'/ admin ===

== MAKE SURE to update all Ibexa content and find/replace eng-GB before importing ==

== create Role 'Editor' before migrations

INSERT INTO `ezrole` (`is_new`, `name`, `version`) VALUES ('0', 'Editor', '0');

== Make sure to FLUSH REDIS (or you will get class errors from your last DB load) ==

  redis-cli -h redis.www.prod flushall

## DO NOT RUN ibexa migrations!! 

see eng-US versions in ./src/Migrations/Ibexa/migration/*.yml 
  
  php bin/console ibexa:migrations:import

## run 'status' as needed

  php bin/console ibexa:migrations:status

## run each import (modified to be eng-US)

  php bin/console ibexa:migrations:migrate --file 000_taxonomy_content_types.yml
  php bin/console ibexa:migrations:migrate --file 001_corporate_account.yaml
  php bin/console ibexa:migrations:migrate --file 001_taxonomy_sections.yml
  php bin/console ibexa:migrations:migrate --file 002_taxonomy_content.yml
  php bin/console ibexa:migrations:migrate --file 003_taxonomy_permissions.yml
 ## NOTE: product class group should already exist so it's commented out in the .yml
  php bin/console ibexa:migrations:migrate --file 009_product_catalog.yml
  php bin/console ibexa:migrations:migrate --file 010_currencies.yml
  php bin/console ibexa:migrations:migrate --file 013_product_categories.yaml

## regenerate yarn / node

  rm -Rf node_modules
  rm -Rf yarn.lock
  yarn install

## ADMIN MANUAL WORK: ezpage / ezlayout needs to be recreated as ezlandingpage

That should be it.
The below notes are for reference.

======================


## SKIP: Install eztags from NetGen package
PHP Package installed

SKIP: david B. ran this on db upgrade

  mysql <db> < vendor/netgen/tagsbundle/bundle/Resources/sql/upgrade/mysql/2.2/dbupdate-2.1-to-2.2.sql

Install Netgen eztags https://github.com/netgen/TagsBundle/blob/master/doc/INSTALL.md

/* SKIP: david B. ran this on db upgrade
  insert language specific keywords

  insert into eztags_keyword (keyword_id,locale,language_id,keyword,`status`)
  select id, 'eng-US',3,keyword,1
  from eztags
*/
## SKIP ibexa_taxonomy_entry class attribute

NOTE: not using new 'ibexa_taxonomy_entry' class attribute identifier becuase of upgrade

## SEE SQL FIX: VAR_DIR paths
Instead of changing the .yaml I just updated the Database (see fixes .sql )

## SEE SQL FIX: delete ezmatrix
The graphQL would not generate becuase of the ezmatrix. Deleting the ezmatrix becuase it's not supported fixed this (see fixes .sql)

## SEE SQL FIX: role / policy 'Tag' not supported 
delete the 'Tag' policy (see fixes .sql)

## grant Anonymous user/read siteaccess (client)
TODO: Manualy add this is the Admin

## DONE: log rotation using logrotate in monolog.yaml

## SEE SQL FIX: Content type integer min/max save events errors/warnings (see fixes .sql)

  BBA1 Species Account SAVE throws 2 errors
  "The value can not be lower than 100."
  "The value can not be lower than 10000."

  Quick Quiz Question SAVE ERROR    
  "The value can not be lower than 1.
  answer_pos - min:1 | max: 5 | default 0 << error