<?php
global $yy;
$pppp = 1;
?>
@extends(config('nesttab.layout'))
@section('content')
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/ajax_infinite')?>">Ajax до бесконечности</a><br />
<a href="<?=asset('/' . config('nesttab.nurl') . '/tests/ajax_infinite_sql')?>">Ajax до бесконечности Sql</a><br />
@endsection