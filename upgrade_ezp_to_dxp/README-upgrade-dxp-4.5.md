
### Upgrade from 4.4 to 4.5

see https://doc.ibexa.co/en/latest/update_and_migration/from_4.4/update_from_4.4/#update-the-database


### Migrate richtext namespaces

If you earlier upgraded from v3.3 to v4.x and haven't run the migrate script yet, do it now, run:

    php bin/console ibexa:migrate:richtext-namespaces