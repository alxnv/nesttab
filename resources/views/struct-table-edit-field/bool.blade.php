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

$e = new \Alxnv\Nesttab\Models\ErrorModel();
if (isset($r['is_error'])) {
    $lnk_err = \yy::getErrorEditSession();
    $e->err = session($lnk_err);
    //$lnk_data = \yy::getEditSession();
    echo $e->getErr('');
    //echo '<br /><p align="left" class="red">' . nl2br(\yy::qs(session($lnk_err))) . '</p><br />';
    //\app\core\Helper::assignData($r, $_SESSION[$lnk_data]); // читаем сохраненные данные формы
}

if (isset($r['opt_fields'])) {
    $optOpened = true;
} else {
    $optOpened = false;
    if ($e->hasOneOf(['name'])) $optOpened = true; // если есть ошибки, относящиеся к
       // имени поля, то открываем div с именем поля
}

echo '<form method="post" action="' . $yy->baseurl . 'nesttab/struct-table-edit-field/save/' . $tbl_id .
        '"><div class="align-left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@csrf
@include('nesttab::all-fields.all')
<?php

echo $e->getErr('default');
echo __('Default value') . ': <input id="default" type="checkbox"'
        . ' name="default" ' .(isset($r['default']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="default">' .__('Checked') .'</label><br />';
//echo '</p>';


/*
echo '<hr /><p align="left">';
$controller->render_partial(['r' => $r], 'additional', 'all-fields');
echo '</p>';
*/
?>
<div id="app">
<input type="checkbox" name="opt_fields" id="opt_fields" v-model="checked" /> <label for="opt_fields"><?=__('Optional fields')?></label><br />
<div v-show="checked">
<?=$e->getErr('name')?>
<?=__('Physical name of the field')?> : <input type="text" name="name" size="25" value="<?=\yy::qs($r['name'])?>" /><br/>
</div>
</div>
<br />
<p align="left">
<input type="submit" value="<?=__('Save')?>" />
</p>
<?php
echo '</div>';
echo '</form>';
?>
<script>
const app = Vue.createApp({
  data() {
    return {
      checked: <?=($optOpened ? 'true' : 'false')?>
    }
  }
});
app.mount('#app');
</script>

@endsection