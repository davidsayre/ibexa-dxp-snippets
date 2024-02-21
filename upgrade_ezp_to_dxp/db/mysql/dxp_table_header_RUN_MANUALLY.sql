/*
    The new DXP richtext does NOT like <tbody><th>
    In fact it will compeltely mess up the table if it sees this
    This is tricky stuff but you may have to completely abandon the <th> for <td> in this case becuase the parent is not correct

    CORRECT: <thead><th ....>
    INCORRECT: <tbody><th ...>

    IMPORTANT: run these in chucks and the LAST set is the destructive one that can break things
    STRONGLY suggest you backup the attributes:

*/


/****** part 1 ******/

/* BACKUP ezrichtext ATTRIBUTES you are about to mess with !!! */

CREATE TABLE `tmp_tr_fix_ezcontentobject_attribute` (
  `attribute_original_id` int(11) DEFAULT '0',
  `contentclassattribute_id` int(11) NOT NULL DEFAULT '0',
  `contentobject_id` int(11) NOT NULL DEFAULT '0',
  `data_float` double DEFAULT NULL,
  `data_int` int(11) DEFAULT NULL,
  `data_text` longtext,
  `data_type_string` varchar(50) DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` varchar(20) NOT NULL DEFAULT '',
  `language_id` bigint(20) NOT NULL DEFAULT '0',
  `sort_key_int` int(11) NOT NULL DEFAULT '0',
  `sort_key_string` varchar(255) NOT NULL DEFAULT '',
  `version` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`version`),
  KEY `ezcontentobject_attribute_co_id_ver_lang_code` (`contentobject_id`,`version`,`language_code`),
  KEY `ezcontentobject_attribute_language_code` (`language_code`),
  KEY `ezcontentobject_classattr_id` (`contentclassattribute_id`),
  KEY `sort_key_int` (`sort_key_int`),
  KEY `sort_key_string` (`sort_key_string`(191)),
  KEY `ezcontentobject_attribute_co_id_ver` (`contentobject_id`,`version`)
) ENGINE=InnoDB ;

insert into tmp_tr_fix_ezcontentobject_attribute(
attribute_original_id
,contentclassattribute_id
,contentobject_id
,data_float
,data_int
,data_text
,data_type_string
,id
,language_code
,language_id
,sort_key_int
,sort_key_string
,version
)
select attribute_original_id
,contentclassattribute_id
,contentobject_id
,data_float
,data_int
,data_text
,data_type_string
,id
,language_code
,language_id
,sort_key_int
,sort_key_string
,version from ezcontentobject_attribute
where contentobject_id in (select contentobject_id from tmp_tr_fix)
 and data_type_string = 'ezrichtext'
;



/****** part 2 ******/

/* create tmp table to flag these effected */
create table tmp_tr_fix ( contentobject_id int, primary key (contentobject_id) );
insert into tmp_tr_fix select distinct contentobject_id from ezcontentobject_attribute where data_text like "%<tbody><tr><th%";



/****** part 3 ******/

/* Clean TH attributes */
update ezcontentobject_attribute set data_text = replace(data_text,' valign="top"',' ') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,' abbr=""',' ') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,' scope="col"',' ') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,' custom:scope="col"',' ') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,' custom:abbr=""',' ') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,' custom:valign="top"',' ') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,' xhtml:width="0"',' ') where contentobject_id in (select contentobject_id from tmp_tr_fix);

/* clean ezattributes */
update ezcontentobject_attribute set data_text = replace(data_text,'<ezattribute><ezvalue key="scope">col</ezvalue><ezvalue key="abbr"/><ezvalue key="valign">top</ezvalue></ezattribute>','') where contentobject_id in (select contentobject_id from tmp_tr_fix);
// NOTE: you may need to check for more variations here:


/* Clean table tag */
update ezcontentobject_attribute set data_text = replace(data_text,'<informaltable class="default" width="0">','<informaltable class="table">') where contentobject_id in (select contentobject_id from tmp_tr_fix);


/****** part 4 DESTRUCTIVE 1-time operation (note the spaces so it does NOT match [<th]ead ) ******/
/* one-time replace ALL th with TD becuase not in thead */
update ezcontentobject_attribute set data_text = replace(data_text,'</th>','</td>') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,'<th ','<td ') where contentobject_id in (select contentobject_id from tmp_tr_fix);
update ezcontentobject_attribute set data_text = replace(data_text,'<th>','<td>') where contentobject_id in (select contentobject_id from tmp_tr_fix);