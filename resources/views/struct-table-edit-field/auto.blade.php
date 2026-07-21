@extends(config('nesttab.layout'))
@section('content')
<?php
/**
 * редактирование структуры поля типа int (integer 1,2,3,4,8 bytes)
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 */
global $yy, $db;

$requires['need_confirm'] = 1;
echo '<div id="main_contents">';
if (!isset($r['default'])) {
    $r['default'] = '0';
}

echo '<a href="' . $yy->nurl . 'struct-change-table/edit/' . $tbl['id'] . '/0">'
        .__('Back') . '</a><br /><br />';

echo '<h1 class="center">' . __('Edit table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        __('physical name') . ': ' . \yy::qs($tbl['name']) .')<br /><br />';

if(!isset($r['id'])) {
    echo '<p class="center">' . __('Add field') . ' ' . __('of type') . ' "' .
            \yy::qs($fld['descr']) . '" ('  . __('there may be maximum the one field of this type in the table') . ')</p>';
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
    if ($e->hasOneOf(['name', 'default', 'required'])) $optOpened = true; // если есть ошибки, относящиеся к
       // имени поля, то открываем div с именем поля
}

echo '<form method="post" action="' . $yy->nurl . 'struct-table-edit-field/save/' . $tbl_id .
        '"><div class="align-left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@csrf
<?php
/* 
 * Основные поля для отображения в режиме редактирования структуры полей
 */
if (!isset($r['name'])) $r['name'] = '';
if (!isset($r['descr'])) $r['descr'] = '';
if (!isset($r['step'])) {
    if (isset($params->step)) {
        $r['step'] = $params->step;
    } else {
        $r['step'] = 1000;
    }
}
$r['name'] = mb_substr($r['name'], 0, 200);
$r['descr'] = mb_substr($r['descr'], 0, 200);
if (trim($r['descr']) == '') {
    $r['descr'] = $fld['descr'];
}

if (isset($r['id'])) echo '<input type="hidden" name="id" value="' . intval($r['id']) . '" />';
?>
<input type="hidden" name="field_type_id" value="<?=intval($r['field_type_id'])?>" />
<?=$e->getErr('descr')?>
<?=__('Description')?> : <input type="text" name="descr" size="40" value="<?=\yy::qs($r['descr'])?>" /><br/>
<div id="app">
<input type="checkbox" name="opt_fields" id="opt_fields" v-model="checked" /> <label for="opt_fields"><?=__('Additional fields')?></label><br />
<div v-show="checked"  class="opt_fields">
<?php

echo $e->getErr('step');
echo __('Step') . ': <input type="text" size="30" id="step"'
        . ' name="step" value="' . (isset($r['step']) ? \yy::qs($r['step']) : '') . '" />'
        . '<br />';
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
/*echo $e->getErr('required');
echo '<input id="required" type="checkbox"'
        . ' name="req" ' .(isset($r['req']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="required">' . __('Is required') .'</label><br />';
*/?>
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