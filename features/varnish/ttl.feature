Feature: As a site administrator I want Varnish to apply the configured TTL and to serve stale content while it refreshes

    # The siteaccesses used here are created by the setup suite and only differ in
    # ibexa.system.<siteaccess>.content.default_ttl: cache_ttl_long is 60s, cache_ttl_short is 5s.
    # Keeping them separate means the timing of the other suites is untouched and the assertions
    # do not depend on whatever the default TTL happens to be.

    @varnish6 @varnish7
    Scenario: The TTL Varnish stores an object with comes from content.default_ttl
        Given I create "Folder" Content items in root in "eng-GB"
            | name       | short_name  |
            | TestFolder | TtlTestItem |
        When I visit "TtlTestItem" on siteaccess "cache_ttl_long"
        And I reload the page
        Then response headers contain
            | Header  | Value |
            | x-cache | HIT   |
        And response headers match pattern
            | Header      | Pattern                 |
            | x-cache-ttl | /^(5[0-9]\|60)\.\d+$/   |
        When I visit "TtlTestItem" on siteaccess "cache_ttl_short"
        And I reload the page
        Then response headers contain
            | Header  | Value |
            | x-cache | HIT   |
        And response headers match pattern
            | Header      | Pattern        |
            | x-cache-ttl | /^[0-4]\.\d+$/ |

    @varnish6 @varnish7
    Scenario: Responses for anonymous users may be cached by shared proxies for a short while
        Given I create "Folder" Content items in root in "eng-GB"
            | name       | short_name    |
            | TestFolder | AnonCacheItem |
        When I visit "AnonCacheItem" on siteaccess "cache_ttl_long"
        Then response headers contain
            | Header        | Value                                                                |
            | cache-control | public, s-maxage=600, stale-while-revalidate=300, stale-if-error=300 |
        # The user context hash is an internal detail and must never be advertised to clients
        And response headers match pattern
            | Header | Pattern                          |
            | vary   | /^(?!.*X-User-Context-Hash).*$/i |

    @varnish6 @varnish7
    Scenario: Responses for logged-in users are not cacheable by shared proxies or browsers
        Given I create "Folder" Content items in root in "eng-GB"
            | name       | short_name     |
            | TestFolder | AdminCacheItem |
        And I am viewing the pages on siteaccess "cache_ttl_long" as "admin" with password "publish"
        When I visit "AdminCacheItem" on siteaccess "cache_ttl_long"
        Then response headers contain
            | Header        | Value                                        |
            | cache-control | private, no-cache, no-store, must-revalidate |

    @varnish6 @varnish7
    Scenario: An expired object is served from grace and refreshed in the background
        Given I create "Folder" Content items in root in "eng-GB"
            | name       | short_name    |
            | TestFolder | StaleTestItem |
        And I visit "StaleTestItem" on siteaccess "cache_ttl_short"
        And I reload the page
        And response headers contain
            | Header  | Value |
            | x-cache | HIT   |
        When I wait 7 seconds
        And I reload the page
        # A negative obj.ttl proves the object is past its TTL and delivered from the grace window
        Then response headers contain
            | Header  | Value |
            | x-cache | HIT   |
        And response headers match pattern
            | Header      | Pattern |
            | x-cache-ttl | /^-\d/  |
        # The stale hit triggered a background fetch; the first request to the object it
        # inserted reports MISS, the one after that is a hit on a fresh object again
        When I wait 2 seconds
        And I reload the page
        And I reload the page
        Then response headers contain
            | Header  | Value |
            | x-cache | HIT   |
        And response headers match pattern
            | Header      | Pattern        |
            | x-cache-ttl | /^[0-4]\.\d+$/ |

    @varnish6
    Scenario: A logged-in user in the grace window gets a refreshed response, not a stale one
        # varnish6.vcl vcl_hit: when the request carries a session cookie and the object is in
        # grace, it does return (miss) so that editors are never shown stale content.
        # varnish7.vcl has no vcl_hit at all and cannot be given the same one, because
        # return (miss) was removed from that subroutine in Varnish 7 - so Varnish 7 and later
        # deliver the stale object here instead. Tracked as a separate ticket; once varnish7.vcl
        # gains the equivalent (return (pass)), this scenario can be tagged @varnish7 as well.
        Given I create "Folder" Content items in root in "eng-GB"
            | name       | short_name         |
            | TestFolder | StaleAdminTestItem |
        And I am viewing the pages on siteaccess "cache_ttl_short" as "admin" with password "publish"
        And I visit "StaleAdminTestItem" on siteaccess "cache_ttl_short"
        And I reload the page
        And response headers contain
            | Header  | Value |
            | x-cache | HIT   |
        When I wait 7 seconds
        And I reload the page
        Then response headers contain
            | Header  | Value |
            | x-cache | MISS  |
