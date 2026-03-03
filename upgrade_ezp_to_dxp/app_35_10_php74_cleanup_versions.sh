#!/bin/bash
DIR=`realpath $(dirname $0)`
cd $DIR
cd ../

if [ ! -d "upgrade_dxp3" ]; then
    echo "missing ./upgrade_ezp3/ installation for php 7.4 upgrade"
    return 1;
fi

echo "running in app php 7.4 ezplatform v3 ./upgrade_ezp3 instance"
echo "recommend mysqldump to save after this lengthy process"

docker compose exec -u www-data app74 /bin/bash -c "cd upgrade_dxp3; export SYMFONY_ENV=prod && php -d memory_limit=-1 bin/console --env=prod ezplatform:content:cleanup-versions --keep=2"

echo "recommend mysqldump to save after this lengthy process"
# [WARNING] Command alias "ezplatform:content:cleanup-versions" is deprecated since 3.3 and will be removed in in 4.0.
#           Use "ibexa:content:cleanup-versions" instead.


