<?php

/**
 * Редактирование структуры таблиц - общий список
 */

//echo '<pre>';
//var_dump($list);

global $yy, $db, $td;

$arrt = $yy->settings2['table_types'];
$arr_ts = $yy->settings2['table_names_short'];

$s = '';
?>
@extends(config('nesttab.layout'))
@section('content')
<?php
echo '<h1 class="center">' . __('All tables list') . '</h1><br />'; 

$s = \Alxnv\Nesttab\core\FormatHelper::getTree($td['cat'], 0,
        // функция возвращает гиперссылку с данными таблицы по элементу массива $td['cat']
        function ($id) {
            global $td, $yy;
            if (isset($td['ind'][$id]) && isset($td['dat'][$td['ind'][$id]])) {
                $row = $td['dat'][$td['ind'][$id]];
                return '<a href="' . $yy->nurl . 'struct-change-table/edit/' . $row[0] . '">' .
                        \yy::qs($row[3] . ' (' . $row[2] . ') (' . $row[4] . ')') . '</a>';
            } else {
                return '';
            }
        },
        // функция возвращает айди элемента по элементу массива $td['cat'], или -1, если элемент не найден        
        function ($id) {
            global $td;
            if (isset($td['ind'][$id]) && isset($td['dat'][$td['ind'][$id]])) {
                $row = $td['dat'][$td['ind'][$id]];
                return $row[0];
            } else {
                return -1;
            }
            
        });

echo $s;
echo '<br /><p><span class="red">*</span> O - таблица с одной записью, L - список, C - каталог, D - таблица общего вида</p>'
/*for ($i = 0; $i < count($list); $i++) {
    if ($s <> $list[$i]['table_type']) {
        $s = $list[$i]['table_type'];
        $k = array_search($s, $arr_ts);
        $s2 = ($k === false ? '----' : __($arrt[$k]));
        echo '<br /><h2>' . \yy::qs($s2). '</h2>';
    }
    
    echo '<a href="' . $yy->baseurl . config('nesttab.nurl') . '/struct-change-table/edit/' .
            $list[$i]['id'] . '/">' .
            \yy::qs(trim($list[$i]['descr']) == '' ? '------' : $list[$i]['descr']) .
            ' (' . \yy::qs($list[$i]['name']) . ')</a><br />';
}*/
?>
@endsection
