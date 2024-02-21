/* ezcontentobject_tree.sort is specific values (1,2,4,5,8,9,11,12) */
/* FIX for whacky sorts
  select count(node_id) , sort_field from ezcontentobject_tree
  group by sort_field
  order by sort_field desc
*/
UPDATE ezcontentobject_tree SET `sort_field` = '1' where sort_field = 6;
