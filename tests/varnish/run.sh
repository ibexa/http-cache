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
# varnish5.vcl and varnish6.vcl target Varnish 6.0LTS and do not compile on 7.x (vcl_hit returns
# miss, which 7.x rejects), so they need a different image than varnish7.vcl. Both images must
# carry the xkey vmod, since the VCLs import it.

set -euo pipefail

TESTDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VCLDIR="$(cd "$TESTDIR/../../docs/varnish/vcl" && pwd)"

IMAGE_VARNISH6="${IMAGE_VARNISH6:-ibexa-varnishtest:6}"
IMAGE_VARNISH7="${IMAGE_VARNISH7:-ibexa-varnishtest:7}"

image_for() {
    local vcl="$1"

    case "$vcl" in
        varnish7.vcl) echo "$IMAGE_VARNISH7" ;;
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

# varnish5.vcl is kept as a 1:1 copy of varnish6.vcl for BC. Guard that, so the two cannot drift.
check_copies_in_sync() {
    if ! diff <(tail -n +3 "$VCLDIR/varnish5.vcl") <(tail -n +3 "$VCLDIR/varnish6.vcl") > /dev/null; then
        echo "FAIL: varnish5.vcl and varnish6.vcl differ beyond their two-line header comment." >&2
        echo "      They are documented as 1:1 copies - apply every change to both." >&2
        return 1
    fi
    echo "==> varnish5.vcl and varnish6.vcl are in sync"
}

vcls=("${@:-varnish5.vcl varnish6.vcl varnish7.vcl}")
# shellcheck disable=SC2206
vcls=(${vcls[@]})

check_copies_in_sync

for vcl in "${vcls[@]}"; do
    for case_name in untrusted trusted; do
        run_case "$vcl" "$case_name"
    done
done

echo "All varnishtest cases passed."
