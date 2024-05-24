<?php
global $yy;
$pppp = 1;
?>
@extends(config('nesttab.layout'))
@section('content')
<?php
/*$cf = config('nesttab.jlkjkjl');
var_dump($cf);*/
if (!extension_loaded('gd')) {
    echo '<div class="error">' . sprintf(__("PHP extension '%s' has not been loaded"), 'gd') 
          . '<br />'
          .  __('Image related functionality of Nesttab would be impossible') . '</div>';
}
?>
@endsection