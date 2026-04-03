## Overall process
* load raw database
* truncate ezsearch
* drop tables no longer used by ibexa (ezx_ ..)
* Run database update scripts from v2 to v3
* Run Missing tables fix
* backup database
* boot php7.4 + ezplatform v3 
* * delete all drafts
* * run richtext update
* backup database
* run more database upgrades from v3 to v4
* run db upgrades from v4 to v5??


The main issue with upgrades is the conversion of the ezxmltext to the ezrichtext

That command is ONLY available at a certain production version and no later

the latest version that has 'ezxmltext:convert-to-richtext' is ?

A known version is v3 running on php7.4: 

Becuase of security issues, set composer.json to allow insecure
```
"config": {
    "audit": {
        "block-insecure": false
    }
}
```

composer require --update-with-all-dependencies "ezsystems/ezplatform-xmltext-fieldtype"
