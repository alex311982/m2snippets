# Magento 2 Extension Attributes

Атрибуты расширения. Атрибуты расширения являются новыми в Magento 2. Они используются для расширения функциональности и часто используют более сложные типы данных, чем настраиваемые атрибуты. Эти атрибуты не отображаются в графическом интерфейсе.

Создание расшияемого аттрибута:

1. Объявление в файле app/code/{Vendor}/{Module}/etc/extension_attributes.xml. Например:
```
<!-- etc/extension_attributes.xml -->
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Api/etc/extension_attributes.xsd">
    <extension_attributes for="Magento\Sales\Api\Data\OrderInterface">
        <attribute code="fooman_attribute" type="{Vendor}\{Module}\Api\Data\FoomanAttributeInterface" />
    </extension_attributes>
</config>
```

2. Создаем новый интерфейс для нашего аттрибута:
```
<?php

namespace {Vendor}\{Module}\Api\Data;

interface FoomanAttributeInterface
{
    const VALUE = 'value';

    /**
     * Return value.
     *
     * @return string|null 
     */
    public function getValue();

    /**
     * Set value.
     *
     * @param string|null $value
     * @return $this
     */
    public function setValue($value);
}
```

3. Создаем реализацию интерфейса:
```
<?php

namespace {Vendor}\{Module}\Model;

class FoomanAttribute extends \Magento\Framework\Api\AbstractSimpleObject implements {Vendor}\{Module}\Api\Data\FoomanAttributeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
```

4. Указываем префренс для нашего интерфейса в файле di.xml:
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <preference for="{Vendor}\{Module}\Api\Data\FoomanAttributeInterface" type="{Vendor}\{Module}\Model\FoomanAttribute" />
</config>
```

5. В отличии от Магенты 1 - необходимо отдельно реализовывать функционал для сохранения и выборки значения расшияемого аттрибута из базы данных.
Для этого подходит подход создания плагинов для соответствующих методов репозиториев. Например:
   
```
<!-- etc/di.xml -->
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Magento\Sales\Api\OrderRepositoryInterface">
        <plugin name="save_fooman_attribute" type="{Vendor}\{Module}\Plugin\OrderSave"/>
        <plugin name="get_fooman_attribute" type="{Vendor}\{Module}\Plugin\OrderGet"/>
    </type>
</config>
```

6. Реализовываем сами плагины.
```
<?php

namespace {Vendor}\{Module}\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;

class OrderSave
{

...

    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->saveFoomanAttribute($resultOrder);

        return $resultOrder;
    }

    private function saveFoomanAttribute(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (
            null !== $extensionAttributes &&
            null !== $extensionAttributes->getFoomanAttribute()
        ) {
            $foomanAttributeValue = $extensionAttributes->getFoomanAttribute()->getValue();
            try {
                // The actual implementation of the repository is omitted
                // but it is where you would save to the database (or any other persistent storage)
                $this->foomanExampleRepository->save($order->getEntityId(), $foomanAttributeValue);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __('Could not add attribute to order: "%1"', $e->getMessage()),
                    $e
                );
            }
        }
        return $order;
    }
```

Ддля того, чтобы добавить расширяемый аттрибут к ордеру после его фэтча из базы данных, необходимо создать плагин afterGet. Например:  

```
<?php

namespace Fooman\Example\Plugin;

class OrderGet
{

...  

    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getFoomanAttribute($resultOrder);

        return $resultOrder;
    }

    private function getFoomanAttribute(\Magento\Sales\Api\Data\OrderInterface $order)
    {

        try {
            // The actual implementation of the repository is omitted
            // but it is where you would load your value from the database (or any other persistent storage)
            $foomanAttributeValue = $this->foomanExampleRepository->get($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $foomanAttribute = $this->foomanAttributeFactory->create();
        $foomanAttribute->setValue($foomanAttributeValue);
        $orderExtension->setFoomanAttribute($foomanAttribute);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }
```

Таким образом расширяемые аттрибуты во второй версии Магенто - это сдвиг в стороны расширяемости и стабильности работы всей системы. 
Нет необходимости реврайтить целые модели с целью добавить функциональность. Расширяемость обеспечивается за счет расширения сервисных контрактов данных.
