After upgrading the database and logging into the admin there are some specific Content Type changes needed

## Change Admin > Users to sort by 'Content Name' Ascending

## Move inactive users into Admin > Users > Deactivated users

## MOVE ALL ADMINS TO Site Admins (reduce permissions)
We don't want people creating things they should not

## Cleanup OLD Role policies that no longer exist
The Gray EDIT does NOT indicate invalid!!!

### ROLE: Anonymous (to copy)
```
- role.*
# HOLD: Content Create / Change Owner: -1; Content type: Corporate Account Application; Content type of Parent: Folder; Subtree: /Corporate Account Applications
Content Pdf / Section: Standard
Content Read / Content type: File; Section: Media
Content Read / Content type: File; Section: Media; Subtree: /Media/Files
# HOLD: Content Read / Content type: Folder; Location: /Corporate Account Applications
Content Read / Content type: Image; Section: Media; Subtree: /Media/Images
Content Read / Section: Products Taxonomy
Content Read / Section: Standard
# HOLD: Content Read / Section: Standard; State: Review State:Approved
Content Read / Section: Taxonomy
Content View_embed / None
Netgen Tags Read / None
Netgen Tags view / None
Section / View      # must have 'view' else error 
Taxonomy Read / Taxonomy: Product categories
Taxonomy Read / Taxonomy: Tags
User Login / SiteAccess: site
```

### ROLE: Base Editor (Policies to copy)
```
- role.*
Content	Bookmark / None
Content	Dashboard / None
Content	Diff / None
Content	Pendinglist	/ None
Content	Read / Section: Standard
Content	Read / Location: /Media
Content	Read / Location: /Multisite
Content	Read / Section: Standard
Content Read / Section: Taxonomy
Content	Translate / None
Content	Versionread / None
Content	Versionremove Owner: Self
Content	View_embed / None 
Netgen Tags	Read / None
Netgen Tags	Search / None
Netgen Tags	View / None
User Login / SiteAccess: site
User Login / SiteAccess: admin
User Password / None
User Preferences / None
User Selfedit / None
Comparison All functions / None
URL	View / none ( hold on ALL )
Section	View / None
Content	Publish / None # TBD this could be limited
Content	Reverse related list / None
Activity Log Read / Only own logs
Content	Read / Owner: Self & Section: Users
```

### ROLE: Base Editor (Policies to copy)
```
Dashboard / Customize
Content / Read Subtree: /Dashboards
Content / Edit Subtree: /Dashboards/User Dashboards   Owner: Self
Content / Publish Subtree: /Dashboards/User Dashboards  Owner: Self
Content / Versionread Subtree: /Dashboards  Owner: Self
```

### Manually Setup Admin > Dashboards > Predefined Dashboards > Default Dashboard
* You must add blocks / layout into the 'Default Dashboard' in order to be copied
  Choose layout 'Three Rows Two Columns'

#### Default blocks (but I would swap Ibexa news for 'Review Queue')
* Top: Block 'Quick Actions'
* Left: Block 'Recent Activity'
* Right: Block 'Ibexa News'
* Bottom: Block 'Review Queue'
* Bottom: Block 'Common Content'

### ROLE: Site Editor
+ Segment group
+ Segment
+ TBD: Netgen tags All (though subtree may not work)

### ROLE: Site Admin

### ROLE: Tags Admin (assign to Admins)
+ Netgen Tags	Dashboard
+ Netgen Tags	Edit
+ Netgen Tags	Id
+ Netgen Tags	Read
+ Netgen Tags	Search
+ Netgen Tags	View
+ Netgen Tags	Addsynonym
+ Netgen Tags	Editsynonym
+ Netgen Tags	Deletesynonym
+ Netgen Tags	Makesynonym
+ HOLD:  Netgen Tags Delete

### ROLE: Tags Editor (assign to Editors)
+ Netgen Tags / Dashboard
+ Netgen Tags / Edit
+ Netgen Tags / Id
+ Netgen Tags / Read
+ Netgen Tags / Search
+ Netgen Tags / View
+ Netgen Tags / Addsynonym
+ Netgen Tags / Editsynonym
+ Netgen Tags / Deletesynonym
+ Netgen Tags / Makesynonym

Check all Content Type specific permissions for Site Editors / Site Admins

+ Content create / layout page & parent content type layout page
+ Content edit / layout page
+ Content remove / layout page
+ Content publish / layout page


### Extra Steps:
* Run draft cleanup CLI
* MOVE old/unused fields into new field group 'legacy' per content Type
* Create new documentation for content entry


## Add 'ezlandingpage' field to existing classes

* content > frontpage
* content > landing_page
* content > press_listing
* content > news_listing
* etc...


### see fix var .sql

### PAGE BUILDER: You must re-edit every content type when a new block is configured :(
### PAGE BUILDER: You must regenerate graphql when a new block is configured :(

--------------------------
