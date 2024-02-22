#!/bin/bash
DIR=`realpath $(dirname $0)`
# Project root directory (-./patches)
cd $DIR/../

echo "cp admin-ui-image_variations.yaml vendor/ibexa/admin-ui/src/bundle/Resources/config/image_variations.yaml"
cp patches/admin-ui-image_variations.yaml vendor/ibexa/admin-ui/src/bundle/Resources/config/image_variations.yaml