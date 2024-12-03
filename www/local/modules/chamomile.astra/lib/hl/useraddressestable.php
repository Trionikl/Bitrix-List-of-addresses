<?php

namespace Chamomile\Astra\hl;


use \Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use \Bitrix\Highloadblock\HighloadBlockTable;


// Имя должно обязательно содержать Table, название файла не обязательно
/**
 * UserAddressesTable
 */
class UserAddressesTable extends Entity\DataManager
/**
 * Класс UserAddressesTable представляет собой менеджер данных для таблицы пользовательских адресов.
 * Он предоставляет методы для работы с Highload-блоком, в котором хранятся данные об адресах пользователей.
 * Класс отвечает за создание, проверку существования, добавление данных и удаление таблицы пользовательских адресов.
 * Кроме того, он обрабатывает события обновления записей в таблице, очищая кеш списка пользователей.
 */
{
    /**
     * modules
     *
     * @return void
     */
    protected static function modules()
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * getName
     *
     * @return void
     */
    public static function getName()
    {
        return "UserAddresses";
    }

    // Название таблицы в базе данных:
    // Если не указывать данную функцию, то таблица в бд сформируется автоматически из неймспейса
    // Например: b_chieff_books_book    
    /**
     * getTableName
     *
     * @return void
     */
    public static function getTableName()
    {
        return "ch_user_addresses";
    }

    //Проверить существует ли HL таблица    
    /**
     * checkHlTableExists
     *
     * @return void
     */
    public static function checkHlTableExists()
    {
        self::modules();
        // Получаем информацию о Highload-блоке из БД
        $hlblock = HighloadBlockTable::getList([
            'filter' => ['NAME' => self::getName()]
        ])->fetch();

        // Проверяем существование Highload-блока
        if ($hlblock) {
            return true;
        } else {
            return false;
        }
    }

    // Если не указывать, то будет использовано значение по умолчанию подключения к бд из файла .settings.php
    // Если указать, то можно выбрать подключение, которое может быть описано в .setting.php    
    /**
     * createTable
     *
     * @return void
     */
    public static function createTable()
    {
        self::modules();
        $result = HighloadBlockTable::add(array(
            'NAME' => self::getName(), //должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
            'TABLE_NAME' => self::getTableName(), //должно состоять только из строчных латинских букв, цифр и знака подчеркивания   
        ));
        if (!$result->isSuccess()) {
            return $result->getErrorMessages();
        } else {
            $hlBlockId = $result->getId();
            self::addField($hlBlockId);
            return $hlBlockId;
        }
    }

    // добавить поля в таблицу    
    /**
     * addField
     *
     * @param  mixed $hlBlockId
     * @return void
     */
    public static function addField($hlBlockId)
    {
        self::modules();
        // Добавить поля в таблицу
        $arFields = [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlBlockId,
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'USER_TYPE_ID' => 'integer',
                'FIELD_NAME' => 'UF_USER_ID',
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlBlockId,
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'USER_TYPE_ID' => 'string',
                'FIELD_NAME' => 'UF_ADDRESS',
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlBlockId,
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'USER_TYPE_ID' => 'boolean',
                'FIELD_NAME' => 'UF_ACTIVITY',
            ],
        ];

        $obUserTypeEntity = new \CUserTypeEntity();
        foreach ($arFields as $value) {
            $fieldID = $obUserTypeEntity->Add($value);
        }

        return $fieldID;
    }

    /**
     * addData
     *
     * @param  mixed $arData
     * @return void
     */
    public static function addData($arData)
    {
        self::modules();
        $obEntity = HighloadBlockTable::compileEntity(self::getName());
        $strEntityDataClass = $obEntity->getDataClass();

        foreach ($arData as $value) {
            $obResult = $strEntityDataClass::add(
                array(
                    'UF_USER_ID' => $value['UF_USER_ID'],
                    'UF_ADDRESS' => $value['UF_ADDRESS'],
                    'UF_ACTIVITY' => $value['UF_ACTIVITY'],
                )
            );
            // можем сразу получить информацию о добавленном поле
            $ID = $obResult->getID();
            $bSuccess[$ID] = $obResult->isSuccess();
        }

        return $bSuccess;
    }

    //сбросить кеш при изменении значения в таблице
    /**
     * Handles actions to be performed after an update to the user addresses table.
     *
     * This method is called after a record in the user addresses table has been updated.
     * It clears the cache tag 'chamomile_list_users' to ensure the updated data is reflected
     * in any cached lists of users.
     *
     * @param Entity\Event $event The event object containing information about the update.
     */

    /**
     * deleteTable
     *
     * @return void
     */
    public static function deleteTable()
    {
        self::modules();
        //удаление hl-блока
        $hlblock = HighloadBlockTable::getList(
            array("filter" => array(
                'TABLE_NAME' => self::getTableName()
            ))
        )->fetch();
        HighloadBlockTable::delete($hlblock['ID']);
    }
}