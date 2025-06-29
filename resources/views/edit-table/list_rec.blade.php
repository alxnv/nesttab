@extends(config('nesttab.layout'))
@section('content')
<?php
global $db, $yy;
use Illuminate\Support\Facades\Session;

//$with_html_editor = 1;

$requires['need_confirm'] = 1;

//dd($recs);
$yy->loadPhpScript(app_path() . '/Models/nesttab/tables/' 
        . ucfirst($tbl['name']) . '.php');

echo '<div id="main_contents">';
/*
$s8=$db->qdirect("insert into temp5 values(2,2,2)", [], $db::ERROR_MODE_RETURN_ERROR);
    var_dump($s8, $db->errorCode, $db->errorMessage);
*/

/*$contr = new \Alxnv\Nesttab\Http\Controllers\AjaxController();
var_dump($contr->getSelectListHtml(93, request()));
*/

//$columnsModel = new \Alxnv\Nesttab\Models\ColumnsModel();
//$table_name = '';
//var_dump($columnsModel->getSelectListHtml(93));
//$table_name = '';
//$arr = \Alxnv\Nesttab\Models\ColumnsModel::getOneSelectFldNames(93, 2, $table_name);
//dd($arr, $table_name);

if (count($rec) > 0) { // если не новая запись
    $moveTo = $rec['ordr']; // для edit и кнопки "Переместить"
}

$e = new \Alxnv\Nesttab\Models\ErrorModel();
$lnk_err = \yy::getErrorEditSession();
if (Session::has($lnk_err)) {
    $e->err = session($lnk_err);
    //if (count($e->err) > 0 ) dd($e);
    Session::forget($lnk_err);
}
echo \yy::getSuccessOrErrorMessage($r, $e);
$formNoSubmit = false; // запретить отправку формы
if ($errorMsg <> '') {
    $e->setErr('', $errorMsg);
    $formNoSubmit = true;
}
$err3 = $e->getErr('');
if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onShow'))) $s77($recs, -2, '', false, $err3);
echo $err3;

$s = \Alxnv\Nesttab\core\FormatHelper::breadcrumbsEdit($id2, $id);
echo $s;

/*echo '<p><a href="' . $yy->baseurl . config('nesttab.nurl') . '/edit/' .
        $parent_id . '/' . $table_id . '?page=' . ($rec_id == 0 ? 1 : $returnToPage) . '">' . __('Back') . '</a></p>';*/
$title = \yy::qs($tbl['descr']) . ' - ' 
        . ($rec_id == 0 ? __('add record') : __('edit record'));
if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onShow'))) $s77($recs, -2, '', true, $title);
echo $title;
echo '<br />';

if ($rec_id <> 0) { // если не новая запись
    // вывести список всех таблиц следующего уровня, вложенных в данную
    $s = \Alxnv\Nesttab\core\TableHelper::childTables($tbl['id'], '', 
            function ($ind, $ap) {
                global $td, $yy;
                if (isset($td['ind'][$ind]) && isset($td['dat'][$td['ind'][$ind]])) {
                    $row = $td['dat'][$td['ind'][$ind]];
                    return '<a href="' . $yy->nurl .  'edit/' . $ap['parent_id'] . '/' . $row[0] . '">' . \yy::qs($row[3]) . '</a><br />';
                } else {
                    return '';
                }
            },
            ['parent_id' => $id3]); // id текущей записи типа 'one'
    if ($s <> '') {
        echo '<br />' . $s;        
    }
}
echo '<br />';
//dd($recs);
?>
@include('nesttab::edit-table.rec-inc')
<?php

if (!$formNoSubmit) echo '<form enctype="multipart/form-data" method="post" action="' . $yy->baseurl . config('nesttab.nurl') . '/editrec/save/' . 
        $parent_id . '/' . $table_id. '/' . $rec_id . '" >';
?>
@csrf
<input type="hidden" name="return_to_page5871" value="<?=$returnToPage?>" />
<?php
//(new \Alxnv\Nesttab\Models\UploadModel())->moveFileToUpload(public_path() . '/file2.bin');
//var_dump(\Alxnv\Nesttab\core\FileHelper::writeToFile(public_path() . '/file1.bin', 
//        public_path() . '/file2.bin'));
//dd($recs);
$i = 0;
foreach ($recs as $rec) {
    //var_dump($rec);
    $err3 = $e->getErr($rec['name']);
    if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onShow'))) $s77($recs, $i, $rec['name'], false, $err3);
    echo $err3;
    
    ob_start();
    $rec['obj']->editField($rec, [], $table_id, $rec_id, $r, $extra);
    $out1 = ob_get_contents();
    ob_end_clean();
    if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onShow'))) $s77($recs, $i, $rec['name'], true, $out1);
    echo $out1;
    
    $i++;
}
$footer = '';
if (count($recs) > 0) {
    $footer = '<input type="submit" value="' . __('Save') . '" />';
}
if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onShow'))) $s77($recs, -1, '$', true, $footer);
if (!$formNoSubmit) {
    echo $footer;
    echo '</form>';
}
echo '</div>';
?>
<div id="error_div"></div>
@endsection
