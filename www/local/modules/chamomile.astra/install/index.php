<?php

// Разработка начинается с папки установки и файла index.php
// Индексный файл в папке install - основной файл установки, в котором прописывается класс модуля, функции установки, удаления, работа с этапами этих процессов
// В начале подключаются классы битрикса, которые будут использоваться и файлы локализации

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\ModuleManager;


Loc::loadMessages(__FILE__);

// Имя класса должно проецироваться от айди модуля (имени папки), точка заменяется на нижнее подчеркивание - обязательное условие.
// И должен наследоваться от CModule.
/**
 * chamomile_astra
 */
class chamomile_astra extends CModule
{
    public $arResponse = [
        "STATUS" => true,
        "MESSAGE" => ""
    ];

    /**
     * setResponse
     *
     * @param  mixed $status
     * @param  mixed $message
     * @return void
     */
    public function setResponse($status, $message = "")
    {
        $this->arResponse["STATUS"] = $status;
        $this->arResponse["MESSAGE"] = $message;
    }

    /**
     * __construct
     *
     * @return void
     */
    function __construct()
    {
        $arModuleVersion = array();

        // Подключение файла версии, который содержит массив для модуля
        require(__DIR__ . "/version.php");

        // Поля заполняются в переменных класса для удобства работы
        $this->MODULE_ID = "chamomile.astra"; // Имя модуля

        // Переменная пути до папки с компонентами, для опциональной установки в папку local
        $this->COMPONENTS_PATH = $_SERVER["DOCUMENT_ROOT"] . "/local/components";

        // Переменная пути до папки со страницами, для опциональной установки
        $this->PAGES_PATH = $_SERVER["DOCUMENT_ROOT"] . "/";

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("CHAMOMILE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("CHAMOMILE_MODULE_DESCRIPTION");

        // Имя партнера создавшего модуль (Выводится информация в списке модулей о человеке или компании, которая создала этот модуль)
        $this->PARTNER_NAME = Loc::getMessage("CHAMOMILE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("CHAMOMILE_PARTNER_URI");

        // Если указано, то на странице прав доступа будут показаны администраторы и группы (страницу сначала нужно запрограммировать)
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
        // Если указано, то на странице редактирования групп будет отображаться этот модуль
        $this->MODULE_GROUP_RIGHTS = "Y";
    }

    // Установка баз данных    
    /**
     * installDB
     *
     * @return void
     */
    function installDB()
    {
        Loader::includeModule($this->MODULE_ID);

        //Создать таблицу года в HL Блоке
        if (!\Chamomile\Astra\hl\UserAddressesTable::checkHlTableExists()) {
            \Chamomile\Astra\hl\UserAddressesTable::createTable();
        }

        // таблица событий в Битрикс "b_module_to_module"
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(
            '',
            'UserAddressesOnAfterUpdate',
            $this->MODULE_ID,
            '\\Chamomile\\Astra\\Events\\UserAddressesEvents',
            'deleteCache'
        );
    }

    // При установке
    function installEvents() {}

    // Копирование файлов    
    /**
     * installFiles
     *
     * @return void
     */
    function installFiles()
    {
        // Проверим существовавание папки перед записью, если она есть, то удалим
        $this->unInstallFiles();
        $resMsg = "";
        // Скопируем компоненты из папки в битрикс
        $res = CopyDirFiles(
            __DIR__ . "/components",
            $_SERVER["DOCUMENT_ROOT"] . "/local/components",
            true, // Перезаписывает файлы
            true  // Копирует рекурсивно
        );

        if (!$res)
            $resMsg = Loc::getMessage("CHAMOMILE_INSTALL_ERROR_FILES_ADM");

        // Скопируем страницы из папки в битрикс
        $res = CopyDirFiles(
            __DIR__ . "/pages",
            $this->PAGES_PATH,
            true, // Перезаписывает файлы
            true  // Копирует рекурсивно
        );

        if (!$res)
            $resMsg = ($resMsg) ? $resMsg . "; " . Loc::getMessage("CHAMOMILE_INSTALL_ERROR_FILES_COM") : Loc::getMessage("CHAMOMILE_INSTALL_ERROR_FILES_COM");
        if ($resMsg) {
            $this->setResponse(false, $resMsg);
            return false;
        }
        $this->setResponse(true);

        return true;
    }

    // Заполнение таблиц тестовыми данными    
    /**
     * addTestData
     *
     * @return void
     */
    function addTestData()
    {
        Loader::includeModule($this->MODULE_ID);


        // Таблицца со списком адресов, тестовые данные
        $arUserAddressesTable = [
            [
                "UF_USER_ID" => 1,
                "UF_ADDRESS" => "Москва, ул. Ленина, 10",
                "UF_ACTIVITY" => true,
            ],
            [
                "UF_USER_ID" => 2,
                "UF_ADDRESS" => "Москва, ул. Королева, 15",
                "UF_ACTIVITY" => false,
            ],
            [
                "UF_USER_ID" => 1,
                "UF_ADDRESS" => "Москва, ул. Королева, 50",
                "UF_ACTIVITY" => false,
            ],
        ];

        // Записать данные в таблицу список адресов
        \Chamomile\Astra\hl\UserAddressesTable::addData($arUserAddressesTable);

        return true;
    }

    // Для удобства проверки результата    
    /**
     * checkAddResult
     *
     * @param  mixed $result
     * @return void
     */
    function checkAddResult($result)
    {
        if ($result->isSuccess()) {
            return [true, $result->getId()];
        }

        return [false, $result->getErrorMessages()];
    }

    // Основная функция установки, должна называться именно так, поэтапно производим установку нашего модуля    
    /**
     * DoInstall
     *
     * @return void
     */
    function DoInstall()
    {
        global $APPLICATION;

        // Пример с установкой в один шаг:
        // Если необходимо использовать ORM сущности при установке (например для создания таблицы в бд), то нужно регистрировать его до вызова создания таблиц и т.п.
        // Иначе не сможем использовать неймспейсы
        // ModuleManager::registerModule($this->MODULE_ID);
        // $this->installDB();
        // $this->installEvents();
        // $this->installAgents();
        // if (!$this->installFiles())
        //     $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
        // if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/step.php"))
        //     $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/step.php");
        // else
        //     $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/chamomile.astra/install/step.php");

        // Пример с установкой в несколько шагов:
        // Получаем контекст и из него запросы
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        // Проверяем какой сейчас шаг, если он не существует или меньше 2, то выводим первый шаг установки
        if ($request["step"] < 2) {
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/step1.php"))
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/step1.php");
            else
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/chamomile.astra/install/step1.php");
        } elseif ($request["step"] == 2) {
            // Если шаг второй, то приступаем к установке
            // Если необходимо использовать ORM сущности при установке (например для создания таблицы в бд), то нужно регистрировать его до вызова создания таблиц и т.п.
            // Иначе не сможем использовать неймспейсы

            // Глянуть все языковые константы по установке и удалению модулей - https://github.com/devsandk/bitrix_utf8/blob/master/bitrix/modules/main/lang/ru/admin/partner_modules.php

            ModuleManager::registerModule($this->MODULE_ID);
            $this->installDB();
            if (!$this->installFiles())
                $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
            if ($request["add_data"] == "Y") {
                $result = $this->addTestData();
                if ($result !== true)
                    $APPLICATION->ThrowException($result);
            }
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/step2.php"))
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/step2.php");
            else
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/chamomile.astra/install/step2.php");
        }
    }

    // Удаление файлов    
    /**
     * unInstallFiles
     *
     * @return void
     */
    function unInstallFiles()
    {

        if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/components/" . $this->MODULE_ID))
            $res = DeleteDirFilesEx("/local/components/" . $this->MODULE_ID);

        /** удалить страницы*/
        if (is_dir($this->PAGES_PATH)) {
            $res = DeleteDirFilesEx("/local/test2.php");
        }

        if (!$res)
            $resMsg = Loc::getMessage("CHAMOMILE_UNINSTALL_ERROR_FILES_COM");
        if ($resMsg) {
            $this->setResponse(false, $resMsg);
            return false;
        }
        $this->setResponse(true);

        return true;
    }


    /**
     * unInstallDB
     *
     * @return void
     */
    function unInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        // Удаление таблицы
        if (\Chamomile\Astra\hl\UserAddressesTable::checkHlTableExists()) {
            \Chamomile\Astra\hl\UserAddressesTable::deleteTable();
        }

        // удаление события
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            '',
            'UserAddressesOnAfterUpdate',
            $this->MODULE_ID,
            '\\Chamomile\\Astra\\Events\\UserAddressesEvents',
            'deleteCache'
        );
    }

