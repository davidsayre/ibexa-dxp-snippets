/*
upgrade eztags into language eztags_keywords (must have eng-US as language 2 installed)
*/

insert into eztags_keyword (keyword_id,locale,language_id,keyword,`status`)
select id, 'eng-US',2,keyword,1
from eztags
where id not in (select keyword_id from eztags_keyword);





