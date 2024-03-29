<?php
use Illuminate\Support\Facades\Session;

global $yy;

$lnk = yy::getErrorSession();

$s = session($lnk, '');
//$session_id = $_COOKIE["laravel_session"];
//dump(Session::all());
if (!Session::has($lnk)) die('Required parameter has not been passed');
?>
@extends($layout)
@section('content')
<?php
echo '<h2 class="center red">' . __('Error') . '</h2>';
echo '<p class="center red"><br /><br />' . nl2br(\yy::qs(session($lnk))) . 
   '<br /><br />';
if (!$wl) {
    echo  '<span class="backlink"><a href="javascript:history.back(1)">Назад</a></span></p>';
}
?>
@endsection
