<?php

if (\Bitrix\Main\Engine\CurrentUser::get()->getId()) {
    $APPLICATION->IncludeComponent(
        'bitrix:main.ui.grid',
        'excel',
        [
            'GRID_ID' => 'MY_GRID_ID',
            'COLUMNS' => $arResult['columns'],
            'ROWS' => $arResult['rows'],
            'AJAX_MODE' => 'Y',
            'AJAX_OPTION_JUMP' => 'N',
            'AJAX_OPTION_HISTORY' => 'N',
        ]
    );
}