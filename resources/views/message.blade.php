<?php
global $yy;

$lnk = yy::get_message_session();

if (!isset($_SESSION[$lnk])) die('Required parameter has not been passed');
?>
@extends($yy->settings['layout'])
@section('content')
<?php

echo '<p class="center green"><br /><br />' . nl2br(\yy::qs($_SESSION[$lnk])) . '<br /><br />'
        . '<span class="backlink"><a href="javascript:history.back(1)">Назад</a></span></p>';
?>
@endsection
