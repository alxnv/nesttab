@extends(config('nesttab.layout'))
@section('content')
<script>
    var fld_list = {}; // новая запись для параметров
     // трансформации (добавляется по нажатию кнопки "+"
    var _this;
    var baseUrl = '<?=asset('/' . config('nesttab.nurl'))?>';
</script>
<?php
/**
 * редактирование структуры поля типа select - поле выбора записи из другой таблицы
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 */
global $yy, $db;

$is_new_rec = !isset($r['id']);

if ($is_new_rec) {
    // новая запись
    $tm = new \Alxnv\Nesttab\Models\TablesModel();
    $arr1 = $tm->getAllForSelect();
    $arTables = $tm->getAllTablesSelectData($arr1);
    $curTable = 0;
    $visibleTable = 0; // таблица невидима
    $flds = []; // fields loaded
} else {
    // редактируем существующую запись
}


$requires['need_confirm'] = 1;
echo '<div id="main_contents">';

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

?>
<?php
if (isset($r['opt_fields'])) {
    $optOpened = true;
} else {
    $optOpened = false;
    if ($e->hasOneOf(['name', 'required', 'iprm', 'iprm0', 'iprm1', 'iprm2', 'iprm3', 'iprm4'])) $optOpened = true; // если есть ошибки, относящиеся к
       // имени поля, то открываем div с именем поля
}

echo '<form method="post" action="' . $yy->baseurl . config('nesttab.nurl') . '/struct-table-edit-field/save/' . $tbl_id .
        '"><div class="align-left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@csrf
@include('nesttab::all-fields.all')
<div id="app">
<?php

/*echo $e->getErr('required');
echo  '<input id="required" type="checkbox"'
        . ' name="req" ' .(isset($r['req']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="required">' . __ ('Is required') .'</label><br />';*/
echo $e->getErr('');
if ($is_new_rec) {
    echo '<br />' . __('Choose a table to link to');
?> : <br />
<select name="table_id"
        @change="select_item()" v-model="tables_index"><?= \Alxnv\Nesttab\core\HtmlHelper::makeselect($arTables, -1)?></select>
<br />
<br />
<input type="hidden" id="hidden" name="table_id" v-model="tables_index" />
<?php
}
?><div v-show="visible">
    <table class="table">
        <tr><th><?=__('Fields to show')?></th></tr>
        <tr v-for="(field, index) in recs">
                <td>
                    <div v-html="field.err"></div>
                    <div v-if="index > 0" class="minus_button_small"><input type="button" value="-" @click="del(index)" /></div>
                    <select v-model="field.vl" name="flds[]">
                        <option v-for="fld in field.flds" :value="fld.id">@{{ fld.name }}
                    </select>
                    <div v-if="(index < 4)" class="plus_button_small"><input type="button" value="+" @click="add(index)" /></div>
                </td>
            </tr>
    </table>    
<br />
</div>
    
<input type="checkbox" name="opt_fields" id="opt_fields" v-model="checked" /> <label for="opt_fields"><?=__('Additional fields')?></label><br />
<div v-show="checked" class="opt_fields">
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
function confirm_del_param(index) {
    $.confirm({
        useBootstrap: false,
        content: __lang('Do you really want to delete this element?'),
        title: '',
        backgroundDismiss: true,
        index: index,
     buttons: {
            yes: {
                text: __lang('Yes'),
                action: function(){
                    _this.delete2(this.index);
                    return true;
                    }               
            },
            no: {
                text: __lang('No'),
                action: function(){
                    return true;
                }
            }
        }
    });
}
    
const app = Vue.createApp({
  data() {
    return {
      visible: <?=$visibleTable?>,  
      tables_index: <?=$curTable?>,  
      checked: <?=($optOpened ? 'true' : 'false')?>,
      recs: [
<?php
for ($i = 0; $i < count($flds); $i++) {
    echo "{
        flds: fld_list,
        vl: 0,
        err: " . '"' . \yy::jsmstr($e->getErr('flds' . $i)) . '"' .  "   },
            "; 
}
?>
    ],
    }
  },
  methods: {
    select_item() {
      // alert(document.getElementById("hidden").value); - нормально отображается
      if (this.tables_index == 0) {
      } else {
        _this = this;  
        exec_ajax_json(baseUrl +'/ajax_flds_for_select/' + this.tables_index, {},
          function (data) {
              //alert(data[0].arr[0].name);
              let v = (data[0].arr.length == 0 ? 0 : data[0].arr[0].id); 
              fld_list = data[0].arr;
              _this.recs.splice(0, 1000000, {vl:v, flds: data[0].arr, err:''});
              _this.visible = 1;
          });
      }

    },
    add(index) {
      if (this.recs.length < 5) {
        let v = (fld_list.length == 0 ? 0 : fld_list[0].id);
        this.recs.splice(index + 1, 0, {vl:v, flds: fld_list, err:''});
      }
    },  
    del(index) {
        _this = this;
        confirm_del_param(index);
    },
    delete2(index) {
        // called from del if confirmed
        this.recs.splice(index, 1);
    },
  }
});
app.mount('#app');

</script>
<?php
echo '</div>';
?>
<div id="error_div"></div>

@endsection