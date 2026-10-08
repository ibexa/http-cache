// Test fixture standing in for parameters.vcl: the client IS a trusted proxy.
//
// The backend is on a fixed port so that it can be referenced from a static file, see the
// "server s1 -listen" line in the .vtc files.

backend ezplatform {
    .host = "127.0.0.1";
    .port = "9081";
}

acl invalidators {
    "127.0.0.1";
}

acl debuggers {
    "127.0.0.1";
}

// Contains 127.0.0.1, so that varnishtest's client is treated as a reverse proxy in front of
// Varnish, the way a TLS terminator or CDN would be.
acl trusted_proxies {
    "127.0.0.1";
}
