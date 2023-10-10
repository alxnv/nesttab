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


if (is_null($recCnt)) {
    echo '<div class="error">' . __('Error accessing table') . '</div>';
} else {
    echo '<br />' . __('Number of records in the table') . ': ' . $recCnt;
}
echo '</div>'; // main_contents
?>
<div id="error_div"></div>

@endsection
