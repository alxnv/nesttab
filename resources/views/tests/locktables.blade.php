<?php
global $yy;
?>
@extends(config('nesttab.layout'))
@section('content')
Test lock tables for InnoDB with myIsam<br /><br />
<a href="<?=$yy->nurl?>tests/locktables-start-test">Start</a><br /><br />
<a href="<?=$yy->nurl?>tests/locktables-test-test">Test</a><br /><br />

Использование: открыть проект в двух закладках (окнах), в одном запустить Start, затем 
в другом запустить Test. <br />
Должна быть пауза в 10 секунд в окне Test, а затем сообщение об удачном завершении
<br /><br />
Проверяется последовательность команд БД:<br />
set autocommit=0<br />
lock tables temp_s5 write<br />
commit<br />
unlock tables<br /><br />

(Так нужно блокировать таблицы innoDb)<br />
Оказалось что для MyIsam тоже это работает
@endsection
