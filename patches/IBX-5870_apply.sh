#!/bin/bash
DIR=`realpath $(dirname $0)`
# Project root directory (-./patches)
cd $DIR/../

echo "applying IBX-5870.patch to vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/link/link-ui.js"
cd vendor/ibexa/fieldtype-richtext
PATCH_PATH=../../../patches/
FILENAME="${PATCH_PATH}IBX-5870.patch"
echo $FILENAME
OUT="$(patch -p1 -i ${FILENAME})" || echo "${OUT}" | grep "Skipping patch" -q || (echo "$OUT" && false);
echo $OUT;