@extends(config('nesttab.layout'))
@section('content')
<?php
$e = new \Alxnv\Nesttab\Models\ErrorModel();
if (isset($r['is_error'])) {
    $lnk_err = \yy::getErrorEditSession();
    $e->err = session($lnk_err);
    //$lnk_data = \yy::getEditSession();
    //echo '<br /><p align="left" class="red">' . nl2br(\yy::qs(session($lnk_err))) . '</p><br />';
    //\app\core\Helper::assignData($r, $_SESSION[$lnk_data]); // читаем сохраненные данные формы
}

//$arr76 = \Alxnv\Nesttab\Models\ColumnsModel::getSelectFldNames(5);
//dd($arr76);
//dd(session($lnk_err));
?>
<?php
/**
 * редактирование структуры поля типа select - поле выбора записи из другой таблицы
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 * $r['params'] отображаются в $r
 * $r['id'] - id поля
   вызывается return view('nesttab::struct-table-edit-field.' . $fld['name'], ['tbl' => $tbl, 'tblname' => $tbl['name'], 'tbl_id' => $table_id,
            'field_type_id' => intval($r['field_type_id']), 'fld' => $fld, 'r' => $r,
            'tableModel' => $tableModel, 'fieldModel' =>$fieldModel] 

 *  */
global $yy, $db;

$is_new_rec = !isset($r['id']);

function getSelCol($table_id) {
    $arr = \Alxnv\Nesttab\Models\ColumnsModel::getSelectColumns($table_id);
    $ar2 = [];
    foreach ($arr as $key => $value) {
        $ar2[] = "{'id' : $key, 'name' : " . '"' . \yy::jsmstr($value) . '"' . "}";
    }
    $s = join(', ', $ar2);
    return $s;
}

$sFldList = '';
if ($is_new_rec) {
    // новая запись
    $tm = new \Alxnv\Nesttab\Models\TablesModel();
    $arr1 = $tm->getAllForSelect();
    $arTables = $tm->getAllTablesSelectData($arr1);
    $curTable = 0;
    $flds = []; // fields loaded
    if ($e->hasErr()) {
        if (!isset($r['table_id'])) die('No link table id');
        $curTable = intval($r['table_id']);
        $flds = (isset($r['flds']) ? $r['flds'] : []);
        if ($curTable <>0) $sFldList = getSelCol($curTable);
    }
} else {
    // редактируем существующую запись
    $linkedTable = \Alxnv\Nesttab\Models\TablesModel::getOne($r['ref_table']);
    if ($e->hasErr()) {
        if (!isset($r['table_id'])) die('No link table id');
        $curTable = intval($r['table_id']);
        $flds = (isset($r['flds']) ? $r['flds'] : []);
    } else {
        $curTable = $r['ref_table'];
        $flds = $fieldModel->getSelectData($r['id']);
    }
    $sFldList = getSelCol($curTable);
}
$visibleTable = ((count($flds) > 0) ? 1 : 0);
?>
<script>
    var fld_list = [<?=$sFldList?>]; // новая запись для параметров
     // трансформации (добавляется по нажатию кнопки "+"
    var _this;
    var baseUrl = '<?=asset('/' . config('nesttab.nurl'))?>';
</script>

<?php
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
if (isset($r['opt_fields'])) {
    $optOpened = true;
} else {
    $optOpened = false;
    if ($e->hasOneOf(['name', 'required'])) $optOpened = true; // если есть ошибки, относящиеся к
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
?> : 
<?php 
echo $e->getErr('table_id');
?>
<select name="table_id"
        @change="select_item()" v-model="tables_index"><?= \Alxnv\Nesttab\core\HtmlHelper::makeselect($arTables, -1)?></select>
<br />
<br />
<?php
} else {
?>
<br />
<?=__('Link to table'). ': ' . \yy::qs($linkedTable['descr'] . ' (' . $linkedTable['name'] . ')')?><br />
<input type="hidden" name="table_id" v-model="tables_index" />
<?php
}
?><br /><div v-show="visible">
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
<?php
echo $e->getErr('required');
echo  '<input id="required" type="checkbox"'
        . ' name="req" ' .(isset($r['req']) ? 'checked="checked"' : '') . ' />'
        . ' <label for="required">' . __ ('Is required') .'</label><br />';
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
    $id1 = $flds[$i];
    echo "{
        flds: fld_list,
        vl: " . $id1 . ",
        err: " . '"' . \yy::jsmstr($e->getErr('flds' . $id1)) . '"' .  "   },
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
              _this.recs.splice(0, 1000000);
              fld_list = [];
              _this.visible = 0;
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