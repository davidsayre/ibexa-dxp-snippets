update ezuser set email = concat(ezuser.contentobject_id,"@localhost") where email = "";
