## upgrade to 4.4


see https://doc.ibexa.co/en/latest/update_and_migration/from_4.3/update_from_4.3_new_commerce/#customer-portal-self-registration

## FIX: config/packages/ibexa.yaml

Fix eng-US

## FIX: config/packages/ibexa_admin_ui.yaml

Fix eng-US

## FIX: package.json

Ibexa recipe WIPES OUT all our needed packages, must manually compare and restore

## FIX: webpack.config.js

Ibexa recipe WIPES OUT all our needed config, must manually compare and restore

### DO NOT run migration from Ibexa, instead use src/Migrations/ibexa/migrations

Fix: eng-US

