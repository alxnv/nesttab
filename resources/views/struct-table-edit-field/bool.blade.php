@extends(config('nesttab.layout'))
@section('content')
<?php
/**
 * редактирование структуры поля типа boolean
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 */
global $yy, $db;


echo '<h1 class="center">' . __('Edit table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        __('physical name') . ': ' . \yy::qs($tbl['name']) .')<br /><br />';

if(!isset($r['id'])) {
    echo '<p class="center">' . __('Add field') . ' ' . __('of type') . ' "' .
            \yy::qs($fld['descr']) . '"</p>';
} else {
    echo '<p class="center">' . __('Edit field') . ' ' . __('of type') . ' "' .
            \yy::qs($fld['descr']) . '"</p>';
};
echo '<br />';


if (isset($r['is_error'])) {
    $lnk_err = \yy::get_error_edit_session();
    //$lnk_data = \yy::get_edit_session();
    echo '<br /><p align="left" class="red">' . nl2br(\yy::qs($_SESSION[$lnk_err])) . '</p><br />';
    //\app\core\Helper::assignData($r, $_SESSION[$lnk_data]); // читаем сохраненные данные формы
}

echo '<form method="post" action="' . $yy->baseurl . 'struct-table-edit-field/save/t/' . $tbl_id .
        '/prev/"><p align="left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@include('nesttab::all-fields.all')
<?php

echo __('Default value') . ': <input id="default" type="checkbox"'
        . ' name="default" ' .(isset($r['default']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="default">' .__('Checked') .'</label>';
echo '</p>';


/*
echo '<hr /><p align="left">';
$controller->render_partial(['r' => $r], 'additional', 'all-fields');
echo '</p>';
*/
?>
<br />
<p align="left">
<input type="submit" value="<?=__('Save')?>" />
</p>
<?php
echo '</form>';
?>
@endsection