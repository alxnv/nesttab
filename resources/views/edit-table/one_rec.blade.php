@extends(config('nesttab.layout'))
@section('content')
<?php
global $yy;

//$with_html_editor = 1;

echo '<h1>' . __('Table') . ' "' . \yy::qs($tbl['descr']) . '"</h1><br />'; 
//dd($recs);


$e = new \Alxnv\Nesttab\Models\ErrorModel();
$lnk_err = \yy::getErrorEditSession();
if (Session::has($lnk_err)) {
    $e->err = session($lnk_err);
}
echo \yy::getSuccessOrErrorMessage($r, $e);
echo $e->getErr('');
//    echo '<br />';



echo '<form enctype="multipart/form-data" method="post" action="' . $yy->baseurl . 'nesttab/edit/save_one/' . $tbl['id'] . '" >';
?>
@csrf
<?php
//(new \Alxnv\Nesttab\Models\UploadModel())->moveFileToUpload(public_path() . '/file2.bin');
//var_dump(\Alxnv\Nesttab\core\FileHelper::writeToFile(public_path() . '/file1.bin', 
//        public_path() . '/file2.bin'));
//dd($recs);
foreach ($recs as $rec) {
    echo $e->getErr($rec['name']);
    $rec['obj']->editField($rec, []);
}
if (count($recs) > 0) {
    echo '<input type="submit" value="' . __('Save') . '" />';
}
echo '</form>';
?>
@endsection
