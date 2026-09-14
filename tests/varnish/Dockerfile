# Image for running the varnishtest suite in tests/varnish/.
#
# Mirrors doc/docker/Dockerfile-varnish9 from ibexa/docker: the VCLs import the xkey vmod, which the
# official image does not ship.

FROM varnish:7.1

SHELL ["/bin/bash", "-c"]

# Root is only needed to install the vmod; the image drops back to the unprivileged user the base
# image runs as by default.
USER root
RUN set -e && \
    read -ra vmod_deps <<< "$VMOD_DEPS" && \
    apt-get update && \
    apt-get -y --no-install-recommends install "${vmod_deps[@]}" /pkgs/*.deb && \
    install-vmod https://github.com/varnish/varnish-modules/releases/download/0.20.0/varnish-modules-0.20.0.tar.gz && \
    rm -rf /var/lib/apt/lists/*

USER varnish
