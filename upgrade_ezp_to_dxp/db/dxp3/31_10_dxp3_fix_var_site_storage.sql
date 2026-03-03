/* run these after importing eZ database */
update ezimagefile set filepath = replace(filepath,"var/ezdemo_site/storage/","var/site/storage/") where filepath like "var/ezdemo_site/storage/%";
update ezcontentobject_attribute set data_text = replace(data_text,"var/ezdemo_site/storage/","var/site/storage/") where data_text like "%var/ezdemo_site/storage/%";

update ezcontentobject_attribute set data_text = replace(data_text,'url="/trashed/','url="var/site/storage/trashed/') where data_text like '%url="/trashed/%';
update ezcontentobject_attribute set data_text = replace(data_text,'dirpath="/trashed"','dirpath="var/site/storage/trashed"') where data_text like '%dirpath="/trashed"%';

update ezcontentobject_attribute
set data_text = replace(
        replace(data_text,'url="','url="var/site/storage/images/')
        ,'dirpath=""'
        ,'dirpath="var/site/storage/images/"'
    )
where data_text like '%dirpath=""%' and data_text not like '%url=""%';


update ezimagefile set filepath = replace(filepath,"var/ezflow_site/storage/","var/site/storage/") where filepath like "var/ezflow_site/storage/%";
update ezcontentobject_attribute set data_text = replace(data_text,"var/ezflow_site/storage/","var/site/storage/") where data_text like "%var/ezflow_site/storage/%";
