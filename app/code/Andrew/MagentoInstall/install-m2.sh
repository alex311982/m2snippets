#!/usr/bin/env bash

#Requirements: web-server, php-fpm, elasticsearch, mysql-server should be installed

magentoRepoUser="d6f9f2156d09b5cbbc0f897f211237ae"
magentoRepoPass="8dded5e9d2e2673e96a556ecf6f8da62"

magentoTz="Europe/Kiev"
magentoLang="en_AU"
magentoCurr="AUD"
magentoOwner="alex"
magentoDbHost="localhost"
magentoDbName="magento22"
magentoDbUser="user22"
magentoDbPass="user_pass"
mysqlPass="root_pass"
magentoDir="magento_m2_install"
magentoBackendFrontname="admin"
magentoAdminFirstName="AdminName"
magentoAdminLastName="AdminLastName"
magentoHost="http://magento.local/"
magentoUseRewrites=1

magentoAdminLogin="admin"
magentoAdminPass="admin_pass"
magentoAdminEmail="admin@admin.com"

PHP=$(which php)
PHP_MEM_LIMIT=' -d memory_limit=-1'
COMPOSER=$(which composer)
COMPOSER_PATH="$PHP$PHP_MEM_LIMIT $COMPOSER"
BIN_MAGENTO="/bin/magento"


mkLog(){
 echo "[$(date +%c)] $@" | tee -a ${logFile}
}

configMysql(){
mysql -uroot -p${mysqlPass} <<MYSQL_SCRIPT
CREATE DATABASE ${magentoDbName};
CREATE USER '${magentoDbUser}'@'localhost' IDENTIFIED BY '${magentoDbPass}';
GRANT ALL PRIVILEGES ON ${magentoDbName}.* TO '${magentoDbUser}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
}

installMagento(){
mkdir $(pwd)/${magentoDir}
sudo chown -R ${magentoOwner}:www-data $(pwd)/${magentoDir}
cd $(pwd)/${magentoDir}
sudo ${COMPOSER_PATH} create-project --repository-url=https://${magentoRepoUser}:${magentoRepoPass}@repo.magento.com/ magento/project-community-edition .
sudo find var generated vendor $(pwd)/pub/static $(pwd)/pub/media $(pwd)/app/etc -type f -exec chmod g+w {} +
sudo find var generated vendor $(pwd)/pub/static $(pwd)/pub/media $(pwd)/app/etc -type d -exec chmod g+ws {} +
sudo chown -R ${magentoOwner}:www-data .
sudo chmod u+x $(pwd)${BIN_MAGENTO}

${PHP}${PHP_MEM_LIMIT} $(pwd)${BIN_MAGENTO} setup:install \
--base-url=${magentoHost} \
--db-host="${magentoDbHost}" \
--db-name="${magentoDbName}" \
--db-user="${magentoDbUser}" \
--db-password="${magentoDbPass}" \
--backend-frontname="${magentoBackendFrontname}" \
--admin-firstname=${magentoAdminFirstName} \
--admin-lastname=${magentoAdminLastName} \
--admin-email="${magentoAdminEmail}" \
--admin-user="${magentoAdminLogin}" \
--admin-password="${magentoAdminPass}" \
--language="${magentoLang}" \
--currency="${magentoCurr}" \
--timezone="${magentoTz}" \
--use-rewrites=${magentoUseRewrites}
${PHP}${PHP_MEM_LIMIT} $(pwd)${BIN_MAGENTO} deploy:mode:set developer
}

doInstall(){
configMysql
installMagento
}

doInstall
