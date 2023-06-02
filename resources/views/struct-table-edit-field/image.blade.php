@extends(config('nesttab.layout'))
@section('content')
<?php
/**
 * редактирование структуры поля типа image
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 */
global $yy, $db;

if (isset($r['allowed'])) {
    $r['allowed'] = join(', ', $r['allowed']);
} else {
    $r['allowed'] = '';
}

$image_params = [[0, 0, 'contain'], [200, 150, 'contain']];

echo '<a href="' . $yy->baseurl . 'nesttab/struct-change-table/edit/' . $tbl['id'] . '/0">'
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

?>
<?php
if (isset($r['opt_fields'])) {
    $optOpened = true;
} else {
    $optOpened = false;
    if ($e->hasOneOf(['name', 'required'])) $optOpened = true; // если есть ошибки, относящиеся к
       // имени поля, то открываем div с именем поля
}

echo '<form method="post" action="' . $yy->baseurl . 'nesttab/struct-table-edit-field/save/' . $tbl_id .
        '"><div class="align-left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@csrf
@include('nesttab::all-fields.all')
<?php

//echo '</p>';


/*
echo '<hr /><p align="left">';
$controller->render_partial(['r' => $r], 'additional', 'all-fields');
echo '</p>';
*/
echo $e->getErr('allowed');
echo __('Allowed extensions') . ': <input type="text" size="50" id="allowed"'
        . ' name="allowed" value="' . (isset($r['allowed']) ? \yy::qs($r['allowed']) : '') . '" />'
        . '<br />';
echo '<div class="comment"><span class="red">*</span> ' . __("Enter file extensions delimeted by ','") . 
        ', ' . __("or empty string, if any extension is allowed") . '.<br />' . 
        __("Possible extensions") . ': gif, jpeg, jpg, png' .
         '.<br /></div>';
?>
<div id="app">
<input type="checkbox" name="opt_fields" id="opt_fields" v-model="checked" /> <label for="opt_fields"><?=__('Additional fields')?></label><br />
<div v-show="checked" class="opt_fields">
<?=$e->getErr('name')?>
<?=__('Physical name of the field')?> : <input type="text" name="name" size="25" value="<?=\yy::qs($r['name'])?>" /><br/>
<?php

echo $e->getErr('required');
echo  '<input id="required" type="checkbox"'
        . ' name="req" ' .(isset($r['req']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="required">' . __ ('Is required') .'</label><br />';
?>
<hr />
<p class="center"><?=__("Image transformations")?></p>
<br />
    <template v-for="(field, index) in fields" />
    <div v-if="index > 1"><button>-</button></div>
    <table class="table">
        <th colspan="2">
            @{{captions[index]}}
        </th>
        <tr>
            <td><?=__('Width, pixels')?>:</td>
            <td><input type="number" name="i_width[]" class="img_width" v-model="field.width" /></td>
        </tr>
        <tr>
            <td><?=__('Height, pixels')?>:</td>
            <td><input type="number" name="i_height[]" class="img_height" v-model="field.height" /></td>
        </tr>
        <tr>
            <td><?=__("Transformation type")?>:</td>
            <td><select v-model="field.type" name="i_type[]">
                    <option value="cover">Cover</option>
                    <option value="contain">Contain</option>
            </select>
            </td>
        </tr>
    </table>
    <div v-if="index == 0" class="comment"><span class="red">*</span> <?=__("If width = 0 and height = 0 for the main image, the image is loaded as is, without size transformation")?></div>
    <div v-if="index > 0"><input type="button" value="+" @click="add(index)" /></div>
    <br />    
    </template>
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
      checked: <?=($optOpened ? 'true' : 'false')?>,
    fields: [
<?php
for ($i = 0; $i < count($image_params); $i++) {
    echo "{
        width: " . $image_params[$i][0] . ",
        height: " . $image_params[$i][1] . ",
        type: '" . $image_params[$i][2] . "'},
            "; 
}
?>
    ],
    captions: [
      "<?=__("Main image")?>",
      "Thumbnail",
      "<?=__("Image")?> 1",
      "<?=__("Image")?> 2",
      "<?=__("Image")?> 3",
    ],
    }
  },
  methods: {
    add(index) {
      alert(index);
  },  
  }
});
app.mount('#app');

</script>

@endsection