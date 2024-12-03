<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Data\Cache;
use \Bitrix\Main\Application;

if ($this->checkModule()) {

    // Тегированный кеш
    $cache = Cache::createInstance(); // Служба кеширования
    $taggedCache = Application::getInstance()->getTaggedCache(); // Служба пометки кеша тегами

    /*
     * Чтобы тегированный кеш нашел что ему сбрасывать, необходим
     * одинаковый путь в $cache->initCache() и  $taggedCache->startTagCache()
     * У нас путь указан в $cachePath
     */
    $cachePath = 'chamomile';
    $cacheTtl = 3600;
    $cacheKey = 'listuserscachekey';

    if ($cache->initCache($cacheTtl, $cacheKey, $cachePath)) {
        $arResult = $cache->getVars();

        /*
         * Еще тут можно вывести данные в браузер, через $cache->output();
         * Тогда получится замена классу CPageCache
         */
    } elseif ($cache->startDataCache()) {
        // Начинаем записывать теги
        $taggedCache->startTagCache($cachePath);
        $arResult = $this->getAll();

        // Добавляем теги

        // Кеш сбрасывать при изменении данных chamomile_list_users
        $taggedCache->registerTag('chamomile_list_users');

        // Если что-то пошло не так и решили кеш не записывать
        $cacheInvalid = false;
        if ($cacheInvalid) {
            $taggedCache->abortTagCache();
            $cache->abortDataCache();
        }

        // Всё хорошо, записываем кеш
        $taggedCache->endTagCache();
        $cache->endDataCache($arResult);
    }
    // Данные будут обновляться раз в час или при обновлении данных в HL Блоке UserAddresses


    $this->IncludeComponentTemplate();
}