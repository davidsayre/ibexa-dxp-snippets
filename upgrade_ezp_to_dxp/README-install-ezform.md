## run the SQL manually

    cat vendor/ibexa/form-builder/src/bundle/Resources/installer/ezform.sql
    cat vendor/ibexa/form-builder/src/bundle/Resources/installer/ezform_submissions.sql

    # COPY and PASTE into MYSQL and MANUALLY RUN

## create form container 'folder'

Option 1)

* Admin > Content Structure > Multisite
* Create 'Folder' called 'Forms'
* Get LocationID and put into src/config/ibexa_forms.yaml (below)

Option 2) 

    bin/console ibexa:form-builder:create-forms-container

## define YAML

    Location with ID 28586 has been created. Remember to update the YAML

    ibexa.system.<SCOPE>.form_builder.forms_location_id

This is NOT on the content tree, it's directly under node_id 2

Example parameter in your configuration. 

    ibexa:
        system:
            admin_group:
                form_builder:
                    forms_location_id: 28586

## MANUAL ADMIN WORK (REQUIRED)
* edit Content Type "form/survey" change
* add the new 'Form' field with identifier 'form
* remove all ezsurvey field
* regenerate config cache