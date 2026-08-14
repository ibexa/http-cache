@varnish6 @varnish7
Feature: As a site administrator I want Varnish to drop reverse proxy headers sent by clients

    # The application trusts X-Forwarded-* as soon as framework.trusted_proxies is configured, so a
    # client must not be able to set them. Varnish strips them for every client that is not listed
    # in the trusted_proxies ACL - which is every client here, as the ACL deliberately does not
    # contain the Docker network.
    #
    # The rendered page reports the headers the application received, since that cannot be observed
    # from the response alone. See src/bundle/Resources/views/tests/cache/proxy_headers.html.twig.

    @admin
    Scenario Outline: Client supplied reverse proxy headers never reach the application
        Given I create "proxyHeadersContentType" Content items in root in "eng-GB"
            | name       |
            | <itemName> |
        And I am viewing the pages on siteaccess "site" as "<user>" "<password>"
        And I set request header "X-Forwarded-Host" to "evil.example"
        And I set request header "X-Forwarded-Proto" to "https"
        And I set request header "X-Forwarded-Port" to "443"
        And I set request header "X-Forwarded-Prefix" to "/admin"
        And I set request header "X-Forwarded-For" to "6.6.6.6"
        And I set request header "X-Client-IP" to "6.6.6.6"
        And I set request header "Client-Cdn" to "fastly"
        And I set request header "Forwarded" to "for=6.6.6.6;host=evil.example;proto=https"
        When I visit "<itemName>" on siteaccess "site"
        # A cache hit would show the headers of whichever request populated the cache
        And response headers contain
            | Header  | Value |
            | x-cache | MISS  |
        Then I should see "XFHOST:-"
        And I should see "XFPROTO:-"
        And I should see "XFPREFIX:-"
        And I should see "FORWARDED:-"
        And I should see "XCLIENTIP:-"
        And I should see "CLIENTCDN:-"
        # Derived by the VCL from the stripped X-Forwarded-Proto, not taken from the client
        And I should see "XFPORT:80"
        # X-Forwarded-For is overwritten with the real client IP rather than unset
        And I should not see "6.6.6.6"
        And I should not see "evil.example"

        Examples:
            | user      | password | itemName             |
            | admin     | publish  | ProxyProbeAdmin      |
            | anonymous |          | ProxyProbeAnonymous  |
