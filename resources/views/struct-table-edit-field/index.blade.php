<?php
use Illuminate\Support\Facades\Session;

global $yy, $db;
?>
@extends($yy->settings['layout'])
@section('content')

<?php
/*$s = Session::get('trw', '8888');
Session::put('trw', '777');
//Session::save();
dump($s);
*/
//echo '<h1 class="center">' . __('Table') . ' - ' . mb_strtolower(__('Add field')) . '</h1>';
echo '<h1 class="center">' . __('Table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        __('physical name') . ': ' . \yy::qs($tbl['name']) . ')  - ' . mb_strtolower(__('Add field')) , '<br /><br />';
?>
<form method="get" class="choose_field_form" action="<?=$yy->baseurl?>nesttab/struct-table-edit-field/step2/<?=$table_id?>">
@csrf
    <p class="center">
    <?=__('Select field type')?>:<br /><br />
    <select name="field_type_id" class="choose_field_type" size="17">
<?php
foreach ($yy->settings2['col_categories'] as $key=>$value) {
    echo '<optgroup label="' . \yy::qs($value) . '">';
    if (isset($field_types[$key])) {
        foreach($field_types[$key] as $arr) {
            echo '<option value="' . $arr['id'] . '">' . \yy::qs($arr['descr']);
        }
    }
    echo '<optgroup>';
}
?>
    </select>
<br /><br />
<input type="submit" name="sb" value="  <?=__('Select')?>  " />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" name="sbnested" value="<?=__('Add nested table')?>" />
</p>
</form>
<script type="text/javascript">
    $(function () {
        $('.choose_field_type').dblclick(function () {
            //alert(4);
            $('.choose_field_form').first().submit();
        });
        $('.choose_field_form').submit(function (e) {
            let n = $('.choose_field_type').first().val();
            //debugger;
            return (n !== null);
        });

    });
    </script>
    @endsection