<?php
global $yy;
$pppp = 1;
?>
@extends(config('nesttab.layout'))
@section('content')
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/ajax_infinite')?>">Ajax до бесконечности</a><br />
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/ajax_infinite_sql')?>">Ajax до бесконечности Sql</a><br />
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/input-null-test')?>">Test how laravel handle empty values in post</a><br />
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/show-db-select-time')?>">Db benchmark</a><br />
<br />
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/double-click-test')?>">Test many clicks at once (Для обработчика удаления записей в физической БД (по ajax))</a><br />
<br />
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/locktables-test')?>">Test lock tables for InnoDB with myIsam</a><br />
@endsection
