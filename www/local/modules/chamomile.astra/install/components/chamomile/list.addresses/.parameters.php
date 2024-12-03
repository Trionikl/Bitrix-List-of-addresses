<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arComponentParameters = array(
        "PARAMETERS" => array(
                "ADDRESS_OUTPUT" => array(
                        "PARENT" => "BASE",
                        "NAME" => "Вывод адреса",
                        "TYPE" => "LIST",
                        "VALUES" => array(
                                "FULL" => "Все адреса ",
                                "ACTIVE_ADDRESSES" => "Только активные"
                        ),
                        "DEFAULT" => "FULL",
                        "REFRESH" => "Y"
                ),
        )
);