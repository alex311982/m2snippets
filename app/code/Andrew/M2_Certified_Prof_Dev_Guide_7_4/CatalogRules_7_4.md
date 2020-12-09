# 7.4 Определение ценовых правил каталога (Catalog Price Rules) и управление ими
## Как реализовать ценовые правила каталога? Когда вы будете использовать ценовые правила каталога?
Ценовые правила каталога будут использоваться для установки скидки, в зависимости от условий, для
продуктов или группы продуктов. Чтобы создать их в панели администратора, перейдите в раздел ```Marketing -> Catalog Price Rule```. 
Здесь администратор может указать условия, при которых ценовые правила каталога могут применяться к товарам, действиям, которые необходимо выполнить, и другим параметрам.

### Как они влияют на производительность?
Влияние на загрузку страницы относительно невелико, так как все расчеты цен происходят во время переиндексции. 
На скорость переиндексации влияют: количество ценовых правил каталога, групп заказчиков, продукты (количество) и веб-сайты. 
В худшем случае, если все ценовые правила каталога влияют на все группы клиентов, на все продукты и все веб-сайты, тогда количество строк в таблице ``catalogrule_product``  будет исчисляться формулой:
``CATALOG_RULES_QTY * CUSTOMER_GROUPS_QTY * PRODUCTS_QTY * WEBSITES_QTY.``
В таблице ``catalogrule_product_price`` содержится в 3 раз больше строк, чем в таблице ``catalogrule_product``.

**_Давайте рассмотрим один пример конфигурации_**:

У нас есть одно правило каталога, которое применяется к 4 группам клиентов, одному веб-сайту и 247
товарам. В этом случае количество строк в таблицах будет следующим:

``catalogrule_product``: 1 * 4 * 1 * 247 = 988

``Catalogrule_product_price``: 988 * 3 = 2964.

_**Другой пример**_:

У нас есть 5 правил каталога, каждое из которых касается 4 групп клиентов, 5 веб-сайтов и 2000
товаров. Тогда количество строк будет следующим:

``catalogrule_product``: 5 * 4 * 5 * 2000 = 200000

``Catalogrule_product_price``: 200000 * 3 = 600000.

Теперь мы рассмотрим причину, по которой влияние на загрузку страницы относительно незначительно. 
Обратимся к коду Magento.

В файле **_vendor \ magento \ module-catalog-rule \ etc \ frontend \ events.xml_** установлено обсервер
для события ``catalog_product_get_final_price``.

<img src="https://i.imgur.com/XuicwTk.png" width="1200">

```
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="catalog_product_get_final_price">
        <observer name="catalogrule" instance="Magento\CatalogRule\Observer\ProcessFrontFinalPriceObserver" />
    </event>
    <event name="prepare_catalog_product_collection_prices">
        <observer name="catalogrule" instance="Magento\CatalogRule\Observer\PrepareCatalogProductCollectionPricesObserver" />
    </event>
</config>
```

Затем в файле **_magento \ module-catalog-rule \ Observer \ ProcessFrontFinalPriceObserver.php_** , получаем цену и присваиваем ее товару.

<img src="https://i.imgur.com/Tx3qb7L.png" width="1200">

```
 public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $pId = $product->getId();
        $storeId = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = new \DateTime($observer->getEvent()->getDate());
        } else {
            $date = $this->localeDate->scopeDate($storeId);
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = $this->storeManager->getStore($storeId)->getWebsiteId();
        }

        if ($observer->hasCustomerGroupId()) {
            $gId = $observer->getEvent()->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $gId = $product->getCustomerGroupId();
        } else {
            $gId = $this->customerSession->getCustomerGroupId();
        }

        $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}";
        if (!$this->rulePricesStorage->hasRulePrice($key)) {
            $rulePrice = $this->resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
            $this->rulePricesStorage->setRulePrice($key, $rulePrice);
        }
        if ($this->rulePricesStorage->getRulePrice($key) !== false) {
            $finalPrice = min($product->getData('final_price'), $this->rulePricesStorage->getRulePrice($key));
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }

```

