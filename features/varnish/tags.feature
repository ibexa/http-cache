Feature: As a site administrator I want responses to carry the cache tags Varnish invalidates on

    @varnish6 @varnish7
    Scenario: A Content view response is tagged with the content and location it was built from
        # Tags are what every purge in this bundle keys on, but they are otherwise only tested
        # indirectly - through whether a purge happened to work. fos_http_cache is configured
        # with tag_mode: purgekeys, so TagHandler emits them space separated in an xkey header,
        # always seeded with ez-all. The header only reaches clients inside the debuggers ACL.
        Given I create "Folder" Content items in root in "eng-GB"
            | name       | short_name  |
            | TestFolder | TagTestItem |
        When I visit "TagTestItem" on siteaccess "site"
        Then response headers match pattern
            | Header | Pattern              |
            | xkey   | /(^\| )ez-all( \|$)/ |
            | xkey   | /(^\| )c\d+( \|$)/   |
            | xkey   | /(^\| )l\d+( \|$)/   |
