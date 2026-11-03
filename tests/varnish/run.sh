#!/bin/bash
#
# Runs the varnishtest suite for the VCLs in docs/varnish/vcl/.
#
# Each case is run against a Varnish that has loaded the real VCL file, with a fixture standing in
# for parameters.vcl, so what is asserted is the shipped configuration rather than a copy of it.
#
# Usage:
#   tests/varnish/run.sh                 # all VCLs, using Docker
#   tests/varnish/run.sh varnish7.vcl    # a single VCL
#
# varnish6.vcl targets Varnish 6.0LTS and does not compile on 7.x (vcl_hit returns miss, which 7.x
# rejects), so it needs a different image than varnish7.vcl. varnish9.vcl needs a third, built from
# Dockerfile-9. All images must carry the xkey vmod, since the VCLs import it.

set -euo pipefail

TESTDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VCLDIR="$(cd "$TESTDIR/../../docs/varnish/vcl" && pwd)"

IMAGE_VARNISH6="${IMAGE_VARNISH6:-ibexa-varnishtest:6}"
IMAGE_VARNISH7="${IMAGE_VARNISH7:-ibexa-varnishtest:7}"
IMAGE_VARNISH9="${IMAGE_VARNISH9:-ibexa-varnishtest:9}"

image_for() {
    local vcl="$1"

    case "$vcl" in
        varnish7.vcl) echo "$IMAGE_VARNISH7" ;;
        varnish9.vcl) echo "$IMAGE_VARNISH9" ;;
        *)            echo "$IMAGE_VARNISH6" ;;
    esac
}

run_case() {
    local vcl="$1" case_name="$2" image
    image="$(image_for "$vcl")"

    echo "==> $vcl / $case_name (${image})"
    docker run --rm \
        -v "$VCLDIR/$vcl:/etc/varnish/default.vcl:ro" \
        -v "$TESTDIR/fixtures/parameters-${case_name}.vcl:/etc/varnish/parameters.vcl:ro" \
        -v "$TESTDIR/${case_name}.vtc:/${case_name}.vtc:ro" \
        --entrypoint varnishtest "$image" "/${case_name}.vtc"
}

vcls=("${@:-varnish6.vcl varnish7.vcl varnish9.vcl}")
# shellcheck disable=SC2206
vcls=(${vcls[@]})

for vcl in "${vcls[@]}"; do
    for case_name in untrusted trusted; do
        run_case "$vcl" "$case_name"
    done
done

echo "All varnishtest cases passed."
