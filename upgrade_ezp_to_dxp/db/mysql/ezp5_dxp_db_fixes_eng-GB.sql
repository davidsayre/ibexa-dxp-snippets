
/* >>> before running these BACKUP THE DATABASE becuase this WILL break things and you cannot fix once updated / deleted <<< */
/*
  check for eng-GB select * FROM ezcontentobject_name where real_translation = 'eng-GB' ;
*/

/* multi-language safe*/
update ezcontentclass set serialized_description_list = replace(serialized_description_list,"eng-GB","eng-US") where serialized_description_list like '%eng-GB%';
update ezcontentclass set serialized_name_list = replace(serialized_name_list,"eng-GB","eng-US") where serialized_name_list like '%eng-GB%';
update ezcontentclass_attribute set serialized_description_list = replace(serialized_description_list,"eng-GB","eng-US") where serialized_description_list like '%eng-GB%';
update ezcontentclass_attribute set serialized_name_list = replace(serialized_name_list,"eng-GB","eng-US") where serialized_name_list like '%eng-GB%';
update ibexa_taxonomy_entries set mainLanguageCode = 'eng-US', names = replace(names, "eng-GB","eng-US");
update ezcontentobject_name set content_translation = 'eng-US', language_id = 2, real_translation = 'eng-US' where real_translation = 'eng-GB';
update ezcontentobject_attribute set data_text = replace(data_text,"eng-GB","eng-US") where data_text like '%eng-GB%';

/* ezpage fixes */
update ezpage_pages set language_code = 'eng-US' where language_code = 'eng-GB';

/* NOTE : it is likely you will have to run client specific language specific fixes as well */