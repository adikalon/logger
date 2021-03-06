<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hellpers\Logger;

/*
|------------------------------------------------------------------------------
| Пример
|------------------------------------------------------------------------------
|
| 1. Создаем объект Hellpers\Logger, передавая в него абсолютный путь к корню
| приложения;
| 2. Устанавливаем параметры (о них чуть ниже);
| 3. Метод send() - отправляет лог.
|
*/
$logger = new Logger(__DIR__);

$logger->path('temp/logs')
    ->file(Logger::d('Y-m-d') . '.txt')
    ->from('Имя сайта <127.0.0.1>')
    ->subject('Логирование')
    ->mail('test@localhost');

$logger->send('Текст сообщения');



/* ========================================================================= */



/*
|------------------------------------------------------------------------------
| Документация ко всем методам
|------------------------------------------------------------------------------
|
| При создании объекта передается абсолютный путь к директории внутри которой
| происходит вся работа. Этот путь считается корнем приложения. Посредством
| методов логгера - устанавливаются различные настройки. Каждый (кроме
| статического метода Logger::d()) метод возвращает ссылку на объект, что
| позволяет устанавливать параметры в цепочке.
|
| -----------------------------------------------------------------------------
| core()
| -----------------------------------------------------------------------------
| Позволяет изменить корень приложения (абсолютный путь) - строка.
|
| -----------------------------------------------------------------------------
| path()
| -----------------------------------------------------------------------------
| Путь к папке хранения логов (относительно корня приложения, установленного при
| создании объекта) - строка. По умолчанию установлен в корень приложения.
|
| -----------------------------------------------------------------------------
| file()
| -----------------------------------------------------------------------------
| Имя файла, в который будет писаться лог - строка. По умолчанию - пустая
| строка, в таком положении лог в файл не пишется.
|
| -----------------------------------------------------------------------------
| console()
| -----------------------------------------------------------------------------
| Принимает булев тип. По умолчанию - true. Включить/отключить вывод логов в
| консоль.
|
| -----------------------------------------------------------------------------
| before() и after()
| -----------------------------------------------------------------------------
| Позволяет задать строки, которыми обрамлено сообщение в начале и конце текста.
| Удобно, если необходимо разделять каждой сообщение, например переносами строк:
| \n, PHP_EOL и т.д. По умолчанию заданы разделители.
|
| -----------------------------------------------------------------------------
| mail()
| -----------------------------------------------------------------------------
| Строка, адрес электронной почты, если необходимо отправлять лог на почту. По
| умолчанию установлен в пустую строку, что отключает рассылку. При рассылки
| используется нативная PHP функция - mail().
|
| -----------------------------------------------------------------------------
| from() и subject()
| -----------------------------------------------------------------------------
| Строки. По умолчанию пустые. Позволяют установить адрес отправителя и тему
| письма для метода mail().
|
| -----------------------------------------------------------------------------
| date()
| -----------------------------------------------------------------------------
| Строка, которая преобразовывается стандартным PHP методом DateTime::format().
| Позволяет установить необходимый формат отображения времени, когда был сделан
| лог. По умолчанию задан, если нет необходимости - можно передать пустую
| строку.
|
| -----------------------------------------------------------------------------
| timezone()
| -----------------------------------------------------------------------------
| Принимает целое число (как отрицательное, так и положительное). Позволяет
| установить псевдо временную зону. Прибавляет указанное число к часам метода
| date(). По умолчанию - 0.
|
| -----------------------------------------------------------------------------
| delete()
| -----------------------------------------------------------------------------
| Задает необходимость удалять устаревшие файлы логов. Принимает 2 параметра:
| целое число, либо null - количество секунд прошедших с момента изменения
| файла, спустя которые файл считается устаревшим. Строка, либо null - файлы с
| каким расширением необходимо удалять.
|
| -----------------------------------------------------------------------------
| d()
| -----------------------------------------------------------------------------
| Создать шаблон для преобразования методом DateTime::format().
| Статический метод.
| Порой очень удобно создавать файлы и/или папки, имена которых содержали бы
| элементы даты. Например, для записи логов. Передавать уже готовое название не
| всегда практично, т.к. если скрипт работает продолжительное время и переходит
| из одних суток в другие, тогда название продолжает соответствовать дню
| предыдущему.
| Метод принимает шаблон результирующей строки, как и метод - DateTime::format()
| и возвращает этот же шаблон но обернутый в специальный внутриклассовый, его
| уже можно использовать давая названия папкам и файлам, т.к. обрабатываться
| методом DateTime::format() название будет непосредтвенно в момент создания,
| т.е. будет всегда актуальным.
|
| -----------------------------------------------------------------------------
| send()
| -----------------------------------------------------------------------------
| Метод отправки лога. Принимает строку.
|
*/
