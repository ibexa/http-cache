Feature: As a site administrator I want equivalent requests to share one cache object

    @varnish6 @varnish7 @varnish9
    Scenario: Query string parameter order does not create a second cache object
        # vcl_recv normalises the cache key with std.querysort, so the two requests below
        # differ only in the order the parameters were written and must hit the same object
        Given I create "Folder" Content items in root in "eng-GB"
            | name       | short_name |
            | TestFolder | QsTestItem |
        When I am on "/site/QsTestItem?b=2&a=1"
        Then response headers contain
            | Header  | Value |
            | x-cache | MISS  |
        When I am on "/site/QsTestItem?a=1&b=2"
        Then response headers contain
            | Header  | Value |
            | x-cache | HIT   |
