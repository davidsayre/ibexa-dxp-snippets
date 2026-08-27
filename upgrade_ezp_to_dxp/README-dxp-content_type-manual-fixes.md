### A Content Type cannot be in multiple content groups!

Every Content type must be in only 1 content type. No more, no less.

It will break Search with a field error

```
SELECT count(*), content_type_id FROM dxp.ibexa_content_type_group_assignment group by content_type_id
```

You must decide which content type + content type group record to delete.

