# Magento 2 setup scripts

При инсталяции и апгрейде модуля возможно необходимо сделать изменения в структуре базы данных или добавить данные в существующие таблицы.
Для этого есть определенные классы:

- InstallSchema - для обновления структуры базы при инстале модуля
- InstallData - для добавления данных при инстале модуля
- UpgradeSchema - для обновления структуры базы при апгрейде модуля
- UpgradeData - для добавления данных при апгрейде модуля
- Recurring
- Uninstall

Команда для установки/апгрейда модуля:

```
php bin/magento setup:upgrade
```

Пример написания скрипта для создания таблицы:
```
#app/code/Mageplaza/HelloWorld/Setup/InstallSchema.php

<?php

namespace Mageplaza\HelloWorld\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('mageplaza_helloworld_post')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('mageplaza_helloworld_post')
			)
				->addColumn(
					'post_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Post ID'
				)
				->addColumn(
					'name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false'],
					'Post Name'
				)
				->addColumn(
					'url_key',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Post URL Key'
				)
				->addColumn(
					'post_content',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'64k',
					[],
					'Post Post Content'
				)
				->addColumn(
					'tags',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Post Tags'
				)
				->addColumn(
					'status',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					1,
					[],
					'Post Status'
				)
				->addColumn(
					'featured_image',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Post Featured Image'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Post Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('mageplaza_helloworld_post'),
				$setup->getIdxName(
					$installer->getTable('mageplaza_helloworld_post'),
					['name', 'url_key', 'post_content', 'tags', 'featured_image'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['name', 'url_key', 'post_content', 'tags', 'featured_image'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}
		$installer->endSetup();
	}
}
```

Требования к скрипту:

- класс должен имплементировать \Magento\Framework\Setup\InstallSchemaInterface
- должен иметь install() метод с 2-мя параметрами, имплементирующие интерфейсы SchemaSetupInterface и ModuleContextInterface. 
SchemaSetupInterface предоставляет множество методов для работы с базой данных. ModuleContextInterface содержит единственный метод getVersion для получения текущей версии модуля.

Пример написания скрипта для добавления данных:
```
#app/code/Mageplaza/HelloWorld/Setup/InstallData.php

<?php

namespace Mageplaza\HelloWorld\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	protected $_postFactory;

	public function __construct(\Mageplaza\HelloWorld\Model\PostFactory $postFactory)
	{
		$this->_postFactory = $postFactory;
	}

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$data = [
			'name'         => "How to Create SQL Setup Script in Magento 2",
			'post_content' => "In this article, we will find out how to install and upgrade sql script for module in Magento 2. When you install or upgrade a module, you may need to change the database structure or add some new data for current table. To do this, Magento 2 provide you some classes which you can do all of them.",
			'url_key'      => '/magento-2-module-development/magento-2-how-to-create-sql-setup-script.html',
			'tags'         => 'magento 2,mageplaza helloworld',
			'status'       => 1
		];
		$post = $this->_postFactory->create();
		$post->addData($data)->save();
	}
}
```

Требования к скрипту такие же, как и к скрипту для обновления структуры таблиц.

Скрипты для апгрейда схемы и данных также запускаются при установке модуля. Также они запускаются при запуске апгрейда модуля.
Для версирования модуля используется аттрибут setup_version в файле module.xml.
Пример файла с указанием версии:
```
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Mageplaza_HelloWorld" setup_version="1.2.0">
    </module>
</config>
```

Пример апгрейд скрипта:
```
#app/code/Mageplaza/HelloWorld/Setup/UpgradeSchema.php

<?php
namespace Mageplaza\HelloWorld\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '1.2.0', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'mageplaza_helloworld_post' ),
				'test',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
					'nullable' => true,
					'length' => '12,4',
					'comment' => 'test',
					'after' => 'status'
				]
			);
		}



		$installer->endSetup();
	}
}
```

Пример, апгрейд скрипта данных:

```
#app/code/Mageplaza/HelloWorld/Setup/UpgradeData.php

<?php

namespace Mageplaza\HelloWorld\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
	protected $_postFactory;

	public function __construct(\Mageplaza\HelloWorld\Model\PostFactory $postFactory)
	{
		$this->_postFactory = $postFactory;
	}

	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		if (version_compare($context->getVersion(), '1.2.0', '<')) {
			$data = [
				'name'         => "Magento 2 Events",
				'post_content' => "Test content",
				'url_key'      => '/magento-2-module-development/magento-2-events.html',
				'tags'         => 'magento 2,mageplaza helloworld',
				'status'       => 1
			];
			$post = $this->_postFactory->create();
			$post->addData($data)->save();
		}
	}
}
```

Для анинстала данных и структуры базы данных применяются анинстал скрипты. 
Например, при пониженни версии модуля отрабатывают скрипты, которые были по версионности выше текущей версии модуля.
Если нет проверки версии, то код скрипта отрабатывает каждый раз при анинстале модуля.
Пример:

```
#app/code/Mageplaza/HelloWorld/Setup/Uninstall.php

<?php
namespace Mageplaza\HelloWorld\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
	public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();

		$installer->getConnection()->dropTable($installer->getTable('mageplaza_helloworld_post'));

		$installer->endSetup();
	}
}
```