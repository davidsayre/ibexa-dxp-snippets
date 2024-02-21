update ezcontentobject_attribute set data_type_string = 'ezstring' where data_type_string = 'ezidentifier';
update ezcontentclass_attribute set data_type_string = 'ezstring' where data_type_string = 'ezidentifier';
update ezimagefile set filepath = replace(filepath,"var/storage/","var/site/storage/") where filepath like "var/storage/%";
update ezcontentobject_attribute set data_text = replace(data_text,"var/storage/","var/site/storage/") where data_text like "%var/storage/%";
/* fix https://www.fileformat.info/info/unicode/char/00a0/index.htm */
UPDATE ezcontentobject_attribute SET data_text = REPLACE(data_text, UNHEX('C2A0'), ' ');

/* fix ezinteger 'default' 0 should be set to the MIN data_int1 */
update ezcontentclass_attribute set data_int3 = data_int1 where data_int3 < data_int1 and data_type_string = 'ezinteger';
/* matrix field is not supported */
delete from ezcontentclass_attribute where data_type_string = 'ezmatrix';
delete from ezcontentobject_attribute where data_type_string = 'ezmatrix';