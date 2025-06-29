@extends(config('nesttab.layout'))
@section('content')
<?php
global $yy;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

$page = (Request::has('page') ? Request::input('page') : 1); // current page for current list
//$with_html_editor = 1;


//dd($recs);
$yy->loadPhpScript(app_path() . '/Models/nesttab/tables/' 
        . ucfirst($tbl['name']) . '.php');



$e = new \Alxnv\Nesttab\Models\ErrorModel();
$lnk_err = \yy::getErrorEditSession();
if (Session::has($lnk_err)) {
    $e->err = session($lnk_err);
    //if (count($e->err) > 0 ) dd($e);
}
echo \yy::getSuccessOrErrorMessage($r, $e);
if ($errorMsg <>'') $e->setErr('', $errorMsg);
$err3 = $e->getErr('');
echo $err3;

$s = \Alxnv\Nesttab\core\FormatHelper::breadcrumbsEdit($id2, $id);
echo $s;
//if ($parent_id <> 0) echo '<p><a href="#" onClick="history.back()">' . __('Back') . '</a></p>';
$title = '' . \yy::qs($tbl['descr']) . '<br />';
echo $title;


//dd($recs);
if ($errorMsg == '') echo '<br /><p class="center"><a class="addfield" href="' . $yy->nurl . 'editrec/' .
        $parent_id . '/' . $tbl['id'] . '/0">' . __('Add record') . '</a>'
        . '</p>';
echo '<br /><div id="idt" class="table center2 div-table">';
echo '<div class="div-th"><span>№</span><span>' . __('Name') . '</span>'
        .  '</div>';

?>
@csrf
<?php
//(new \Alxnv\Nesttab\Models\UploadModel())->moveFileToUpload(public_path() . '/file2.bin');
//var_dump(\Alxnv\Nesttab\core\FileHelper::writeToFile(public_path() . '/file1.bin', 
//        public_path() . '/file2.bin'));
//dd($recs);
$i = 0;
foreach ($recs as $rec) {
    echo '<div><span><a class="addfield" href="' . $yy->nurl . 'editrec/' 
        . $tbl['id'] . '/' . $rec->id . '?page=' . $page . '">' . $rec->ordr;
    echo '</a></span><span><a class="addfield" href="' . $yy->nurl . 'editrec/' 
        . $parent_id. '/' . $tbl['id'] . '/' . $rec->id . '?page=' . $page . '">' . \yy::qs($rec->name);
    echo '</a></span></div>';
    $i++;
}
echo '</div>';
?>
<br />
<?php
if (!is_array($recs)) echo $recs->links();
?>
@endsection
