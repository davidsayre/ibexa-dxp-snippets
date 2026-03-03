delete from ibexa_policy_limitation_value where limitation_id in (
    select id from ibexa_policy_limitation where policy_id in (
        select id from ibexa_policy where module_name in (
        'ezflow',
        'ezie',
        'ezjscore',
        'ezmultiupload',
        'ezodf',
        'ezoe',
        'ezrssfeed',
        'survey',
        'websitetoolbar',
        'ezoe',
        'rss'
        )
    )
);

delete from ibexa_policy_limitation where policy_id in (
    select id from ibexa_policy where module_name in (
    'ezflow',
    'ezie',
    'ezjscore',
    'ezmultiupload',
    'ezodf',
    'ezoe',
    'ezrssfeed',
    'survey',
    'websitetoolbar',
    'ezoe',
    'rss'
    )
)
;
delete from ibexa_policy where module_name in (
'ezflow',
'ezie',
'ezjscore',
'ezmultiupload',
'ezodf',
'ezoe',
'ezrssfeed',
'survey',
'websitetoolbar',
'ezoe',
'rss'
);
