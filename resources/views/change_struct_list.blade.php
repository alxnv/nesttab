<?php

/**
 * Редактирование структуры таблиц - общий список
 */

//echo '<pre>';
//var_dump($list);

global $yy, $db;

$arrt = $yy->settings2['table_types'];
$arr_ts = $yy->settings2['table_names_short'];

$s = '';
?>
@extends(config('nesttab.layout'))
@section('content')
<?php
echo '<h1 class="center">' . __('All upper level tables list') . '</h1>'; 

for ($i = 0; $i < count($list); $i++) {
    if ($s <> $list[$i]['table_type']) {
        $s = $list[$i]['table_type'];
        $k = array_search($s, $arr_ts);
        $s2 = ($k === false ? '----' : $arrt[$k]);
        echo '<br /><h2>' . \yy::qs($s2). '</h2>';
    }
    
    echo '<a href="' . $yy->baseurl . 'nesttab/struct-change-table/edit/' .
            $list[$i]['id'] . '/">' .
            \yy::qs(trim($list[$i]['descr']) == '' ? '------' : $list[$i]['descr']) . '</a><br />';
}
?>
@endsection
