## Overall process
* load raw database
* truncate ezsearch
* drop tables no longer used by ibexa (ezx_ ..)
* Run database update scripts from v2 to v3
* Run Missing tables fix
* (backup database)
* boot php7.4 + ezplatform v3
* * empty trash
* * delete all drafts
* * run richtext update
* (backup database)
* run more database upgrades from v3 to v4
* add missing tables
* align schema to dxp4
* (backup database)
* run db upgrades from v4 to v5
* align schema to dxp5 (ex: move column 'id' to FIRST position)
* (backup database)
* run several src/Commands/Ibexa/(verify) 
  * check content has name, name has valid content
  * check content has version, version has valid content
  * check content types in groups, groups references valid content types
  * check content on tree, tree references valid content
  * check content fields references valid content
  * check content fields match content types
  * check content relations match valid content
  * etc..
* write custom SQL to fix database errors
* (backup database)




The main issue with upgrades is the conversion of the ezxmltext to the ezrichtext using DXP 3

The solution is to boot app PHP 7.4 with a ./upgrade_dxp3/ DXP3 installation connected to the SAME database as php 8.3 (app).

```
docker compose -f docker-compose.yaml -f docker-compose-app74.yaml up -d
```

Becuase of security issues, set dxp3's composer.json to allow insecure so 'composer install' works
```
"config": {
    "audit": {
        "block-insecure": false
    }
}
```

Then install the field type in dxp3
```
composer require --update-with-all-dependencies "ezsystems/ezplatform-xmltext-fieldtype"
```
