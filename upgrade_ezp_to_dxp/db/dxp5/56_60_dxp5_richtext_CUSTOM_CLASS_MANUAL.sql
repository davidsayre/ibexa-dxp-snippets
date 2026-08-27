/* YOU MUST find and fix any custom classes */

/*
fix custom link classes (no longer allowed)
select * from ibexa_content_field where data_text like '%ezxhtml:class="' and data_type_string = 'ibexa_richtext';
*/

update ibexa_content_field
set data_text = replace(data_text,'ezxhtml:class="btn-primary"','')
where data_text like '%ezxhtml:class="btn-primary"%'
and data_type_string = 'ibexa_richtext';
