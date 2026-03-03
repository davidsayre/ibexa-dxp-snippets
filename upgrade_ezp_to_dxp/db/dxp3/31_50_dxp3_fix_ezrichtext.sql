/* The custom_tag .yaml requires at least 1 attribute, this forces the XML to change from :

  <eztemplate name="rawhtml" ezxhtml:class="ez-custom-tag">
      <ezcontent>&lt;br/&gt;</ezcontent>
  </eztemplate>

  TO:

  <eztemplate name="rawhtml" ezxhtml:class="ez-custom-tag">
    <ezcontent>&lt;br/&gt;</ezcontent>
    <ezconfig><ezvalue key="title">rawhtml</ezvalue></ezconfig>
  </eztemplate>

  and the ORDER of the tags is critical!

*/

/* VERY IMPORTANT!! this is unique and standardized as attribute 'import' becuase if we need to find this again */
/* IMPORTANT altering the custom_tag .yaml will BREAK ALL RICHTEXT that is not compliant!! */

CREATE TABLE `tmp_ecoa_rawhtml_fix` (
  `attribute_original_id` int DEFAULT '0',
  `contentclassattribute_id` int NOT NULL DEFAULT '0',
  `contentobject_id` int NOT NULL DEFAULT '0',
  `data_float` double DEFAULT NULL,
  `data_int` int DEFAULT NULL,
  `data_text` longtext,
  `data_type_string` varchar(50) DEFAULT '',
  `id` int NOT NULL AUTO_INCREMENT,
  `language_code` varchar(20) NOT NULL DEFAULT '',
  `language_id` bigint NOT NULL DEFAULT '0',
  `sort_key_int` int NOT NULL DEFAULT '0',
  `sort_key_string` varchar(255) NOT NULL DEFAULT '',
  `version` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`version`),
  KEY `ezcontentobject_attribute_co_id_ver_lang_code` (`contentobject_id`,`version`,`language_code`),
  KEY `ezcontentobject_attribute_language_code` (`language_code`),
  KEY `sort_key_int` (`sort_key_int`),
  KEY `ezcontentobject_classattr_id` (`contentclassattribute_id`),
  KEY `sort_key_string` (`sort_key_string`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into tmp_ecoa_rawhtml_fix
select *
from ezcontentobject_attribute
where data_text like '%rawhtml%'

/* this SHOULD hit every rawhtml and not other customtags becuase factbox,accordion and separator all should have ezconfigs already */
update ezcontentobject_attribute
set data_text = replace(data_text, '</ezcontent></eztemplate>','</ezcontent><ezconfig><ezvalue key="import">import</ezvalue></ezconfig></eztemplate>')
where data_text like '%</ezcontent></eztemplate>%';
