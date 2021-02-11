# ВАЖНО
* При глобальной установке любого из инструментов Quality Tools необходимо запоминать куда устанавливаются бинарные файлы, файлы стандартов кода, настроект и т.д. (необходимо для настройки  PHPSTORM)
* Magento 2 поставляется с уже предустановленными файлами, которые находятся в корне проекта``<magento_rppt_path>/vendor/bin/``, которые можно использовать для конкретного проекта не устанавливая бинарные файлы дополнительно
```
# CodeSniffer
<magento_root_path>/vendor/bin/phpcs
<magento_root_path>/vendor/bin/phpcbf

# MessDetector 
<magento_root_path>/vendor/bin/phpmd

# PHP-CS-FIXER
<magento_root_path>/vendor/bin/php-cs-fixer

# PhpStan
<magento_root_path>/vendor/bin/phpstan
```

# 1. CodeSniffer 
Это один один из инструментов для повышения качества написанного кода.
Этот инструмент поможет выявить нарушения форматирования, при надлежащей настройке PhpStorm будет об этом информировать путём выделения проблемных частей кода. Кроме всего прочего мы можем воспользоваться консолью для вывода информации об ошибках.

### 1.1.Установка (Глобальная)
 #### 1.1.1. через COMPOSER  
* В терминале запускаем команду
```
composer require "squizlabs/php_codesniffer=*"  --dev
```
* После этого доступны 2 команды в консоли для проверки на ошибки форматирования и дальнейшего фикса кода. 
P.S. Это бинарные файлы, путь к которым нам понадобится при настройке PHPSTORM
```
~/vendor/bin/phpcs
~/vendor/bin/phpcbf
```

#### 1.1.2. Другие способы установки (опционально)
```
# curl
curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar
curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar

# wget
wget https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar
wget https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar

# test the downloaded PHARs
php phpcs.phar -h
php phpcbf.phar -h

# Phive 
phive install phpcs
phive install phpcbf
## путь к файлам тогда будет тут 
./tools/phpcs -h
./tools/phpcbf -h

# PEAR
pear install PHP_CodeSniffer

```
P.S. Особенности настройки при альтернативных способах установки можно посмотреть тут https://github.com/squizlabs/PHP_CodeSniffer#installation 

#### 1.1.3. Пути к стандартам кода

* Сами стандарты PHP, как правило, поставляются с маджентой и находятся тут ``dev/tests/static/framework/Magento/ruleset.xml``. Для их установки переходим в PhpStorm, Settings/Preferences -> Editor -> Inspections -> PHP_CodeSniffer validation, 
  выбираем этот файл в Installed standart paths, затем выбрать в дробдауне Coding standart стандарт Magento.

* (Опционально)Для установки правил проверки кода для проектов на Magento 2 можно установить кастомные маджентовские рулы:
```
composer require --dev magento/magento-coding-standard
```

* Если будут проблемы с обнаружением правил для magento 2 нужно добавить конфигурацию
```
  vendor/bin/phpcs --config-set installed_paths ./vendor/magento/magento-coding-standard/
```

#### 1.1.4.Ручное управление
##### Запоминаем команды - они далее могут/будут использоваться при настройке External Tools в PhpStorm 
* Имея различные стандарты, можно вручную указывать какой стандарт применить к проверке кода, используя аргумент ``--standard``, например:
```
phpcs --standard=/path/to/MyStandard /path/to/code/myfile.inc

#или можно указать напрямую стандарт
phpcs --standard=PSR2 /path/to/code/myfile.php

vendor/bin/phpcs --standard=Magento2 app/code/path/to/some/dir/or/file
```