    // Основная функция удаления, должна называться именно так, поэтапно производим удаление нашего модуля    
    /**
     * DoUninstall
     *
     * @return void
     */
    function DoUninstall()
    {
        global $APPLICATION;
        // Получаем контекст и из него запросы
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        // Проверяем какой сейчас шаг, если он не существует или меньше 2, то выводим первый шаг удаления
        if ($request["step"] < 2) {
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/unstep1.php"))
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/unstep1.php");
            else
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/unstep1.php");
        } elseif ($request["step"] == 2) {
            // Если шаг второй, то приступаем к удалению
            if ($request["save_data"] != "Y")
                $this->unInstallDB();
            if (!$this->unInstallFiles())
                $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
            ModuleManager::unRegisterModule($this->MODULE_ID);
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/unstep2.php"))
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/unstep2.php");
            else
                $APPLICATION->IncludeAdminFile(Loc::getMessage("CHAMOMILE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/chamomile.astra/install/unstep2.php");
        }
    }

    // Функция для определения возможных прав
    // Если не задана, то будут использованы стандартные права (D,R,W)
    // Должна называться именно так и возвращать массив прав и их названий    
    /**
     * GetModuleRightList
     *
     * @return void
     */
    function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D", "K", "S", "W"),
            "reference" => array(
                "[D] " . Loc::getMessage("CHAMOMILE_DENIED"),
                "[K] " . Loc::getMessage("CHAMOMILE_READ_COMPONENT"),
                "[S] " . Loc::getMessage("CHAMOMILE_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("CHAMOMILE_FULL"),
            )
        );
    }
}