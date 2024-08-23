### Netgen Elastic Search Visitor

The goal here is to allow ElasticSearch query by TagIDs/keyword etc.

I reached out to Netgen via slack and they were kind enough to point me to code about creating 'Visitor' classes.

After much struggling and research I figured some of it out.

I'm sure someone can write this better and I look forward to getting something official from NetGen. In the meantime this works enough.

### How to test the new Elasticsearch Visitor works

As a sanity check, I created a controller at /_test_tags to run some queries against content type 'article' field 'tags' and some variations 