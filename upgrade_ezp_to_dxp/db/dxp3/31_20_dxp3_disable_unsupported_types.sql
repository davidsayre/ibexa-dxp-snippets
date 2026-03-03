update ezcontentobject_attribute set data_type_string = 'ezstring' where data_type_string = 'ezidentifier';
update ezcontentclass_attribute set data_type_string = 'ezstring' where data_type_string = 'ezidentifier';

/* fix https://www.fileformat.info/info/unicode/char/00a0/index.htm */
UPDATE ezcontentobject_attribute SET data_text = REPLACE(data_text, UNHEX('C2A0'), ' ');

/* matrix field is not supported */
update ezcontentclass_attribute set data_type_string = 'ezstring' where data_type_string = 'ezmatrix';
update ezcontentobject_attribute set data_type_string = 'ezstring' where data_type_string = 'ezmatrix';

/* fix ezinteger 'default' 0 should be set to the MIN data_int1 */
update ezcontentclass_attribute set data_int3 = data_int1 where data_int3 < data_int1 and data_type_string = 'ezinteger';

/* any fields that COULD be used for criteria need to be searchable */
update ezcontentclass_attribute set is_searchable = 1 where data_type_string = 'ezdatetime'

/* we are not converting the ezpage datatype so just keep it as a string */
update ezcontentobject_attribute set data_type_string = 'ezstring' where data_type_string = 'ezpage';
update ezcontentclass_attribute set data_type_string = 'ezstring' where data_type_string = 'ezpage';

