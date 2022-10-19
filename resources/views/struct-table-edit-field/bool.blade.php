<?php
/**
 * редактирование структуры поля типа boolean
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 */
global $yy, $db;


echo '<h1 class="center">' . \yy::t('Edit table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        \yy::t('physical name') . ': ' . \yy::qs($tbl['name']) .')<br /><br />';

if(!isset($r['id'])) {
    echo '<p class="center">' . \yy::t('Add field') . ' ' . \yy::t('of type') . ' "' .
            \yy::qs($fld['descr']) . '"</p>';
} else {
    echo '<p class="center">' . \yy::t('Edit field') . ' ' . \yy::t('of type') . ' "' .
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
        '/prev/' . $controller->prev_link .'"><p align="left">';
$controller->render_partial(['r' => $r], 'all', 'all-fields');

echo \yy::t('Default value') . ': <input id="default" type="checkbox"'
        . ' name="default" ' .(isset($r['default']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="default">' .\yy::t('Checked') .'</label>';
echo '</p>';


/*
echo '<hr /><p align="left">';
$controller->render_partial(['r' => $r], 'additional', 'all-fields');
echo '</p>';
*/
?>
<br />
<p align="left">
<input type="submit" value="<?=\yy::t('Save')?>" />
</p>
<?php
echo '</form>';
?>