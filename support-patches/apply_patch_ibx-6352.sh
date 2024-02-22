#!/bin/bash
DIR=`realpath $(dirname $0)`
# Project root directory (-./patches)
cd $DIR/../

echo "applying IBX-6352.patch to vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/upload-image/upload-image-editing.js"
cd vendor/ibexa/fieldtype-richtext
pwd
FILENAME="${PATCH_PATH}IBX-6352.patch"
echo $FILENAME
OUT="$(patch -p1 -i ${FILENAME})" || echo "${OUT}" | grep "Skipping patch" -q || (echo "$OUT" && false);
echo $OUT;
