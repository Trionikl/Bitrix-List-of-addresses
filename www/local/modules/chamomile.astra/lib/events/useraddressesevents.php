<?php

/**
 * Обработка соббытия редактирования HL-блока со списком адресов
 */

namespace Chamomile\Astra\Events;


//  работает через регистрацию своего события, по умолчанию такого события нет
/**
 * UserAddressesEvents
 */
class UserAddressesEvents
{
    /**
     * Deletes the cache for the list of user addresses when a Highload Block (HL-block) is edited.
     *
     * @param int $idHlBlock The ID of the edited HL-block.
     *
     * @return void
     */
    public static function deleteCache($idHlBlock)
    {

        $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache(); // Служба пометки кеша тегами
        $taggedCache->clearByTag('chamomile_list_users');
    }
}