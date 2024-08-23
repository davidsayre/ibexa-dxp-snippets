### Symfony Migration tips

When running a migration there are 2 parts, the import from the source and the 'migrate'. 
Import copies the source into src/Migrations/Ibexa/migrations and registered the file with the list of migrations.

There is something to know about migrations though, there is a special references file src/Migrations/Ibexa/references in your project.


"You can create custom one and load it in a migration file via"

```
-   type: reference
    mode: load
    filename: references/my_app_references.yaml
    and then use it instead as
    value: 'reference:<your_ref_name>'
```

"it's not done for Editors because the assumption is that if you have different structure than the original data, it's probably completely different and most likely you have multiple groups which need that action (edited)"
- Andrew Longosz @ Ibexa
