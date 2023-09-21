@extends(config('nesttab.layout'))
@section('content')
<?php
/**
 * редактирование структуры поля типа float
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 */
global $yy, $db;

$requires['need_confirm'] = 1;
echo '<div id="main_contents">';
if (!isset($r['default'])) {
    $r['default'] = '0.00';
}
if (!isset($r['m'])) {
    $r['m'] = '0';
}
if (!isset($r['d'])) {
    $r['d'] = '0';
}

echo '<a href="' . $yy->baseurl . config('nesttab.nurl') . '/struct-change-table/edit/' . $tbl['id'] . '/0">'
        .__('Back') . '</a><br /><br />';

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
?>
@include('nesttab::struct-table-edit-field.rec-inc')
<?php

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
    if ($e->hasOneOf(['name', 'default', 'required', 'm', 'd'])) $optOpened = true; // если есть ошибки, относящиеся к
       // имени поля, то открываем div с именем поля
}

echo '<form method="post" action="' . $yy->baseurl . config('nesttab.nurl') . '/struct-table-edit-field/save/' . $tbl_id .
        '"><div class="align-left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@csrf
@include('nesttab::all-fields.all')
<div id="app">
<input type="checkbox" name="opt_fields" id="opt_fields" v-model="checked" /> <label for="opt_fields"><?=__('Additional fields')?></label><br />
<div v-show="checked"  class="opt_fields">
<?php

echo $e->getErr('default');
echo __('Default value') . ': <input type="text" size="30" id="default"'
        . ' name="default" value="' . (isset($r['default']) ? \yy::qs($r['default']) : '') . '" />'
        . '<br />';
echo $e->getErr('m');
echo __('Digits overall') . ': <input type="text" size="10" id="m"'
        . ' name="m" value="' . (isset($r['m']) ? \yy::qs($r['m']) : '') . '" />'
        . '<br />';
echo $e->getErr('d');
echo __('Decimal places') . ': <input type="text" size="10" id="d"'
        . ' name="d" value="' . (isset($r['d']) ? \yy::qs($r['d']) : '') . '" />'
        . '<br />';
echo '<p class="comment">* ' . __("If both last numbers are zero, it can be any floating point number") .
        '</p>';
//echo '</p>';


/*
echo '<hr /><p align="left">';
$controller->render_partial(['r' => $r], 'additional', 'all-fields');
echo '</p>';
*/
?>
<?=$e->getErr('name')?>
<?=__('Physical name of the field')?> : <input type="text" name="name" size="25" value="<?=\yy::qs($r['name'])?>" /><br/>
<?php
echo $e->getErr('required');
echo '<input id="required" type="checkbox"'
        . ' name="req" ' .(isset($r['req']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="required">' . __('Is required') .'</label><br />';
?>
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
<?php
echo '</div>';
?>
<div id="error_div"></div>

@endsection