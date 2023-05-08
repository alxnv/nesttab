@extends(config('nesttab.layout'))
@section('content')
<?php
global $yy;

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



echo '<form method="post" action="' . $yy->baseurl . 'nesttab/edit/save_one/' . $tbl['id'] . '" >';
?>
@csrf
<?php
foreach ($recs as $rec) {
    $rec['obj']->editField($rec, []);
}
echo '<input type="submit" value="' . __('Save') . '" />';
echo '</form>';
?>
@endsection
