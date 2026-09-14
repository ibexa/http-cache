Varnish VCL tests
=================

`varnishtest` cases for the VCLs in [`docs/varnish/vcl`](../../docs/varnish/vcl). Each case loads the
real VCL file into a real Varnish, with a fixture standing in for `parameters.vcl`, and asserts on
the request the **backend** receives — which is where the reverse proxy header filtering in
`vcl_recv` can be observed.

Cases
-----

| Case | Fixture | Asserts |
|---|---|---|
| `untrusted.vtc` | `fixtures/parameters-untrusted.vcl` (client not in `trusted_proxies`) | every client supplied `X-Forwarded-*` / `Forwarded` / `X-Client-IP` / `Client-Cdn` is gone, `X-Forwarded-For` holds only the real client IP |
| `trusted.vtc` | `fixtures/parameters-trusted.vcl` (client in `trusted_proxies`) | the same headers are passed through untouched — guards against the filtering degrading into a blanket strip, which would break every setup with a TLS terminator or CDN in front of Varnish |

Running them
------------

```bash
docker build -t ibexa-varnishtest:7 tests/varnish
IMAGE_VARNISH7=ibexa-varnishtest:7 tests/varnish/run.sh varnish7.vcl
```

`run.sh` with no arguments runs every VCL. `varnish5.vcl` and `varnish6.vcl` target Varnish 6.0LTS
and do not compile on 7.x — their `vcl_hit` returns `miss`, which 7.x rejects — so they need a
Varnish 6.0 image carrying xkey, which you can point at with `IMAGE_VARNISH6`. CI only runs
`varnish7.vcl`; the 6.0 line is covered end to end by the `varnish6` browser-test job, which builds
`varnish5.vcl`.

`run.sh` also asserts that `varnish5.vcl` and `varnish6.vcl` stay identical apart from their
two-line header comment, since they are documented as 1:1 copies.

Notes
-----

- The backend listens on a fixed port (9081) so that the fixtures, which are static files, can
  point a `backend` at it.
- Varnish rejects an unused ACL, so a fixture must define exactly the ACLs its VCL references.
