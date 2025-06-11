@extends(config('nesttab.layout'))
@section('content')
<?php
global $yy;
use Illuminate\Support\Facades\Session;

//$with_html_editor = 1;


//dd($recs);
$yy->loadPhpScript(app_path() . '/Models/nesttab/tables/' 
        . ucfirst($tbl['name']) . '.php');



$e = new \Alxnv\Nesttab\Models\ErrorModel();
$lnk_err = \yy::getErrorEditSession();
if (Session::has($lnk_err)) {
    $e->err = session($lnk_err);
    //if (count($e->err) > 0 ) dd($e);
    Session::forget($lnk_err);
}
if ($errorMsg <> '') {
    $e->setErr('', $errorMsg);
}

echo \yy::getSuccessOrErrorMessage($r, $e);
$title = '<h1>' . \yy::qs($tbl['descr']) . '</h1><br />';
if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onShow'))) $s77($recs, -2, '', true, $title);
echo $title;

$err3 = $e->getErr('');
if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onShow'))) $s77($recs, -2, '', false, $err3);
echo $err3;

//dd($recs);
// вывести список всех таблиц следующего уровня, вложенных в данную
echo \Alxnv\Nesttab\core\TableHelper::childTables($tbl['id'], ' class="center"', 
        function ($ind, $ap) {
            global $td, $yy;
            if (isset($td['ind'][$ind]) && isset($td['dat'][$td['ind'][$ind]])) {
                $row = $td['dat'][$td['ind'][$ind]];
                return '<a href="' . $yy->nurl .  'struct-change-table/edit/' . $row[0] . '">' . \yy::qs($row[3]) . '</a><br />';
            } else {
                return '';
            }
        },
        ['parent_id' => 0]);

echo '<form enctype="multipart/form-data" method="post" action="' . $yy->baseurl . config('nesttab.nurl') . '/edit/save_one/' . $tbl['id'] . '" >';
?>
@csrf
<?php
//(new \Alxnv\Nesttab\Models\UploadModel())->moveFileToUpload(public_path() . '/file2.bin');
//var_dump(\Alxnv\Nesttab\core\FileHelper::writeToFile(public_path() . '/file1.bin', 
//        public_path() . '/file2.bin'));
//dd($recs);
$i = 0;
foreach ($recs as $rec) {
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
echo $footer;

echo '</form>';
?>
@endsection