* Также можно автоматически править код, если есть ошибка:
```
vendor/bin/phpcbf --standard=Magento2 app/code/path/to/file/or/dir
```
P.S. Гайд по возможностям и использованию в "ручном" режиме можно найти тут https://github.com/squizlabs/PHP_CodeSniffer/wiki/Usage
Там же можно найти различные варианты команд, которые можно настроить в ExternalTools в PhpStorm.

 
### 1.2. Настройка в PhpStorm

 #### 1.2.1. Переходим в Settings/Preferences -> Languages & Frameworks -> PHP -> Quality Tools 
 * Открываем вкладку PHP CS Fixer в правой части окна и кликаем на кнопке с многоточием справа от поля Configuration
 * В открывшемся окне нажимаем на иконке директории справа от поля ``PHP CodeSniffer path`` и выбираем путь к установленному на Вашей машине бинарному файлу ``phpcs``.
   в нашем случае путь из п. 1.1.1. ``/home/serj/vendor/bin/phpcs``(/home/serj/ - меняем на свою домашнюю директорию)
 * В том же окне нажимаем на иконке директории справа от поля ``Path to phpcbf`` и выбираем путь к установленному на Вашей машине бинарному файлу ``phpcbf``.
   в нашем случае путь из п. 1.1.1. ``/var/www/m2/vendor/bin/phpcbf``(/home/serj/ - меняем на свою домашнюю директорию)
 * Проверить, правилен ли путь, можно кнопкой Validate - внизу окна должно появиться соответствующее сообщение.
 * Нажимаем Apply и потом Ok.
 
```
PHP CodeSniffer path:       /home/serj/vendor/bin/phpcs
Path to phpcbf:             /var/www/m2/vendor/bin/phpcbf
```

 #### 1.2.2. "Поясняем", где находится файл кофиграции. 
   * Заходим в Settings -> Editor -> Inspections и отмечаем чекбокс PHP -> Quality Tools -> PHP_CodeSniffer validation
   * Ставим галочки в чекбоксах ``Show warnings as`` и ``Show Sniff name``
   * (Опционально) Указываем путь в поле ``Installed standarts path`` к предустановленным стандартам: 
   ``/var/www/m24/vendor/phpcompatibility/php-compatibility/PHPCompatibility`` (``/var/www/m24`` - меняем на свой корневой путь к мадженте)  
   * Подтверждаем выбор нажав Ok и затем Apply

#### 1.2.3. Открываем PHPStorm, Settings > Tools > External Tools, нажимаем на "+" и вводим следующие значения:
    Name:                           PHP_CodeSniffer_Check           или  PHP_CodeSniffer_Fix
    Program:                        /home/serj/vendor/bin/phpcs     или  /home/serj/vendor/bin/phpcbf
    
    # Параметры могут быть разные в зависимости от запускаемой команды (см п. 1.4. Ручное управление)
    Parameters (Arguments):         --standard=PSR2 $FilePath$
    Working directory:              $FileDir$
 P.S. ("/home/serj/" нужно сменить на Вашу домашнюю директорию. Значение ``--standard=`` можно устанавливать любое из доступных)
 
 Parameters (Arguments) принимает любые аргументы доступные для запуска ``phpcs`` 
 
#### 1.2.4. По аналогии с п 1.2.3. настраиваем ExternalTools для "phpcbf"
    Name:                           <Любое имя, идентифицирующее настройку>
    Program:                        <Путь к бинарному файлу>
    Parameters (Arguments):         <Параметры и аргументы для запуска>
    Working directory:              $FileDir$

   
#### 1.2.5. Назначаем горячие клавиши для созданных команд в  ExternalTools
   * Открываем Settings > Keymap 
   * Ищем по названию "PHP_CodeSniffer_Check" (ну или как Вы его назвали). 
   * Добавляем любое сочетание клавиш, например ALT + C


# 2. PHP Mess Detector 
Mess Detector (обнаружитель беспорядков), будучи установленным, обрабатывает ваш код утилитой ````PHP_Depend```` и использует полученные метрики для составления собственных отчётов.

````PHPMD```` пытается выявить ошибки, которые не находит компилятор, не оптимальные алгоритмы, переусложнённый код, не используемые выражения и другие подобные проблемы.

#### 2.1. Установка через COMPOSER (глобально):
```
composer global require 'phpmd/phpmd=*'
```
* Бинарный файл появится в ``~/.composer/vendor/phpmd/phpmd/src/bin/phpmd``

В magento 2 этот файл присутствует также в дев зависимостях. 

#### 2.2. Переходим в Settings/Preferences -> Languages & Frameworks -> PHP -> Quality Tools 
 * Открываем вкладку PHP Mess Detector в правой части окна и кликаем на кнопке с многоточием справа от поля Configuration
 * В открывшемся окне нажимаем на иконке директории справа от поля ``PHP CodeSniffer path`` и выбираем путь к установленному на Вашей машине бинарному файлу ``phpcs``.
   в нашем случае путь из п. 2.1. ``/home/serj/.composer/vendor/phpmd/phpmd/src/bin/phpmd``(/home/serj/ - меняем на свою домашнюю директорию)
 * Проверить, правилен ли путь, можно кнопкой Validate - внизу окна должно появиться соответствующее сообщение.
 * Нажимаем Apply и потом Ok.


