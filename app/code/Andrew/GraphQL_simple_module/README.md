#Создание простого модуля с GraphQL эндпоинтами

В версии Magento 2.3 появилась Graphql альтернатива REST/SOAP API для фронтразработчиков. 

Используется 2 типа GraphQL операций:

- Query: для чтения данных
- Mutation: для изменения данных на сервере

Начиная с версии 2.3.4 модули ядра уже реализуют GraphQL эндпоинты:

- CatalogInventoryGraphQl
- CatalogUrlRewriteGraphQl
- BundleGraphQl
- CatalogGraphQl
- ConfigurableProductGraphQl
- CustomerGraphQl
- DownloadableGraphQl
- EavGraphQl
- GroupedProductGraphQl
- TaxGraphQl
- ThemeGraphQl
- UrlRewriteGraphQl
- WeeeGraphQl

###Основные шаги для создания модуля с GraphQL эндпоинтами:

1. Создание схемы в файле модуля /etc/schema.graphqls:
```
type Query {
    testcustomer(
        email: String @doc(description: "email of the customer")
    ): Testcustomer @resolver(class:"Magenest\\GraphQl\\Model\\Resolver\\Customer") @doc(description:
    "The testcustomer query returns information about a customer") @cache(cacheable: false)
}
type Testcustomer @doc(description: "Testcustomer defines the customer name and other details") {
    entity_id: Int
    firstname: String
    lastname: String
    email: String
}
```

- “type Query”: определяет Query операцию в модуле
- “testcustomer”: название запроса
- email: String : определяет имя поля запроса (‘email’) и его тип (‘string)
- Testcustomer : определяет сущность запроса, класс-резолвер запроса (@resolver), комментарий (@doc), кэшируемый или нет запрос (@cache)
Here we have defined “cacheable: false” meaning the result will not be cached. If the result can be cached, use @cache tag and define a caching class instead.
- “type Testcustomer” : определяет результирующий тип после выполнения запроса вызовом его резолвера

2. Создание резолвера запроса (см. в примере модуля) app/code/Magenest/GraphQl/Model/Resolver/Customer.php
Класс-резолвер должен имплементировать интерфейс Magento\Framework\GraphQl\Query\ResolverInterface
   
###Тестирование

1. Устанавливаем расширение браузера ChromeiQL
2. Запускаем его и устанавливаем Set endpoint: http://[domen]/graphql
3. Делаем запрос:
```
{
  testcustomer(email:"test@email.com") {
   	email
  	id
    firstname
  }
}

```
   
###Кэширование запросов

Magento может кэшировать страницы, отрендеренные по результатам определенных запросов GraphQL, с кешированием всей страницы. 
Полностраничное кэширование улучшает время отклика и снижает нагрузку на сервер.
Кэшировать можно только запросы, отправленные с помощью HTTP GET. Запросы POST нельзя кэшировать.

GraphQL позволяет создавать несколько запросов за один вызов. 
Если один из них Magento не кеширует, тогда FPC не кэширует весь запрос. Magento кэширует запросы:

- category (deprecated)
- categoryList
- cmsBlocks
- cmsPage
- products
- urlResolver

Не кэширует:

- cart
- country
- countries
- currency
- customAttributeMetadata
- customer
- customerDownloadableProducts
- customerOrders
- customerPaymentTokens
- storeConfig
- wishlist

В режиме разработки Magento возвращает несколько хэдэров для дэбага кэширования.

<table>
  <thead>
    <tr>
      <th>Header</th>
      <th>Description</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code class="language-plaintext highlighter-rouge">X-Magento-Cache-Debug</code></td>
      <td>HIT (the page was loaded from cache) or MISS (the page was not loaded from cache.</td>
    </tr>
    <tr>
      <td><code class="language-plaintext highlighter-rouge">X-Magento-Tags</code></td>
      <td>A list of cache tags that correspond to the catalog, category, or CMS items returned in the query. Magento caches these items.</td>
    </tr>
  </tbody>
</table>

Для кэширования запросов необходимо добавить резолвер кэша для запроса. Метод будет возвращать тэги, по которым FPC будет принимать решении о кэшировании результата запроса по этим тэгам.

Например файл модуля /etc/schema.graphqls станет:

```
type Query {
    testcustomer(
        email: String @doc(description: "email of the customer")
    ): Testcustomer
    @resolver(class: "Magento\\SampleGraphQlEndpoint\\Model\\Resolver\\Customer") 
    @doc(description: "The testcustomer query returns information about a customer") 
    @cache(cacheIdentity: "Magento\\SampleGraphQlEndpoint\\Model\\Resolver\\CacheIdentity\\CustomerCacheIdentities")
}
type Testcustomer @doc(description: "Testcustomer defines the customer name and other details") {
    id: Int
    firstname: String
    lastname: String
    email: String
}
```

Пример реализации класса-резолвера тэгов CustomerCacheIdentities см. в примере модуля.

###Авторизация

Для работы с эндпоинтами необходим во многих случаях авторизация. Для этого необходимо получить от сервера токен.

1. Отправляем запрос для получения токена:
```
mutation {
  generateCustomerToken(email: "customer@example.com", password: "password") {
    token
  }
}
```

2. Полученный токен используем в хэдэре Authorization:

Authorization = Bearer <token>