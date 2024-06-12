#!/bin/bash
DIR=`realpath $(dirname $0)`
# Project root directory (-./patches)
cd $DIR/../

echo "applying IBX-7854.patch to ./vendor/ibexa/form-builder"
cd vendor/ibexa/form-builder
PATCH_PATH=../../../patches/
FILENAME="${PATCH_PATH}IBX-7854.patch"
echo $FILENAME
OUT="$(patch -p1 -i ${FILENAME})" || echo "${OUT}" | grep "Skipping patch" -q || (echo "$OUT" && false);
echo $OUT;
