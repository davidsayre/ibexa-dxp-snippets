
Page builder block > zone > page > content


```sql
SELECT *
FROM ezpage_attributes ea,
dxp.ezpage_map_attributes_blocks emab,
ezpage_blocks eb,
ezpage_map_blocks_zones embz,
ezpage_map_zones_pages emzp,
ezpage_pages ep
where ea.id = emab.attribute_id
and emab.block_id = eb.id
and eb.id = embz.block_id
and embz.zone_id = emzp.zone_id
and ep.id = emzp.page_id
and content_id = 55676
and version_no = 4
```

Find custom rawhtml

```sql
select *
from ezcontentobject_attribute ecoa , ezcontentobject eco
where ecoa.contentobject_id = eco.id
and ecoa.version = eco.current_version
and ecoa.data_type_string = 'ezrichtext'
and ecoa.data_text like "%rawhtml%"
```

Becuase the new Admin requires BOTH the content and node ID it can be a pain

```sql
select eco.id as eco_id
, eco.name
, eco.remote_id
, eco.modified as modified_ts
, from_unixtime(eco.modified) as modified_dt
, ecc.identifier as class
, ecot.node_id
, ecot.parent_node_id
, ecot.path_identification_string
, ecot.is_hidden
, ecot.depth
, ezu_u.login as creator
, ezu_c.login as modifier
from ezcontentobject eco
, ezcontentobject_tree ecot
, ezcontentclass ecc
, ezcontentobject_version ecov
left outer join ezuser ezu_c on ecov.creator_id =
ezu_c.contentobject_id
left outer join ezuser ezu_u on ecov.user_id =
ezu_u.contentobject_id
where eco.id = ecot.contentobject_id
and eco.contentclass_id = ecc.id
and eco.id = ecov.contentobject_id
and eco.current_version = ecov.version
/* and eco.id = 12345 */
order by ecot.path_identification_string
```

If you need to hack into richtext field for the content object’s current version using MySQL Admin (which does not allow update on joins)

```sql
select * from ezcontentobject_attribute where data_type_string = 'ezrichtext' and contentobject_id = 998
and version in (select current_version from ezcontentobject where id = 998)

```