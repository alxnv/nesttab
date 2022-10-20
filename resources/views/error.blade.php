<?php
use Illuminate\Support\Facades\Session;

global $yy;

$lnk = yy::get_error_session();

$s = session($lnk);
$session_id = $_COOKIE["laravel_session"];
5/0;
dd(Session::all(), $session_id);
if (!isset($_SESSION[$lnk])) die('Required parameter has not been passed');
?>
@extends($yy->settings['layout'])
@section('content')
<?php
echo '<h2 class="center red">' . \yy::t('Error') . '</h2>';
echo '<p class="center red"><br /><br />' . nl2br(\yy::qs($_SESSION[$lnk])) . 
   '<br /><br /><span class="backlink"><a href="javascript:history.back(1)">Назад</a></span></p>';
?>
@endsection
