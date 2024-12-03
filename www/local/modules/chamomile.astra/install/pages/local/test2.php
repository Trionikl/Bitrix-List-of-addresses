<? require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php"); ?>

<?
/* Подключитт лист адресов*/
$APPLICATION->IncludeComponent(
    "chamomile:list.addresses",
    "",
    array(),
    false
);
?>