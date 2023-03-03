### Indexing LandingPage field type

1. Copy or merge the config/*.yaml into your project
1. Copy the ./src files into your project's ./src
1. Edit the Content Type with an ezlandingpage and check the 'Searchable'
    2. If that doesn't work, I you can use a SQL query

   update ezcontentclass_attribute set is_searchable = 1 where data_type_string = 'ezlandingpage';

1. Test

* creat a content page with Page Builder
* add a RichText block with some unique text (example 'instantpot')
* publish
* type your RichText block text (example 'instantpot) into the (Admin) site search
* you should find content matches.