// Our Backend - Assuming that web server is listening on port 80
// Replace the host to fit your setup
//
// For additional example see:
// https://github.com/ibexa/docker/blob/main/docker/entrypoint/varnish/parameters.vcl

backend ibexa {
    .host = "127.0.0.1"; // Replace with hostname/ip of the application server
    .port = "80";
}

// ACL for invalidators IP
//
// Alternative using HTTPCACHE_VARNISH_INVALIDATE_TOKEN : VCL code also allows for token based invalidation, to use it define a
//      shared secret using env variable HTTPCACHE_VARNISH_INVALIDATE_TOKEN and Ibexa DXP will also use that for configuring this
//      bundle. This is prefered for setups such as Ibexa Cloud, where circular service dependency is
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
