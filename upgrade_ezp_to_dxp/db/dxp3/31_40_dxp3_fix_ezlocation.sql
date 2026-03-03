/* This is the migration find/replace Ibexa provides for 'ibexa:migrate:richtext-namespaces' */
-- # RichText namespaces map to migrate
--     ibexa.field_type.rich_text.namespaces_migration_map:
--         'xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"': 'xmlns:ezxhtml="http://ibexa.co/xmlns/dxp/docbook/xhtml"'
--         'xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"': 'xmlns:ezcustom="http://ibexa.co/xmlns/dxp/docbook/custom"'
--         'ezxhtml:class="ez-embed-type-image"': 'ezxhtml:class="ibexa-embed-type-image"'
--         'xmlns:ez="http://ez.no/xmlns/ezpublish/docbook"': 'xmlns:ez="http://ibexa.co/xmlns/dxp/docbook"'
--         'xmlns:a="http://ez.no/xmlns/annotation"': 'xmlns:a="http://ibexa.co/xmlns/annotation"'
--         'xmlns:m="http://ez.no/xmlns/module"': 'xmlns:m="http://ibexa.co/xmlns/module"'

/* fix ez_ibexa (see bin/console ibexa:migrate:richtext-namespaces) */
-- update ezcontentobject_attribute
-- set data_text = replace(data_text, 'ez-embed-type-image','ibexa-embed-type-image')
-- where data_text like '%ez-embed-type-image%';

/* fix names space (see bin/console ibexa:migrate:richtext-namespaces) */
-- update ezcontentobject_attribute
-- set data_text = replace(data_text
--         ,'xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook'
--         ,'xmlns:ezxhtml="http://ibexa.co/xmlns/dxp/docbook'
--     )
-- where data_text like '%xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook%';

/* replace eznode:// with ezlocation:// */
update ezcontentobject_attribute
set data_text = replace(data_text
    , 'href="eznode://'
    , 'href="ezlocation://'
    )
where data_text like '%href="eznode:%';

/* ALSO fix if this is POST migration and blocks have richtext copy / pasted */
update ezpage_attributes
set value = replace(value
    , 'href="eznode://'
    , 'href="ezlocation://'
    )
where value like '%href="eznode:%';


/* fix missing main_node_id */
create table tmp_fix_location_main (node_id int not null, main_node_id int not null, primary key (node_id) );

insert into tmp_fix_location_main (node_id, main_node_id)
select node_id, main_node_id from ezcontentobject_tree where main_node_id not in (select node_id from ezcontentobject_tree t);

update ezcontentobject_tree set main_node_id = node_id where node_id in (select node_id from tmp_fix_location_main);

/*
dxp5 variation (for reference)
create table tmp_fix_location_main (node_id int not null, main_node_id int not null, primary key (node_id) );

insert into tmp_fix_location_main (node_id, main_node_id)
select node_id, main_node_id from ibexa_content_tree where main_node_id not in (select node_id from ibexa_content_tree t);

update ibexa_content_tree set main_node_id = node_id where node_id in (select node_id from tmp_fix_location_main);
*/
