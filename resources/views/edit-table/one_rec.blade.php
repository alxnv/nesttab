@extends(config('nesttab.layout'))
@section('content')
<?php
global $yy;
use Illuminate\Support\Facades\Session;

//$with_html_editor = 1;

echo '<h1>"' . \yy::qs($tbl['descr']) . '"</h1><br />'; 
//dd($recs);

/*
@unlink(public_path() . '/upload/2.png');
(new \Alxnv\Nesttab\Models\ImageResizeModel())->
        resizeImage(public_path() . '/upload/1.gif',
                public_path() . '/upload/2.gif', 300, 400, 'cover');
 * 
 */
//$b = \Alxnv\Nesttab\core\FileHelper::deleteDir(public_path() . '/upload/dir1');
//dd($b);
//$obj = new \Alxnv\Nesttab\Models\TokenUploadModel();
//for ($i=0; $i < 10; $i++) $obj->createTokenDir();
//echo url('njkjk/yuiyui');

$e = new \Alxnv\Nesttab\Models\ErrorModel();
$lnk_err = \yy::getErrorEditSession();
if (Session::has($lnk_err)) {
    $e->err = session($lnk_err);
    //if (count($e->err) > 0 ) dd($e);
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
    $rec['obj']->editField($rec, [], $table_id, $rec_id, $r);
}
if (count($recs) > 0) {
    echo '<input type="submit" value="' . __('Save') . '" />';
}
echo '</form>';
?>
@endsection
