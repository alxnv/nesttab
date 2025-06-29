<?php
global $yy, $td;
?>
@extends(config('nesttab.layout'))
@section('content')
<script type="text/javascript">
    var baseUrl = '<?=asset('/' . config('nesttab.nurl'))?>';
</script>
<?php

echo '<div id="main_contents">'; // div с основным содержимым страницы


if ($tbl['p_id'] == 0) {
    echo '<p><a href="' . $yy->baseurl . config('nesttab.nurl') . '/change-struct-list">' . __('All tables') . '</a></p><br />';
} else {
    echo '<p><a href="' . $yy->nurl . 'change-struct-list">' . __('All tables') . '</a> -&gt; ';
    $ar3 = \Alxnv\Nesttab\core\FormatHelper::breadcrumbs($yy->nurl . 'struct-change-table/edit/', $tbl['p_id']);
    echo \Alxnv\Nesttab\core\FormatHelper::breadcrumbsShow($ar3);
    echo '</p>';
}

echo '<br /><br /><h1 class="center">' . __('Edit table') . ' "' . \yy::qs($tbl['descr']) . '" (' .
        __('physical name') . ': ' . \yy::qs($tbl['name']) .')<br /><br />';
$tt = $tbl['table_type'];
$s = \Alxnv\Nesttab\core\Helper::table_types($tt);

echo __('Table type') . ': ' . \yy::qs($s) . '</h1>';

echo '</br /><p class="center"><a href="' . $yy->nurl . 'struct-table-show-settings/' . $tbl_id .'">'
         . __('Table settings') . '</a></p>';

echo '<hr />';
echo '<p class="center"><a href="' . $yy->nurl . 'struct-add-table/' . $tbl_id .'">'
         . __('Add nested table') . '</a></p>';

// вывести список всех таблиц следующего уровня, вложенных в данную
echo \Alxnv\Nesttab\core\TableHelper::childTables($tbl_id, ' class="center"', 
        function ($ind, $ap) {
            global $td, $yy;
            if (isset($td['ind'][$ind]) && isset($td['dat'][$td['ind'][$ind]])) {
                $row = $td['dat'][$td['ind'][$ind]];
                return '<a href="' . $yy->nurl .  'struct-change-table/edit/' . $row[0] . '">' . \yy::qs($row[3]) . '</a><br />';
            } else {
                return '';
            }
        }, []);
echo '<hr />';

echo '<br /><p class="center"><a class="addfield" href="' . $yy->nurl . 'struct-table-edit-field/index/' . $tbl_id . '/' . $prev_link . '">' . __('Add field') . '</a>'
        . '</p>';


echo '<br /><div id="idt" class="table center2 div-table">';
echo '<div class="div-th"><span>№</span><span>' . __('Name') . '</span><span>' . \yy::mb_ucfirst(__('physical name')) . '</span>'
        . '<span>' . __('Type') . '</span>' //'<th>' . __('Description') . '</th>'
        .  '</div>';
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
  },
  props: ['item'],
  template: '<div><span>@{{item.pos}} </span>\
  <span><a href="#"  @click="onChange">\
@{{ item.text }}</a></span>\
<span><a href="#"  @click="onChange">\
@{{ item.name }}</a></span>\
<span><a href="#"  @click="onChange">@{{ item.flddescr }}</a></span>\
  </div>'
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
    echo \yy::jsmstr((trim($f['descr']) == '' ? '-------' : $f['descr']));
    echo '"';
    echo ', name:"';
    echo \yy::jsmstr((trim($f['name']) == '' ? '-------' : $f['name']));
    echo '"';
    echo ', flddescr:"';
    echo \yy::jsmstr(($f['descr_fld']));
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
