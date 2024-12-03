# Bitrix-List-of-addresses
Список адресов текущего авторизованного пользователя для "Битрикс управление сайтом"

Моудуль chamomile.astra устанавливает на сайт компонент который осуществляет вывод адресов текущего пользователя из highload блока.

У компонента есть 1 параметр: выводить все адреса или только активные.

Для вывода таблицы используется bitrix:main.ui.grid.

Используется class.php и d7 api.

Реализован кеш компонента, при изменении данных в highload блоке сбрасывается кеш (по событию). Использован тегированный кеш для того чтобы отлавливать событие изменения highload блока и сбрасывать кеш по тегу




Установка:    
Установить как обычный модуль битрик управление сайтом в разделе 

"Ваш сайт/bitrix/admin/partner_modules.php?lang=ru"

Скриншоты 

![alt text](screenshots/01.png "Интеграция готовой вёрстки каталога 01")    
![alt text](screenshots/02.png "Интеграция готовой вёрстки каталога 02") 
![alt text](screenshots/03.png "Интеграция готовой вёрстки каталога 03") 
![alt text](screenshots/04.png "Интеграция готовой вёрстки каталога 04") 
![alt text](screenshots/05.png "Интеграция готовой вёрстки каталога 05") 
![alt text](screenshots/06.png "Интеграция готовой вёрстки каталога 06") 
![alt text](screenshots/07.png "Интеграция готовой вёрстки каталога 07")
