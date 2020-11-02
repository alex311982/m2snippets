#!/usr/bin/env bash

#Requirements: web-server, php-fpm, elasticsearch, mysql-server should be installed

magentoRepoUser="d6f9f2156d09b5cbbc0f897f211237ae"
magentoRepoPass="8dded5e9d2e2673e96a556ecf6f8da62"

magentoTz="Europe/Kiev"
magentoLang="en_AU"
magentoCurr="AUD"
magentoOwner="alex"
magentoDbHost="localhost"
magentoDbName="magento"
magentoDbUser="user"
magentoDbPass="user_pass"
mysqlPass="root_pass"

magentoAdminLogin="admin"
magentoAdminPass="admin_pass"
magentoAdminEmail="admin@admin.com"


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
mkdir magento_m2_install
sudo chown -R ${magentoOwner}:www-data magento_m2_install
cd magento_m2_install
sudo composer create-project --repository-url=https://${magentoRepoUser}:${magentoRepoPass}@repo.magento.com/ magento/project-community-edition .
sudo find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
sudo find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
sudo chown -R ${magentoOwner}:www-data .
sudo chmod u+x ./bin/magento

./bin/magento setup:install \
--base-url=http://magento.local/ \
--db-host="${magentoDbHost}" \
--db-name="${magentoDbName}" \
--db-user="${magentoDbUser}" \
--db-password="${magentoDbPass}" \
--backend-frontname="admin" \
--admin-firstname="Admin" \
--admin-lastname="Admin" \
--admin-email="${magentoAdminEmail}" \
--admin-user="${magentoAdminLogin}" \
--admin-password="${magentoAdminPass}" \
--language="${magentoLang}" \
--currency="${magentoCurr}" \
--timezone="${magentoTz}" \
--use-rewrites=1
./bin/magento deploy:mode:set developer
}

doInstall(){
configMysql
installMagento
}

doInstall