### Варианты установки Магенто 2.

<ol>
<li>Загрузить с GitHub
Команды:
```
cd /var/www/
git clone https://github.com/magento/magento2.git magento2
composer install
```

Нюансы такой установки - появится возможность обновлять код путем пула с гитхаба. Т.к. клонированный код будет подвергаться изменениям в процессе работы с инсталлированной Магентой, то могут быть конфликты.
Все обновления необходимо делать через composer update.

Следующим стэпом будет создание базы данных и юзера с грантами для соданной базы.

Дальше используя команду исталяции устанавливаем Магенту:
```
php bin/magento setup:install --base-url=http://magento2.example.com/ \
--db-host=localhost --db-name=magento \
--db-user=magento --db-password=magento \
--admin-firstname=Magento --admin-lastname=User --admin-email=user@example.com \
--admin-user=admin --admin-password=admin123 --language=en_US \
--currency=USD --timezone=America/Chicago --cleanup-database --use-rewrites=1
```

<li>Загрузить через Composer
Команды:
```
cd magento2/
composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition
```

Если компоузер запрашивает аутентификацию - необходимо:
- зайти на сайт https://marketplace.magento.com/.
- Зарегистрироваться. 
- Зайти My Profile > Access Keys
- Кликнуть на Create a New Access Key
- Дать имя ключам
- Скопировать Public key (имя пользователя) и Private key (пароль)

Установить разрешения для файлов и каталогов
```
sudo chmod -R 755 /var/www/magento2/
sudo chmod -R 777 /var/www/magento2/{pub,var}
```

Если необходимо установить сэмпл-дата:
```
php bin/magento sampledata:deploy
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

<li>Установка из архива
Зайти на сайт Магенто - https://magento.com/tech-resources/download.
Выбрать установка из архива. Есть 2 пакета установки -Magento Open Source or Magento Commerce  и Magento Commerce.
Magento Open Source - бесплатна в установке, Magento Commerce - необходимо иметь лицензию.
Нюансы такой установки - не будет симлинок на баш-скрипты в vendor/bin. Т.к. это все-лишь симлинки на скрипты, которые находятся в папках модулей, то можно их создать самим, либо запускать сами скрипты.

<li>Установка через браузер.
Необходимо скачать Магенту любым доступным способом. Подготовить хост, базу данных и зайти на корень хоста сайта. 
Магента перекинет на процесс установки. Необходимо следовать инструкциям на каждом степе.
<\ol>