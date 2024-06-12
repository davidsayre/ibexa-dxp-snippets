#!/bin/bash
DIR=`realpath $(dirname $0)`
# Project root directory (-./patches)
cd $DIR/../

PATCH_PATH=patches/

echo "cp admin-ui-image_variations.yaml vendor/ibexa/admin-ui/src/bundle/Resources/config/image_variations.yaml"
cp ${PATCH_PATH}/admin-ui-image_variations.yaml vendor/ibexa/admin-ui/src/bundle/Resources/config/image_variations.yaml