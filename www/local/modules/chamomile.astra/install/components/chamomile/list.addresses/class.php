<?php

use \Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;


class ListUserAddresses extends CBitrixComponent
{
    /**
     * checkModule
     *
     * @return void
     */
    protected function checkModule()
    {
        if (!Loader::includeModule("chamomile.astra")) {
            ShowError(Loc::getMessage("MODULE_NOT_INSTALLED"));
            return false;
        }

        if (!Loader::includeModule("highloadblock")) {
            ShowError(Loc::getMessage("HL_ERROR"));
            return false;
        }

        return true;
    }

    /**
     * getAll
     *
     * @return void
     */
    function getAll()
    {
        // Имя вашего HL блока
        $hlblockName = 'ch_user_addresses'; // Замените на ваше имя

        // Получение ID HL блока по имени
        $hlblock = HL\HighloadBlockTable::getRow([
            'select' => ['ID'],
            'filter' => ['TABLE_NAME' => $hlblockName],
        ]);

        if (!$hlblock) {
            die('HL блок с именем ' . $hlblockName . ' не найден.');
        }

        $hlblockId = $hlblock['ID'];

        // Получение объекта HL блока
        $hlblock = HL\HighloadBlockTable::getById($hlblockId)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();

        //проверка настрое компонента
        switch ($this->arParams['ADDRESS_OUTPUT']) {
            case 'FULL':
                $outputAddress = [0, 1]; // Замените на ваши значения
                break;

            case 'ACTIVE_ADDRESSES':
                $outputAddress = 1;
                break;

            default:
                $outputAddress = [0, 1]; // Замените на ваши значения
                break;
        }

        // Получение данных из HL блока
        $result = $entityDataClass::getList([
            'select' => ['*'], // Выберите все поля
            'filter' => [
                'UF_USER_ID' => \Bitrix\Main\Engine\CurrentUser::get()->getId(),
                '=UF_ACTIVITY' => $outputAddress,
            ], // Фильтр, если необходимо
        ]);

        // Массив для хранения данных
        $dataArray = [];

        // Обработка результатов
        while ($row = $result->fetch()) {
            $dataArray[] = $row;
        }

        //создать колонки для вывода в таблице
        $arResult['columns'] = [
            ['id' => 'USER_ID', 'name' => 'ID Пользователя', 'default' => true],
            ['id' => 'ADDRESS', 'name' => 'Адрес', 'default' => true],
        ];

        //создать строки для вывода в таблице
        foreach ($dataArray as $key => $value) {
            $arResult['rows'][$key]["id"] = $key;
            $arResult['rows'][$key]["columns"]["USER_ID"] = $value["UF_USER_ID"];
            $arResult['rows'][$key]["columns"]["ADDRESS"] = $value["UF_ADDRESS"];
        }

        return $arResult;
    }
}