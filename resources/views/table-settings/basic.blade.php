<?php
global $yy;
?>
@extends(config('nesttab.layout'))
@section('content')
<script type="text/javascript">
    var baseUrl = '<?=asset('/' . config('nesttab.nurl'))?>';
</script>
<?php

echo '<div id="main_contents">'; // div с основным содержимым страницы
echo  '<p><a href="javascript:history.back(1)">Назад</a><br /><br /></p>';
$e = new \Alxnv\Nesttab\Models\ErrorModel();
$lnk_err = \yy::getErrorEditSession();
if (Session::has($lnk_err)) {
    $e->err = session($lnk_err);
    //if (count($e->err) > 0 ) dd($e);
}
echo \yy::getSuccessOrErrorMessage([], $e);

echo '<h1 class="center">' . __('Settings of the table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        __('physical name') . ': ' . \yy::qs($tbl['name']) .')<br /><br />';
$tt = $tbl['table_type'];
$s = \Alxnv\Nesttab\core\Helper::table_types($tt);

echo __('Table type') . ': ' . \yy::qs($s) . '</h1>';

$e = new \Alxnv\Nesttab\Models\ErrorModel();
if (isset($r['is_error'])) {
    $lnk_err = \yy::getErrorEditSession();
    $e->err = session($lnk_err);
}

echo '<br />' . __("Table ID") . ': ' . $tbl['id'];
echo '<br />' . __("Size in bytes of 'id' field") . ': ' . $tbl['id_bytes'];

if (is_null($recCnt)) {
    echo '<div class="error">' . __('Error accessing table') . '</div>';
} else {
    echo '<br />' . __('Number of records in the table') . ': ' . $recCnt;
}
echo '</div>'; // main_contents
?>
<div id="error_div"></div>
<?php
echo '<form method="post" action="' . $yy->baseurl . config('nesttab.nurl') . '/struct-table-settings/save/' . $tbl['id'] .
        '">';

?>
@csrf
<br />
<?=__('Table name') . ":" ?> <input type="text" size="60" name="descr" value="<?=\yy::qs($r['descr'])?>" /><br />
<p align="left">
<input type="submit" value="<?=__('Save')?>" />
</p>
<?php
echo '</div>';
echo '</form>';
?>

@endsection
