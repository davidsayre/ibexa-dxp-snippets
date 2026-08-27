

update ibexa_content_field
set data_text = replace(data_text,'xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/','xmlns:ezxhtml="http://ibexa.co/xmlns/dxp/docbook/')
where data_text like '%xmlns%'
and data_type_string = 'ibexa_richtext';

update ibexa_content_field
set data_text = replace(data_text,'xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/','xmlns:ezcustom="http://ibexa.co/xmlns/dxp/docbook/')
where data_text like '%xmlns%'
and data_type_string = 'ibexa_richtext';
