After upgrading the database and logging into the admin there are some specific Content Type changes needed

## Change Admin > Users to sort by 'Content Name' Ascending

## Move inactive users into Admin > Users > Deactivated users

## MOVE ALL ADMINS TO Site Admins (reduce permissions)
We don't want people creating things they should not

## Cleanup OLD Role policies that no longer exist
The Gray EDIT does NOT indicate invalid!!!

### ROLE: Base Editor

+ Comparison > all
- role.*
- Content / Bookmark
+ user login (admin,site,ma_user)
+ user login (site,ma_user)  
+ URL all
+ section 'view'

### ROLE: Site Editor

+ Segment group
+ Segment
TBD: Netgen tags All (though subtree may not work)

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

Since layout page is the new default content page, grant to editors / admins

+ Content create layout page under layout page
+ Content edit layout page
+ Content remove layout page
+ Content publish layout page

### ROLE: Site Admin 

* Anonymous
  - role.*
  + Netgen tags Read
  + section ALL << must have 'view'

   
HOLD
* Company Application
* Personalization (subscription)

* Run draft cleanup
* new nav button to preview on front end
* System URL url hyperlinked to dxp.massaudubon.org
* every 6 hours database export

* RENAME landing to layout
* ROLE: content page remove permission content > create
* MOVE lots of fields into new field group 'legacy'
* Rename body > body (legacy)

* documentation - page builder blocks (examples)
* documentation - content_page / legacy fields

## DECOM ROLE: Promo Admin
Remove Promo Admin permission from MA Admin
Add new Promo Archiver which has
+ Edit Promo *
+ Remove Promo *
+ hide Promo *

## Add pagebuilder to these existing classes

* content > frontpage  http://localhost:42080/admin/contenttypegroup/1/contenttype/38
* content > landing_page http://localhost:42080/admin/contenttypegroup/1/contenttype/21  (careful errors / large / SQL fixes )
* content > press_listing http://localhost:42080/admin/contenttypegroup/1/contenttype/61
* content > news_listing http://localhost:42080/admin/contenttypegroup/1/contenttype/50

SKIP: * promos > promo_folder

* mass audubon > camp http://localhost:42080/admin/contenttypegroup/5/contenttype/59 (careful errors / large / SQL fixes )
SKIP: * mass audubon > exhibit_listing http://localhost:42080/admin/contenttypegroup/5/contenttype/80
SKIP: * mass audubon > job_listing  http://localhost:42080/admin/contenttypegroup/5/contenttype/78
* mass audubon > sanctuary http://localhost:42080/admin/contenttypegroup/5/contenttype/58
SKIP: * mass audubon > sanctuary_job_listing http://localhost:42080/admin/contenttypegroup/5/contenttype/91
SKIP: * mass audubon > voice_of_audubon http://localhost:42080/admin/contenttypegroup/1/contenttype/93


### NEW Content Type: activity_icon ===

### NEW Content Type: facility_icon ===

### NEW Content Type: testimonial ===

### see fix var .sql

### You must re-edit every content type when a new block is added :(
### You must regenerate graphql when a new block is added :(

--------------------------

### Content Type: Camp
ADD: ezimageasset - background_asset / Background Image
ADD: ezimageasset - asset / Image Asset
ADD: ezlandingpage - page / Page Builder
RENAME: image - Image (legacy/do not use)

### Content Type: Content Page
ADD: ezimageasset - asset / Image Asset
RENAME: image - Image (legacy/do not use)
RENAME: thumbnail caption (legacy/do not use)
RENAME: thumbnail credit  (legacy/do not use)
DEL: image size
DEL: override layout

### Content Type: Content Page
ADD: ezimageasset - asset / Image Asset
RENAME: image - Image (legacy/do not use)
RENAME: thumbnail caption (legacy/do not use)
RENAME: thumbnail credit  (legacy/do not use)
DEL: image size
DEL: override layout

### DECOM Content Type: event
RENAME CLASS: Event (legacy/do not use)

### DECOM Content Type: event calendar
RENAME CLASS: Event calendar (legacy/do not use)

### RENAME Content Type: Landing page >> Layout Page
ADD: ezimageasset - background_asset / Background Image
ADD: ezimageasset - asset / Image Asset
ADD: ezlandingpage - page / Page Builder
RENAME: Image (legacy/do not use)

### Content Type: News Story
ADD: ezimageasset - background_asset / Background Image
ADD: ezimageasset - asset / Image Asset
RENAME: Image (legacy/do not use)
ADD: ezlandingpage - Page Builder
DEL: override layout

### Content Type: Press Item
ADD: ezimageasset - asset / Image Asset
RENAME: image - Image (legacy/do not use)

### Content Tyoe: Sanctuary
ADD: ezimageasset - asset / Image Asset
RENAME: image - Image (legacy/do not use)
RENAME: caption - Image Caption (legacy/do not use)
RENAME: credits - Image Credits (legacy/do not use)

### CONTENT TYPE FIELD GUIDELINES
page : ezlandingpage / Page Builder / Page Builder
background_asset: ezimageasset / Background Image
asset: ezimageasset  / Image Asset - Primary 3:2 image for listings, blocks, share