Далее давайте посмотрим, что происходит в методе getRulePrice (). Перейдите к файлу
**_vendor \ magento \ module-catalog-rule \ Model \ ResourceModel \ Rule.php_** и найдите
метод getRulePrices (), где пердставлен простейший запрос в базу данных в таблицу ``catalogrule_product_price``. Вот как мы получаем цену.

<img src="https://i.imgur.com/3vfkW4u.png" width="1200">

```
    /**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     * Collect data with  product Id => price pairs
     *
     * @param \DateTimeInterface $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $productIds
     * @return array
     */
    public function getRulePrices(\DateTimeInterface $date, $websiteId, $customerGroupId, $productIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('catalogrule_product_price'), ['product_id', 'rule_price'])
            ->where('rule_date = ?', $date->format('Y-m-d'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id IN(?)', $productIds);

        return $connection->fetchPairs($select);
    }
```


## Отладка проблем с правилами цен каталога (Catalog Price Rules)
1. Во-первых, убедитесь, что правило цены активно. Перейдите в раздел ``Marketing > promotions > Catalog
   Price Rule`` и убедитесь, что статус установлен в  **"active"**.

2. Примените правила, нажав соответствующую кнопку Применить правила.
   
   <img src="https://i.imgur.com/99ZRWIp.png" width="1200">

3. После этого, индексы ``Catalog Rule Product`` и ``Product Price``, связанная с ним, устанавливаются как
"invalid".

   <img src="https://i.imgur.com/3vfkW4u.png" width="1200">   

4. В экшене контроллера **_vendor\magento\module-catalog-rule\Controller\Adminhtml\Promo\Catalog\ApplyRule
s.php_**, создается копия класса **_\Magento\CatalogRule\Model\Rule\Job_**, в котором вызывается метод ``applyAll()``
. 

<img src="https://i.imgur.com/v7FfJK5.png" width="1200">

```
    /**
     * Apply all active catalog price rules
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $errorMessage = __('We can\'t apply the rules.');
        try {
            /** @var Job $ruleJob */
            $ruleJob = $this->_objectManager->get(\Magento\CatalogRule\Model\Rule\Job::class);
            $ruleJob->applyAll();

            if ($ruleJob->hasSuccess()) {
                $this->messageManager->addSuccessMessage($ruleJob->getSuccess());
                $this->_objectManager->create(\Magento\CatalogRule\Model\Flag::class)->loadSelf()->setState(0)->save();
            } elseif ($ruleJob->hasError()) {
                $this->messageManager->addErrorMessage($errorMessage . ' ' . $ruleJob->getError());
            }
        } catch (\Exception $e) {
            $this->_objectManager->create(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addErrorMessage($errorMessage);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog_rule/*');
    }

```

5. В этом методе индекс устанавливается как "invalid".

<img src="https://i.imgur.com/NX4XsEE.png" width="1200">

```
    /**
     * Dispatch event "catalogrule_apply_all" and set success or error message depends on result
     *
     * @return \Magento\CatalogRule\Model\Rule\Job
     * @api
     */
    public function applyAll()
    {
        try {
            $this->ruleProcessor->markIndexerAsInvalid();
            $this->setSuccess(__('Updated rules applied.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->setError($e->getMessage());
        }
        return $this;
    }
```

6. Затем запустите переиндексацию  и очистите кеш.
   
```
    bin/magento indexer:reindex 
    bin/magento cache:flush
```   

   После всех этих действий, в случае если правильно настроено ценовое правило (если диапазон дат и
   условия правильные), правила будут работать.

   Если правила не работают, перейдите к таблице ``catalogrule_product_price`` и проверьте там цену товара есть. 
   Если данные неверны, попробуйте проверить логи, отключив сторонние модули, или просмотрите процесс реиндекса Catalog Price Rule с помощью xDebug.