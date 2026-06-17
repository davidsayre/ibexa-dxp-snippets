### DXP 4.6.30 requires permission changes (not documented)

In order for the Admin UI to render the admin/ui/layout.html.twig line 7 and 61 which call the user's record
the permissions must be updated to allow a user to see themselves

I'm unsure why this actually works becuase the 'owner' of a user is not actually themeselves, but whatever.

Edit 'Editor' Role(s)
* grant content/read owner:self section:users


### mysql updates
```
mysql -u <username> -p <password> <database_name> < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.27-to-4.6.28.sql
mysql -u <username> -p <password> <database_name> < vendor/ibexa/installer/upgrade/db/mysql/ibexa-4.6.28-to-4.6.29.sql
```