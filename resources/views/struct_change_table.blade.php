<?php
global $yy;
?>
@extends(config('nesttab.layout'))
@section('content')


<script type="text/javascript">
    var baseUrl = '<?=asset('/nesttab')?>';




function confirm_it(id_passed) {
//    debugger;
    $.confirm({
        useBootstrap: false,
        content: __lang('Do you really want to delete this element?'),
        title: '',
        backgroundDismiss: true,
        id_passed: id_passed,
     buttons: {
            yes: {
                text: __lang('Yes'),
                action: function(){
              /*      $.alert({
                        title: 'Alert!',
                        useBootstrap: false,
                        content: 'Simple alert!', }*/
                    //alert(this.id_passed);
                    exec_ajax_json(baseUrl +'/struct-change-table/delete/' + this.id_passed, {},
                        function () {
                            // refresh the page
                            location.href=baseUrl + '/struct-change-table/edit/<?=$tbl['id']?>/0';
                            
                        });
                        // возвращает {error: '<html of error>') если была ошибка удаления
                    return true;
                    }               
            },
            no: {
                text: __lang('No'),
                action: function(){
                    //$.alert('A or B was pressed');
                    return true;
                }
            }
        }
    });
}


</script>
    
<?php

echo '<div id="main_contents">'; // div с основным содержимым страницы
echo '<h1 class="center">' . __('Edit table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        __('physical name') . ': ' . \yy::qs($tbl['name']) .')<br /><br />';
$tt = $tbl['table_type'];
$s = \Alxnv\Nesttab\core\Helper::table_types($tt);

echo __('Table type') . ': ' . \yy::qs($s) . '</h1>';


echo '<br /><p class="center"><a class="addfield" href="' . $yy->baseurl . 'nesttab/struct-table-edit-field/index/' . $tbl_id . '/' . $prev_link . '">' . __('Add field') . '</a>'
        . '</p>';


echo '<br /><div id="idt" class="table center2 div-table">';
echo '<div class="div-th"><span>№</span><span>' . __('Name') . '</span>' //'<th>' . \yy::mb_ucfirst(__('physical name')) . '</th>'
        . '<span>' . __('Type') . '</span>' //'<th>' . __('Description') . '</th>'
        . '<span>' . __('Operations') . '</span></div>';
?>
<table-elt
      v-for="item in itemsList"
      v-bind:item="item"
      v-bind:key="item.id"
    ></table-elt>

<?php
    

echo '</div>';
echo '</div>';
echo '<div id="error_div"></div>';
//var_dump($flds);
?>
<script type="text/javascript">
const TableElt = {
  methods: {
	onChange: function() {
            //alert(this.item.text) 
            location.href=baseUrl + '/struct-table-edit-field/step2/<?=$tbl['id']?>/'
                + this.item.id;

        },
        onDelete: function() {
            confirm_it(this.item.id);
        },
        onMove: function() {
            location.href=baseUrl + '/struct-change-table/move/<?=$tbl['id']?>/'
                + this.item.id + '/moveto/' + this.item.moveto;
            
        },
  },
  props: ['item'],
  template: '<div><span>@{{item.pos}} </span>\
  <span>@{{ item.text }}</span><span>@{{ item.flddescr }}</span>\
<span><input class="change-button" type="button" value="<?=__('Change')?>" @click="onChange" />&nbsp;\
    <?=__('To position')?>: \
   <input type="number" class="table_edit" v-model="item.moveto" />\
    &nbsp;<input type="button" class="move-button" @click="onMove" value="<?=__('Move')?>" />\
    &nbsp;<input type="button" class="delete-button" @click="onDelete" value="<?=__('Delete')?>" />\
  </span></div>'
}

const EltList = {
  data() {
    return {
    itemsList: [
<?php        

foreach ($flds as $f) {
    echo '{ pos:';
    echo $f['ordr'];
    echo ', moveto:';
    echo $f['ordr'];
    echo ', id:';
    echo $f['id'];
    echo ', text:"';
    echo \yy::jsmstr(\yy::qs(trim($f['descr']) == '' ? '-------' : $f['descr']));
    echo '"';
    echo ', flddescr:"';
    echo \yy::jsmstr(\yy::qs($f['descr_fld']));
    echo '"';
    echo ' },' . "\r\n";
}
?>
    ]
    }
  },
  components: {
    TableElt
  }
}

const app = Vue.createApp(EltList)

app.mount('#idt')
</script>
@endsection