# 3. PhpStan

PHPStan – это инструмент сатического анализа (что и зачем -> https://ru.wikipedia.org/wiki/Статический_анализ_кода ) кода PHP. PHPStan – читает код и PHPDoc и пытаеться обнаружить потенциальные проблемы, такие как:

- вызов неопределенных переменных
- передача неверных типов данных
- использование несуществующих методов и атрибутов
- передача неверного количества параметов в метод
- использование возможных нулевых указателей

#### 3.1. Установка через COMPOSER:
```
cd ~
composer require --dev phpstan/phpstan
```
* Бинарный файл появится в ``~/vendor/bin/phpstan``

В корень проекта добавляем корневой конфиг файл phpstan.neon:
```
parameters:
includes:
    - vendor/bitexpert/phpstan-magento/extension.neon
    - app/code/Itdelight/Callback/extension.neon
```

В каждый модуль добавляем файл extension.neon с кастомными настройками валидатора. Например, для модуля app/code/Itdelight/Callback/extension.neon:
```
parameters:
    level: 7
    fileExtensions:
        - php
        - phtml
    paths:
        - ./
    checkMissingIterableValueType: false
```

#### 3.2 Варианты запуска из консоли:
```
#запуск из корневой папки мадженты (маджента включает бинарный файл "из коробки")
./vendor/bin/phpstan analyse

#Запуск бинарного файла из сохраненной директории
# Если Ваши классы находятся в директориях src и tests, Вы можете запустить PHPStan таким образом:
/home/serj/vendor/bin/phpstan analyse src tests

```
#### 3.3. Открываем PHPStorm, Settings > Tools > External Tools, нажимаем на "+" и вводим следующие значения:
    Name:                           PHP_Stan           
    Program:                        /home/serj/vendor/bin/phpstan
    
    # Параметры могут быть разные в зависимости от запускаемой команды (см п. 1.4. Ручное управление)
    Parameters (Arguments):         analyze --level=7 $FilePath$
    Working directory:              $FileDir$
 P.S. ("/home/serj/" нужно сменить на Вашу домашнюю директорию. Значение ``--standard=`` можно устанавливать любое из доступных)
 
 Parameters (Arguments) принимает любые аргументы доступные для запуска ``phpcs`` 

#### 3.4. Назначаем горячие клавиши для созданных команд в  ExternalTools
   * Открываем Settings > Keymap 
   * Ищем по названию "PHP_Stan" (ну или как Вы его назвали). 
   * Добавляем любое сочетание клавиш, например ALT + S


   # 4. PHP-CS-FIXER
### Некоторые предварительные условия :
  * PHPStorm установлен
  * Composer установлен и добавлен в путь ``$ export PATH="$PATH:$HOME/.composer/vendor/bin"``
  
  (получить путь к глобальному каталогу двоичных файлов ``composer global config bin-dir --absolute``)
  
  ## 4.1. Установка PHP-CS-Fixer (варианты)
  * Не забываем, что Magento 2 уже предоставляет бинарный файл``<magento_root_path>/vendor/bin/php-cs-fixer`` и можно использовать его
  
#### 4.1.1. Глобально (руками)
  ``$ wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O php-cs-fixer``
   ##### with specified version  
  ``$ wget https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v2.15.8/php-cs-fixer.phar -O php-cs-fixer``
   ##### with CURL
  ``$ curl -L https://cs.symfony.com/download/php-cs-fixer-v2.phar -o php-cs-fixer``  
   #####  Then
  ``$ sudo chmod a+x php-cs-fixer``
  
  ``$ sudo mv php-cs-fixer /usr/local/bin/php-cs-fixer``  

#### 4.1.2. Глобально (через Composer)  
``$ composer global require friendsofphp/php-cs-fixer``
  Бинарный файл будет тут ``~/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer``
  
  Или можно установить в специфическую директорию (если надо)
  
  ``$ mkdir --parents tools/php-cs-fixer``

  ``$ composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer``

#### 4.1.3. Глобально (через homebrew)
``$ brew install php-cs-fixer``

#### 4.1.4. Update
``$ php php-cs-fixer.phar self-update`` 


   ## 4.2. Настройка PHP-CS-Fixer в PHPSTORM

#### 4.2.1. Открываем PHPStorm, Settings > Tools > External Tools  вводим следующие значения:
    Name:                       PHP CS-Fixer
    Program:                    /home/serj/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer
    Parameters (Arguments):     --rules=@PSR2 --verbose fix $FileDir$/$FileName$
    Working directory:          $ProjectFileDir$
 P.S. ("/home/serj/" нужно сменить на Вашу домашнюю директорию)
 
#### 4.2.2. Переходим в Settings -> Languages & Frameworks -> PHP -> Quality Tools:
   * Открываем вкладку PHP CS Fixer в правой части окна и кликаем на кнопке с многоточием справа от поля Configuration
   * В открывшемся окне нажимаем на иконке директории справа от поля PHP CS Fixer path и снова выбираем путь к установленному на Вашей машине бинарному файлу php-cs-fixer. 
   Проверить, правилен ли путь, можно кнопкой Validate - внизу окна должно появиться соответствующее сообщение.
   ``/home/serj/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer``
   * Нажимаем Apply и потом Ok.
   
#### 4.2.3. "Поясняем", где находится файл кофиграции. 
   * Заходим в Settings -> Editor -> Inspections и отмечаем чекбокс PHP -> Quality Tools -> PHP CS Fixer validation
   * ставим галочку напротив Allow risky rules, кликаем на значок обновления справа от поля Ruleset и выбираем из списка Custom.
   (Это позволит указывать вручную настройки в файле ``.php_cs.dist`` в корне проекта)
   * Там же нажимаем на кнопку с многоточием. В открывшемся окне Custom Coding Standard выбираем путь к файлу .php_cs.dist в корне проекта.
   * Подтверждаем выбор нажав Ok и затем Apply
   
#### 4.2.4. Назначаем горячие клавиши   
   * Открываем Settings > Keymap 
   * Ищем по названию "PHP CS-Fixer" (ну или как Вы его назвали). 
   * Добавляем любое сочетание клавиш, например CTRL + WINDOWS

#### 4.2.5. Добавим в .gitignore файл .php_cs.cache. 
На данный момент такого файла в проекте не существует, но он будет создаваться после каждого запуска fixer-а.
"Тащить" его в репозиторий нет никакого смысла.

#### 4.2.6. В корне проекта можно создать файл .php_cs.dist, куда поместить кастомные настройки 

   ## 4.3. Как использовать:
##### Для форматирования текущего файла используйте комбинацию горячих клавиш, которая была создана на предыдущем шаге.
##### Для форматирования всех файлов* проекта откройте консоль, перейдите в корень проекта и запустите команду php-cs-fixer fix. 
*Не будут отформатированы файлы, которые мы исключили в методах exclude и notPath в файле конфигурации .php_cs.dist

# 5.Pre-commit hook
Добавляем файл pre-commit в папку .git/hooks/

Указываем абсолютный или относительный путь к code sniffer и phpstan.

После это во время коммита в Phpstorm будет чекбокс Run git hooks. 
Он должен быть включенным. 
Будет производится проверка статическим анализатором

# 6.Magento Cache Clean

Утилита которая позволяет  позволяет более быстро сбрасывать кэш и наблюдать изменения в файлах 

Установака

````composer require --dev mage2tv/magento-cache-clean````

Запускаем с помощью команды

````/usr/bin/node /home/artem/.composer/vendor/bin/cache-clean.js -w```` 

Можно использовать hot keys.


|Key|Cache Segment(s)|
|---|----------------|
|`c`| `config` |
|`b`| `block_html` |
|`l`| `layout` |
|`f`| `full_page` |
|`a`| (a for all) |
|`v`| (v for view) `block_html`, `layout`, `full_page` |
|`t`| `translate` |

Для того чтобы они работали в Phpstorm нажимаем Ctrl + Shift + a

Вводим ````registry````. 
В списке выбираем ````Registry...````

Включаем чекбокс ````nodejs.console.use.terminal````

Запуск скрипта из Phpstorm
````Settings->Tools````

Дальше Жмём на плюсик.

Выбираем `node.js`

Называем, например `cache-cleaner`

Указываем `Javascript file` - `vendor/bin/cache-cleaner.js`

`Application parametr` - `-w`

Теперь в правом верхнем углу как для xdebug мы увидим наш скрипт и сможем его запустить

 





    