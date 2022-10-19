<?php
global $yy;
?>
@extends($yy->settings['layout'])
@section('content')


<script type="text/javascript">
    var baseUrl = '<?=asset('/nesttab')?>';




function confirm_it(id_passed) {
//    debugger;
    $.confirm({
        useBootstrap: false,
        content: captions_and_names[3],
        title: '',
        backgroundDismiss: true,
        id_passed: id_passed,
     buttons: {
            yes: {
                text: captions_and_names[1],
                action: function(){
              /*      $.alert({
                        title: 'Alert!',
                        useBootstrap: false,
                        content: 'Simple alert!', }*/
                    //alert(this.id_passed);
                    exec_ajax_json(baseUrl +'struct-change-table/delete/id/' + this.id_passed, {},
                        function () {
                            // refresh the page
                            location.href=baseUrl + 'struct-change-table/edit/<?=$tbl['id']?>/prev/<?=$prev_link?>';
                            
                        });
                        // возвращает {error: '<html of error>') если была ошибка удаления
                    return true;
                    }               
            },
            no: {
                text: captions_and_names[2],
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


echo '<br /><table id="idt" class="table center2">';
echo '<tr><th>№</th><th>' . __('Name') . '</th><th>' . \yy::mb_ucfirst(__('physical name')) . '</th>'
        . '<th>' . __('Type') . '</th><th>' . __('Description') . '</th>'
        . '<th>' . __('Operations') . '</th></tr>';
$n = 1;
foreach ($flds as $f) {
    echo '<tr><td>';
    echo $f['ordr'];
    echo '</td><td>';
    echo \yy::qs(trim($f['descr']) == '' ? '-------' : $f['descr']);
    echo '</td><td>';
    echo \yy::qs($f['name']);
    echo '</td><td>';
    echo \yy::qs($f['descr_fld']);
    echo '</td><td>';
    // $f['parameters']
    echo '</td><td>';
    echo '<input class="change-button" type="button" data-id="' . $f['id'] . '" value="' . __('Change') . '" />&nbsp;';
    echo __('To position') . ': ';
    echo '<input type="text" data_id="' . $f['id'] . '" id="e' . $n . '" class="table_edit" value="' . $f['ordr'] . '" />';
    echo '&nbsp;<input type="button" class="move-button" data-id="' . 
            $n . '" value="' . __('Move') . '" />';
    echo '&nbsp;<input type="button" data-id="' . $f['id'] . '" class="delete-button" value="' . __('Delete') . '" />';
    echo '</td></tr>';
    $n++;
}
echo '</table>';
echo '</div>';
echo '<div id="error_div"></div>';
//var_dump($flds);
?>
<script type="text/javascript">
    $(function () {
        $('#idt .change-button').click(function (e) {
            let id = e.target.getAttribute('data-id');
            location.href=baseUrl + 'struct-table-edit-field/step2/<?=$tbl['id']?>/<?=$prev_link?>/'
                + id;

        })
        $('#idt .move-button').click(function (e) {
            let n = e.target.getAttribute('data-id');
            let input = document.getElementById('e' + n);
            id = input.getAttribute('data_id');
            //alert(input.value);
            location.href=baseUrl + 'struct-change-table/move/<?=$tbl['id']?>/<?=$prev_link?>/'
                + id + '/moveto/' + input.value;

        })
        $('#idt .delete-button').click(function (e) {
            let n = e.target.getAttribute('data-id');
            confirm_it(n);

        })

});
</script>
@endsection
