/* The login column needs data and it needs to be unique */
update ezuser set login = email where login = "" and email != ""

update ezuser set email = concat(ezuser.contentobject_id,"@localhost") where email = "";

/* users should not be 0, assign to 14 / administrator */
update ezcontentobject set owner_id = 14 where owner_id = 0 ;
update ezcontentobject_version set creator_id = 14 where creator_id = 0 ;
