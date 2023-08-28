@extends(config('nesttab.layout'))
@section('content')
<?php
/**
 * редактирование структуры поля типа num (integer 4 byte)
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 */
use Carbon\Carbon;
global $yy, $db;

$requires = ['need_datetimepicker' => 1];
if (!isset($r['default'])) {
    $r['default'] = ''; //Carbon::now()->format($yy->format); // current datetime
} else {
    $r['default'] = (new Carbon($r['default']))->format($yy->format);
}

/*echo $r['default'];
$b = $yy->localeObj->isValidValue($r['default']);
echo ' ' . $b;

$s = '2003-12-5 20:0:30';
echo $s;
$b = $yy->localeObj->isValidValue($s);
echo ' ' . ($b ? 1 : 0);*/
//echo $yy->format;
//$date = Carbon::now();
//setlocale(LC_TIME, 'ru_RU.UTF-8');
//Carbon::setLocale('ru_RU.UTF-8');
/*echo app()->getLocale() . '<br />';
echo $r['default']->locale() . '<br />';
echo $r['default']->toDateTimeString() . '<br />';
setlocale(LC_TIME, "ru_RU.UTF-8");
echo $r['default']->formatLocalized('%A %d %B %Y') . '<br />';*/
echo '<a href="' . $yy->nurl . 'struct-change-table/edit/' . $tbl['id'] . '/0">'
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
    if ($e->hasOneOf(['name', 'default'])) $optOpened = true; // если есть ошибки, относящиеся к
       // имени поля, то открываем div с именем поля
}

echo '<form method="post" action="' . $yy->nurl . 'struct-table-edit-field/save/' . $tbl_id .
        '"><div class="align-left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@csrf
@include('nesttab::all-fields.all')

<input type="checkbox" name="opt_fields" id="opt_fields" /> <label for="opt_fields"><?=__('Additional fields')?></label><br />
<div id="opt_div" class="opt_fields" <?=($optOpened ? '' : ' style="display:none" ')?>>
<?php

echo $e->getErr('default');
echo __('Default value') . ': <input type="text" size="20" id="default"'
        . '  data-role="datebox" data-options=' . "'" . '{"mode":"datetimebox"}' . "'"
        . '" name="default" value="' . (isset($r['default']) ? \yy::qs($r['default']) : '') . '" />'
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
        . ' <label for="required">' . __('Is required') .'</label><br />';*/
?>
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
$( document ).ready(function () {
    $('#opt_fields').click(function() {
        $('#opt_div').toggle();
    });
});
</script>

@endsection