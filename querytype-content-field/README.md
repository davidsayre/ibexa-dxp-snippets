### 

The view.yaml defined queries using:

    ibexa_query::contentQueryAction 
    ibexa_query::locationQueryAction

However, these do not support field level sorting.

David created a 1-off version:

see  src\Controller\Ibexa\QueryType\QueryFieldController.php

The usage in .yaml look like this (content type 'news_item' with date field 'publish_date') : 

    ibexa:
        system:
            site_group:
                content_view:
                    full:
                        news_listing:
                            template: "@ma_user/news_listing/news_listing-full.html.twig"
                            # DJS custom query with 'content_type/content_field (asc|desc)' awareness
                            controller: app_query::pagingQueryAction
                            params:
                                query:
                                    query_type: 'Children'
                                    parameters:
                                        content: '@=content'
                                    assign_results_to: items
                                    # DJS custom param
                                    sort_field: 'news_item/publish_date desc'
                                    limit: 9
                            match:
                                Identifier\ContentType: news_listing