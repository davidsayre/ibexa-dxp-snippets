
## Custom Dashboards

### Create dashboard section and content types
- dashboard content types are created by migration 2023_09_23_14_15_dashboard_structure.yaml as covered in README-upgrade-dxp-4.6.md
- the migration didn't set the availableBlocks for the dashboard
- to set these, log in as Administrator User and click on:
      Dashboard Type -> Edit -> Field Definitions -> Dashboard Page -> Select Blocks -> Dashboard
- add the blocks you want to be available for editors

### Permissions
- there is a migration to create permissions for the dashboard but it may only work for a fresh install
- to create permissions manually:
- log in as admin user
- create a new role called Dashboard
- add permissions:
  - Content 	Read Subtree: /Dashboards
  - Content 	Edit Subtree: /Dashboards/User Dashboards   Owner: Self
  - Content 	Publish Subtree: /Dashboards/User Dashboards  Owner: Self
  - Content 	Versionread Subtree: /Dashboards  Owner: Self
  - Activity Log 	Read (if you want to see the activity log)

- assign the role to MA Editors, MA Admins
- the migration is 2023_10_10_16_14_dashboard_permissions.yaml but we didn't use it
