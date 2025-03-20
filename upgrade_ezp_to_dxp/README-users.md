The DXP user needs some Admin changes to work correcly.
For example, an upgraded 'user' content class won't have the settings needed for users to be able to update their fields.

1. edit config/ibexa_admin_ui.yaml
``` 

ibexa:
    system:
        admin_group:
            user_profile:
                enabled: true
                content_types: ['editor','user']
                field_groups: ['about', 'contact']
```

2. Edit the User content class
* Move the fields 'Image' and ' User Account' into the 'About field group'
* Move the fields 'First name' and 'last name' into 'Contact' field group
* Edit the User Account field and Check the security options as needed

NOTE: the regex example:  https://doc.ibexa.co/en/latest/users/login_methods/#login-rules

``` 
^[^@]+$
```