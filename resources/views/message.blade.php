<?php
global $yy;

$lnk = yy::getMessageSession();
//dump(Session::all());

if (!Session::has($lnk)) die('Required parameter has not been passed');
?>
@extends(config('nesttab.layout'))
@section('content')
<?php

echo '<p class="center green"><br /><br />' . nl2br(\yy::qs(session($lnk))) . '<br /><br />'
        . '<span class="backlink"><a href="javascript:history.back(1)">Назад</a></span></p>';
?>
@endsection
