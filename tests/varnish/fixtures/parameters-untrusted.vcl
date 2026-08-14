// Test fixture standing in for parameters.vcl: the client is NOT a trusted proxy.
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

// Deliberately does not contain 127.0.0.1, so that varnishtest's client is treated as an
// ordinary client rather than as a reverse proxy.
acl trusted_proxies {
    "192.0.2.1";
}
