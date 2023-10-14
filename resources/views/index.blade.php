<?php
global $yy;
$pppp = 1;
?>
@extends(config('nesttab.layout'))
@section('content')
<?php
if (!extension_loaded('gd')) {
    echo '<div class="error">' . sprintf(__("PHP extension '%s' has not been loaded"), 'gd') 
          . '<br />'
          .  __('Image related functionality of Nesttab would be impossible') . '</div>';
}
?>
@endsection