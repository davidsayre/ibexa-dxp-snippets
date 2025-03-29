### Admin UI Report : Report Images to Archive

Summary of how to use
---------------------

1. Run CLI command in batches to generate table of report items
2. Admin UI - View report items in the admin
3. Admin UI - Download table of report items as CSV

Understand the --archive-sections or do not proceed
---------------------------------------------------

It is critical to understand that impact of the --archive-sections= argument.

Let's assume these example images are in your media library in the 'media' section.

When this command runs it will inspect the relations and reverse-relations of the image and based on what you put in the --archive-sections param, determine if the image is used by ONLY content from 'archived' sections.

Example:
* (image) potatos

In this example the image has no reverse relations status = 'not_in_use'

Example: 
* (frontpage) Homepage (section = 'content') -> (image) Koala
* (content page) About Us (section = 'content') -> (image) Koala
* (content page) Old About Us (section = 'archive') -> (image) Koala

In this example the image status = 'in_use'

Example: 
* Old About Us (section = 'archive') -> (image) Mountain
* Old News Promo (section = 'archive') -> (image) Mountain

In this example the image has relations, but those are all in the 'archive' section therefore status = 'in_use_only_archive'

This is the special case where an image must NOT be deleted but could be moved as all reverse-lreated content is already archived and NOT on the live site.

### --archive-sections as a CSV
It may be useful to denote several sections that you consider 'archive'. The scenario for this would be sections that are NOT on the live site and possibly not even accessible by Editors.

 
### Ready to Run CLI command in batches

Since the amount of records varies over time, this is meant to be run on-demand and by a human. 

You will get duplicate data if you don't pay attention to the limit/offset params

1. Dry run to get 'Total' printed on your first run
```
bin/console app:image-to-archive-report:generate --username=admin --archive-sections=site_archive,superadminonly
```

2. Calculate your batch sizes

For example: if you see 13,900 image records found, you could batch by 5000 to get 3 batch runs

* --limit=5000 --offset=0;
* --limit=5000 --offset=5000;
* --limit=5000 --offset=10000;

3. Start your batching! Be sure to truncate first

```
bin/console app:image-to-archive-report:generate --username=admin --truncate-table=1 --save=1 
bin/console app:image-to-archive-report:generate --username=admin --archive-sections=site_archive,superadminonly --limit=5000 --offset=0 ---save=1"
bin/console app:image-to-archive-report:generate --username=admin --archive-sections=site_archive,superadminonly --limit=5000 --offset=5000 ---save=1"
bin/console app:image-to-archive-report:generate --username=admin --archive-sections=site_archive,superadminonly --limit=5000 --offset=10000 ---save=1"
```

You know you are done when no more runs return any records based on the limit + offset

4. Review the result in the Admin UI "Images to Archive"

5. Download the CSV from the Admin UI "Images to Archive"

Now you will need to decide what to do with this information. For example, you could create a command that uses the content_id of the images with status = 'not_in_use' and move them all into an 'archive' or 'superadminonly' folder which Editors don't have access to.

I highly encourage you run this on a local dev/docker first and verify everything.

Moving can always be undone. I highly discourage deleting these images. If something goes sideways you do not want to break your entire site. 

