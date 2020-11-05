# Code Sniffer
###Установка
Это один один из инструментов для повышения качества написанного кода.  

Этот инструмент поможет выявить нарушения форматирования, при надлежащей настройке PhpStorm будет об этом информировать путём выделения проблемных частей кода. Кроме всего прочего мы можем воспользоваться консолью для вывода информации об ошибках.

Для установки необходимо использовать композер:
```
composer require "squizlabs/php_codesniffer=*"  --dev
```

После этого доступны 2 команды в консоли для проверки на ошибки форматирования и дальнейшего фикса кода.
```
./vendor/bin/phpcs
./vendor/bin/phpcbf
```

Для установки правил проверки кода для проектов на Магенте 2 необходимо установить кастомные магентовские рулы:
```
composer require --dev magento/magento-coding-standard
```

После этого можно инспектировать код используя магентовские рулы. Например:

```
vendor/bin/phpcs --standard=Magento2 app/code/
```

И после этого автоматически править код, если есть ошибки:
```
vendor/bin/phpcbf --standard=Magento2 app/code/
```

###Интеграция в PhpStorm

- Идем на Preferences -> Languages & Frameworks -> PHP -> CodeSniffer
- Выбираем путь к файлу phpcs и phpcbf
- Заходим в Preferences -> Editor -> Inspections -> PHP -> Quality Tools -> PHP Code Sniffer validation для установки правил проверки кода
- Ставим галочку напротив PHP Code Sniffer validation
- Выбираем Coding standart: Custom
- Указываем путь к рулам Магенты: vendor/magento/magento-coding-standard/Magento2/ruleset.xml


# PhpStan

PHPStan – это инструмент сатического анализа (что и зачем -> https://ru.wikipedia.org/wiki/Статический_анализ_кода ) кода PHP. PHPStan – читает код и PHPDoc и пытаеться обнаружить потенциальные проблемы, такие как:

- вызов неопределенных переменных
- передача неверных типов данных
- использование несуществующих методов и атрибутов
- передача неверного количества параметов в метод
- использование возможных нулевых указателей

Устанавливается через composer:
```
composer require --dev phpstan/phpstan
```

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

Запускаем с консоли:
```
./vendor/bin/phpstan analyse
```