##DI плагины в Magento 2

В Magento 2 вместо rewrite'ов, использовавшихся в первой версии,
появились плагины, которые позволяют переопределить поведение большинства методов, перехватив поток выполнения тремя способами:

- before
- after
- around

Они позволяют “вклиниваться” практически во все Public методы и переопределять их поведение. 
У плагинов также возможно установить приоритет их выполнения.
Чтобы создать плагин, т.е. сказать Magento, что для всех public методов (Только для Public!) класса X попробовать найти плагины (переопределения) 
в классе Y и выполнить их в определенной последовательности, нужно добавить следующие конфиги в di.xml (либо в adminhtml/di.xml или frontend/di.xml). 
Например, в следующем примере - класс X это Magento\Customer\Model\AccountManagement, класс Y – наш плагин Vendor\Module\Plugin\Model\AccountManagementPlugin:
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Magento\Customer\Model\AccountManagement">
        <plugin name="vendor_sample_plugin" type="Vendor\Module\Plugin\Model\AccountManagementPlugin" sortOrder="5"/>
    </type>
</config>
```

#####Плагин типа before
Плагин Before исполняется перед основным методом. Для его создания используется одноименный префикс, т.е. к названию public метода приписывается before. Например:
```
public function authenticate($username, $password){} #оригинальный метод
public function beforeAuthenticate(AccountManagement $subject, $username, $password){} #плагин
```

Например код плагина:
```	#app/code/Vendor/Module/Plugin/Model/AccountManagementPlugin.php
	
	<?php
	namespace Vendor\Module\Plugin\Model;

	use Magento\Customer\Model\AccountManagement\AccountManagementPlugin; 
	
	/**
	 * Class AccountManagementPlugin
	 *
	 * @category  Colgee
	 * @package   Colgee_Sample
	 */
	class AccountManagementPlugin
	{
		/**
		 * Before authenticate method
		 *
		 * @param AccountManagement $subject
		 * @param string $username
		 * @param string $password
		 * @return array
		 */
		public function beforeAuthenticate(AccountManagement $subject, $username, $password)
		{
			// For example
			$username = 'colgee';
						
			return [$username, $password];
		}
	}
```
Основные требования при написании таких плагинов:

- Плагин не обязательно наследовать от других классов;
- Нужно обязательно подключать тот класс, для которого создается плагин (use Magento\Customer\Model\AccountManagement;);
- Первым элементом плагина before передавать этот класс. Все его public методы будут доступны;
- Плагин должен возвращать массив тех аргументов, которые требуются в оригинальном методе. В нашем случае это return [$username, $password];. 
- Или не вощвращает ничего, если в оригинальный метод не передаются какие-либо параметры.

#####Плагин типа after

Тот же принцип, что и before, только выполняется после оригинального метода.  Например:
```
#app/code/Vendor/Module/Plugin/Model/AccountManagementPlugin.php
	
	<?php
	namespace Vendor\Module\Plugin\Model;

	use Vendor\Module\Plugin\Model\AccountManagementPlugin; 
	
	/**
	 * Class AccountManagementPlugin
	 *
	 * @category  Colgee
	 * @package   Colgee_Sample
	 */
	class AccountManagementPlugin
	{
		/**
		 * After authenticate method
		 *
		 * @param AccountManagement $subject
		 * @param string $username
		 * @param string $password
		 * @return array
		 */
		public function afterAuthenticate(AccountManagement $subject, $result)
		{
			// Do something with $result
								
			return $result;
		}
	}
```
Основные требования при написании таких плагинов:

- Первым элементом плагина after передается оригинальный класс. Вторым – $result
- Плагин after должен возвращать $result того типа, который возвращает оригинальный метод

#####Плагин типа around
Плагин around позволяет кастомизировать логику до и после оригинального метода. Например:

```
#app/code/Vendor/Module/Plugin/Modell/AccountManagementPlugin.php
	
	<?php
	namespace Vendor\Module\Plugin\Model;

	use Magento\Customer\Model\AccountManagement; 
	
	/**
	 * Class AccountManagementPlugin
	 *
	 * @category  Colgee
	 * @package   Colgee_Sample
	 */
	class AccountManagementPlugin
	{
		/**
		 * Around authenticate method
		 *
		 * @param AccountManagement $subject
		 * @param string $username
		 * @param string $password
		 * @return array
		 */
		public function aroundAuthenticate(AccountManagement $subject, \Closure $proceed)
		{
			// Do something before
		
			$proceed();
		
			// Do something after
		}
	}
```
Основные требования при написании таких плагинов:
- Вторым аргументом передается \Closure $proceed, callback оригинального метода.
- Не обязательно выполнять что-то до или после. Можно сразу вернуть $proceed();



####Сортировка плагинов
Плагины сортируются сначала по порядку сортировки, а затем по префиксу метода.

Пример: для метода с 3 плагинами (PluginA, PluginB, PluginC) со следующими методами и sortOrder:
```
PluginA (sortOrder = 10)
beforeDispatch ()
aroundDispatch ()
afterDispatch ()
PluginB (sortOrder = 20)
beforeDispatch ()
aroundDispatch ()
afterDispatch ()
PluginC (sortOrder = 30):
beforeDispatch ()
aroundDispatch ()
afterDispatch ()
```
Ход выполнения будет следующим:
```
PluginА :: beforeDispatch ()
PluginА :: aroundDispatch ()
PluginB :: beforeDispatch ()
PluginB :: aroundDispatch ()
PluginC :: beforeDispatch ()
PluginC :: aroundDispatch ()
PluginC :: afterDispatch ()
PluginB :: afterDispatch ()
PluginА :: afterDispatch ()
```

####Классы Interceptors

Генерируются Магентой в папке var/generation для классов, у которых есть плагины. Пример сгенерированного класса:
```
#generated/code/Dotdigitalgroup/Email/Block/Recommended/Interceptor.php

<?php
namespace Dotdigitalgroup\Email\Block\Recommended;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Block\Recommended
 */
class Interceptor extends \Dotdigitalgroup\Email\Block\Recommended implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Dotdigitalgroup\Email\Block\Helper\Font $font, \Dotdigitalgroup\Email\Model\Catalog\UrlFinder $urlFinder, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $font, $urlFinder, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getImage');
        if (!$pluginInfo) {
            return parent::getImage($product, $imageId, $attributes);
        } else {
            return $this->___callPlugins('getImage', func_get_args(), $pluginInfo);
        }
    }
}
```

Эти классы используются Магентой для реализации флоу применения плагинов для конкретного класса, как описано выше.