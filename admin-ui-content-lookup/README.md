## Admin Content / Location Lookup

The problem with the DXP admin is that if you know the content id, how do you get to the content page in the admin?

Goal:
* input the content by ID and redirect will try and go to the Admin page
* input the location by ID and redirect will try and go to the Admin page

INSTALL: 

  * copy the files into your project root
  * EDIT config/routing.yaml and include src/config_routes/ibexa_admin_ui_content_lookup.yaml

USE: you should have a new /content_lookup URL path to use and input IDs

Can also redirect via syntax:

``` 
/content_lookup/content/?content=1234
/content_lookup/location/?location=1234
```


