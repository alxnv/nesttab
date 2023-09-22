@extends(config('nesttab.layout'))
@section('content')
<?php
global $yy, $db;

$arr20 = $yy->settings2['table_types'];
$arr2 = [];
foreach ($arr20 as $value) {
    $arr2[] = __($value);
}
//sort($arr2);
$arr_table_names_short = $yy->settings2['table_names_short'];

//if (!isset($r['tbl_type']))  die('Required parameter is not passed');

$tbl_idx = 0; //intval($r['tbl_type']);
//if ($tbl_idx < 0 || $tbl_idx >= count($arr2)) die('Wrong index of table');

$arr7 = $db->q("select * from yy_settings where id=1");
$arr8 = [];
$tblname = null;
foreach ($yy->settings2['table_names'] as $s2) {
    $s3 = $s2 . '_counter';
    $s5 = config('nesttab.db_prefix') . $s2 . \yy::num1($arr7[$s3] + 1);
    if (!isset($tblname)) $tblname = $s5;
    $arr8[] = "'" . $s5 . "'";
}
//$tblname = 
$s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\TableHelper';
$th = new $s();
// получаем возможное количество байтов в поле типа int (tinyint и т.д.)
$arFieldSizes = \Alxnv\Nesttab\core\ArrayHelper::keyLikeValue($th->arrayOfIntFieldSizes()); 
$int4Bytes = 4;
$s = \Alxnv\Nesttab\core\HtmlHelper::makeselect($arFieldSizes, $int4Bytes);
?>
<script type="text/javascript">
    var table_names =[<?=join(', ', $arr8)?>];
</script>
<form method="get" action="<?=$yy->nurl?>struct-add-table/step22">
<h2 class="center"><?=__('Add table')?></h2>
<br />
<p>
<?=__('Table type')?>: <select name="tbl_type" class="tbl_choose"><?=\Alxnv\Nesttab\core\HtmlHelper::makeselsimp($arr2)?></select><br />
<br />
<?=__('Table name')?>: <input type="text" name="tbl_name" class="tbl_name" value="<?=$tblname?>" size="30" /><br />
<br />
<?=__('Table description (name)')?>: <input type="text" name="tbl_descr" size="50" /><br />
<br />
<?=__('Size in bytes of field')?> id: <select name="int_bytes"><?=$s?></select>
<br />
<br />
<input type="submit" value="<?=__('Add')?>" />
</p>

<script type="text/javascript">
$(function() { // document.ready
    $('.tbl_choose').change( function(e) { 
        let id = $('.tbl_choose').val();
        //alert(table_names[id]);
        $('.tbl_name').val(table_names[id]);
        return true;
    } );
});
</script>
@endsection
