<?php
/**
 * модуль для включения во view редактирования записей
 *  
 *  - выводит поле 'edit' для редактирования позиции, куда переместить запись,
 *     кнопку "Переместить" для начала перемещения, и кнопку "Удалить" для удаления
 *     этой записи
 * @param $returnToPage - номер страницы вывода записи в списке
 * 
 * input values:
 *   $fld['ordr']
 */
if ($rec_id <> 0) { // если не новая запись
    $isDeletable = true;
?>
<div id="idt">
    <table-elt></table-elt>
</div>
<script type="text/javascript">
    var baseUrl = '<?=asset('/' . config('nesttab.nurl'))?>';




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
                    exec_ajax_json(baseUrl +'/editrec/delete/<?=$tbl['id']?>/' + this.id_passed, {},
                        function () {
                            // refresh the page
                            location.href=baseUrl + '/editrec/<?=$tbl['id']?>/0';
                            
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


const TableElt = {
  data() {
    return {
        moveto: <?=$moveTo?>,
        id: <?=$rec_id?>
    }
  },
  methods: {
        onDelete: function() {
            confirm_it(this.id);
        },
        onMove: function() {
            location.href=baseUrl + '/editrec/move/<?=$tbl['id']?>/'
                + this.id + '/' + this.moveto + '/<?=$returnToPage?>';
            
        },
  },
  template: '<?=__('To position')?>: \
   <input type="number" class="table_edit" v-model="this.moveto" />\
    &nbsp;<input type="button" class="move-button" @click="onMove" value="<?=__('Move')?>" />\
    <?=($isDeletable ? '&nbsp;<input type="button" class="delete-button" @click="onDelete" value="' . __('Delete') . '" />' : '')?><hr />'
}


const app22 = Vue.createApp(TableElt)

app22.mount('#idt')
</script>
<?php
} // if ($r['id'])
?>