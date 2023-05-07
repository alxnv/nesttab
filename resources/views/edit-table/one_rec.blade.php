@extends(config('nesttab.layout'))
@section('content')
<?php
echo '<h1>' . __('Table') . ' "' . \yy::qs($tbl['descr']) . '"</h1><br />'; 
//dd($recs);
foreach ($recs as $rec) {
    $rec['obj']->editField($rec, []);
}
?>
@endsection
