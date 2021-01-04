#Кэш-тэги

Кэш-тэги позволяют инвалидировать закэшированный в FPC контент при измении одной из сущностей, которая имеет кэш-тэг.
Они генерируются на блочном уровне. Каждый блок при этом должен имплементировать IdentityInterface и таким образом реализовывать getIdentities метод, который должен возвращать уникальный идентификатор.
Например для целого блока:

```
namespace Magento\Cms\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\DataObject\IdentityInterface;

class Page extends AbstractBlock implements IdentityInterface
{
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Page::CACHE_TAG . '_' . $this->getPage()->getId()];
    }
}
```

Данный метод может возвращать неограниченное количество тэгов. Например, для страницы каталога применяется тэг текущей категории и несколько тэгов продуктов этой категории.

После того как Магенто сформировала ответ на запрос из браузера FPC собирает все тэги из лэаута и формируеи кастомный хэдэр X-Magento-Tags. FPC системы по разному интерпретируют его.
Например, Варниш сохраняет его вместе с кэшируемой страницей, поэтому не требуется дополнительной работы. Встроенный в Магенто FPC система анализирует этот хэдэр.

Есть 2 модуля для инвалидации кэша страниц по тэгам - Magento_PageCache и Magento_CacheInvalidate. Первый используется встроенным FPC фронтендом, второй - Варнишем.
После сохранения модели генерится событие clean_cache_by_tags. 
После этого тэги, возращенные методом getIdentities этой модели, направляются в FPC фронтенд. 
После этого происходит сброс всех закэшированных сущностей, связанных с этими тэгами.

Шаги:
1. \Magento\PageCache\Model\Layout\LayoutPlugin::afterGetOutput - получение всех тэгов в блоках, которые имплементируют интерфейс \Magento\Framework\DataObject\IdentityInterface.
Формируется хэдэр X-Magento-Tags и добавляется к респонсу. В этом хэдэре содержатся через запятую уникальные тэги.
   
2. Например, для страницы с продуктом метод \Magento\Catalog\Block\Product\View::getIdentities вызывает метод \Magento\Catalog\Model\Product::getIdentities для получения массива тэгов продукта.

3. \Magento\Framework\App\PageCache\Kernel::process обрабатывает куку X-Magento-Tags и направляет тэги вместе с респонсом в FPC.
К примеру, если это страница каталога, то она будет содержать все тэги продуктов этой категории. При изменении продукта - сбросится FPC этой страницы каталога.
   
4. При сохранении модели в админке в методе \Magento\Framework\Model\AbstractModel::afterSave генерится событие clean_cache_by_tags.

5. Обзервер Magento\PageCache\Observer\FlushCacheByTags модуля Magento_PageCache сбрасывает кэш для встроенного FPC фронта.
   
6. Обзервер Magento\CacheInvalidate\Observer\InvalidateVarnishObserver модуля Magento_CacheInvalidate сбрасывает кэш для Варниша.

Т.е. если страница содержит блок, содержимое которого зависит от какой-то сущности, которая изменяется и требует изменение содержимого блока, то необходимо сделать кастомный блок, имплементирующий IdentityInterface. 
Реализовать в нем метод  getIdentities, который будет возвращать тэги той сущности, от которой зависит содержимое самого блока.
