// Our Backend - Assuming that web server is listening on port 80
// Replace the host to fit your setup
//
// For additional example see:
// https://github.com/ezsystems/ezplatform/blob/master/doc/docker/entrypoint/varnish/parameters.vcl

backend ezplatform {
    .host = "127.0.0.1"; // Replace with hostname/ip of the application server
    .port = "80";
}

// ACL for invalidators IP
//
// Alternative using HTTPCACHE_VARNISH_INVALIDATE_TOKEN : VCL code also allows for token based invalidation, to use it define a
//      shared secret using env variable HTTPCACHE_VARNISH_INVALIDATE_TOKEN and eZ Platform will also use that for configuring this
//      bundle. This is prefered for setups such as platform.sh/eZ Platform Cloud, where circular service dependency is
//      unwanted. If you use this, use a strong cryptological secure hash & make sure to keep the token secret.
// Use ez_purge_acl for invalidation by token.
acl invalidators {
    "127.0.0.1";
    "192.168.0.0"/16;
}

// ACL for debuggers IP
acl debuggers {
    "127.0.0.1";
    "192.168.0.0"/16;
}

// ACL for reverse proxies, TLS terminators and CDNs running in front of Varnish
//
// Only requests coming from these are allowed to set the "X-Forwarded-*" and "Forwarded" headers,
// see vcl_recv. Requests from anyone else get them stripped, as the application trusts them once
// framework.trusted_proxies is configured, which would otherwise let a client spoof the scheme,
// host and client IP it is seen with.
//
// Add the IP of your TLS terminator/load balancer/CDN here if one runs in front of Varnish,
// otherwise leave this as is.
acl trusted_proxies {
    "127.0.0.1";
}
