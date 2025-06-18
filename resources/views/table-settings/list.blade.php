<?php
global $yy;
?>
@extends(config('nesttab.layout'))
@section('content')
<?php

echo '<div id="main_contents">'; // div с основным содержимым страницы
echo  '<p><a href="' . $yy->baseurl . config('nesttab.nurl') . '/struct-change-table/edit/' . $tbl['id'] . '">Назад</a><br /><br /></p>';

$e = new \Alxnv\Nesttab\Models\ErrorModel();
$lnk_err = \yy::getErrorEditSession();
if (Session::has($lnk_err)) {
    $e->err = session($lnk_err);
    //if (count($e->err) > 0 ) dd($e);
}
echo \yy::getSuccessOrErrorMessage([], $e);

echo '<h1 class="center">' . __('Settings of the table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        __('physical name') . ': ' . \yy::qs($tbl['name']) .')<br /><br />';
$tt = $tbl['table_type'];
$s = \Alxnv\Nesttab\core\Helper::table_types($tt);

echo __('Table type') . ': ' . \yy::qs($s) . '</h1>';


echo '<br />' . __("Table ID") . ': ' . $tbl['id'];
echo '<br />' . __("Size in bytes of 'id' field") . ': ' . $tbl['id_bytes'];

if (is_null($recCnt)) {
    echo '<div class="error">' . __('Error accessing table') . '</div>';
} else {
    echo '<br />' . __('Number of records in the table') . ': ' . $recCnt;
}

/**
 * редактирование набора отображаемых в виде таблицы полей
 * 
 * если isset($r['is_error']), то произошел возврат к редактированию с ошибкой
 * $r['params'] отображаются в $r
 * $r['id'] - id поля

 *  */
global $yy, $db;


// редактируем существующую запись
$currentItem = 0;
if ($e->hasErr()) { // возврат ошибки здесь не предусматриваем, так что не проверено
    $flds = (isset($r['flds']) ? $r['flds'] : []);
    $canBeCur = (isset($r['canbecur']) ? $r['canbecur'] : []);
    $currentItem = $r['selectedItem'];
} else {
    $canBeCur = [];
    $flds = $fieldModel->getViewAsTableData($tbl['id'], $currentItem, $canBeCur);
}
//dd($canBeCur);
// список всех полей данной таблицы, доступных для добавления 
$sFldList = $fieldModel->getPossibleFieldsToViewAsTable($tbl);
//var_dump($sFldList);
$visibleTable = true; //((count($flds) > 0) ? 1 : 0);
if (is_null($currentItem)) $currentItem = -10000000;
?>
<script>
    var fld_list = <?=json_encode($sFldList)?>; // новая запись для параметров
     // трансформации (добавляется по нажатию кнопки "+"
    var _this;
    var baseUrl = '<?=asset('/' . config('nesttab.nurl'))?>';
</script>

<?php
$requires['need_confirm'] = 1;

echo '<form method="post" action="' . $yy->baseurl . config('nesttab.nurl') . '/struct-table-settings/save/' . $tbl['id'] .
        '"><div class="align-left">';
//$controller->render_partial(['r' => $r], 'all', 'all-fields');
?>
@csrf
<br />
<?=__('Table name') . ":" ?> <input type="text" size="60" name="descr" value="<?=\yy::qs($r['descr'])?>" /><br />
<div id="app">
<?php

echo $e->getErr('');
?>
<br /><div v-show="visible">
    <table class="table">
        <tr><th><?=__('Fields to show')?></th></tr>
        <tr><td>
            <div class="plus_button_small"><input type="button" value="+" @click="add(-1)" /></div>
            </td></tr>
        <tr v-for="(field, index) in recs">
                <td>
                    <input type="hidden" name="canbecur[]" v-model="field.can_be_cur" />
                     <div v-html="field.err"></div>
                    <div class="minus_button_small"><input type="button" value="-" @click="del(index)" /></div>
                    <select v-model="field.vl" name="flds[]">
                        <option v-for="fld in flds" :value="fld.id">@{{ fld.name }}
                    </select>
                    <span v-if="field.canbecur">
                        <input type="radio" :name="`radio-${index}`" :id="`radio-${index}`" v-bind:value="index" v-model="selectedItem">
                        <label :for="`radio-${index}`"><?=__("Sort by this field")?></label>
                    </span>
                    <div class="plus_button_small"><input type="button" value="+" @click="add(index)" /></div>
                </td>
            </tr>
    </table>    
    <input type="hidden" name="selectedItem" v-model="selectedItem" />
<br />
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
    <div id="info" style="white-space: pre;"></div>
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
      selectedItem: <?=$currentItem?>,
      flds: <?=json_encode($sFldList)?>,
      recs: [
<?php
for ($i = 0; $i < count($flds); $i++) {
    $id1 = $flds[$i];
    echo "{
        vl: '" . $id1 . "',
        canbecur: " . $canBeCur[$i] . ", // can it be the current item   
        cur: " . ((!is_null($currentItem) && ($currentItem == $i)) ? 1 : -1) . ",    
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
        /* _this = this;  
        exec_ajax_json(baseUrl +'/ajax_flds_for_select/' + this.tables_index, {},
          function (data) {
              //alert(data[0].arr[0].name);
              let v = (data[0].arr.length == 0 ? 0 : data[0].arr[0].id); 
              fld_list = data[0].arr;
              _this.recs.splice(0, 1000000, {vl:v, flds: data[0].arr, err:''});
              _this.visible = 1;
          });*/

    },
    make_selected(index) {
        //alert(index);
        //s = JSON.stringify(this.recs);
        //s = xlog(this.recs);
        //$('#info').html(s);//
        /*
        for (i = 0; i < this.recs.length; i++) {
            if (i != index) this.recs[i].cur = 0;
        }*/
    },
    add(index) {
      if (true) {
        let v = (fld_list.length == 0 ? 0 : fld_list[0].id);
        this.recs.splice(index + 1, 0, {cur:0, can_be_cur:1, vl:v, err:''});
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
/*
* 
 */
?>
<div id="error_div"></div>

@endsection
