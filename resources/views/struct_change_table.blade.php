<?php
global $yy;
?>
@extends(config('nesttab.layout'))
@section('content')
<script type="text/javascript">
    var baseUrl = '<?=asset('/nesttab')?>';
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
