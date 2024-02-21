/* run these after importing DXP database */
update ezimagefile set filepath = replace(filepath,"var/ezdemo_site/storage/","var/site/storage/") where filepath like "var/ezdemo_site/storage/%";
update ezcontentobject_attribute set data_text = replace(data_text,"var/ezdemo_site/storage/","var/site/storage/") where data_text like "%var/ezdemo_site/storage/%";

update ezcontentobject_attribute set data_text = replace(data_text,'url="/trashed/','url="var/site/storage/trashed/')
where data_text like '%url="/trashed/%';

update ezcontentobject_attribute set data_text = replace(data_text,'dirpath="/trashed"','dirpath="var/site/storage/trashed"')
where data_text like '%dirpath="/trashed"%';

update ezcontentobject_attribute
set data_text = replace(
        replace(data_text,'url="','url="var/site/storage/images/')
        ,'dirpath=""'
        ,'dirpath="var/site/storage/images/"'
    )
where data_text like '%dirpath=""%' and data_text not like '%url=""%';

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

