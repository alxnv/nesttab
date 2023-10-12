<?php
global $yy;
?>
@extends(config('nesttab.layout'))
@section('content')
Tests how ConvertEmptyStringsToNull (and maybe TrimStrings) middleware
convert data

<br />
<br />
Проверено. В общем данные не удаляет, просто делает alltrim() и подставляет значение null в пустые строки
<form action="<?=$yy->nurl?>tests/save-input-null-test" method="post" /><!-- comment -->
@csrf
<input type="hidden" name="var" value=" " />
<input type="hidden" name="arr[]" value=" " />
<input type="hidden" name="arr[]" value="1" />
<input type="hidden" name="arr[]" value="" />

<input type="hidden" name="arr[]['node']" value=" " />
<input type="hidden" name="arr[]['node']" value="1" />
<input type="hidden" name="arr[]['node']" value="" />

<br />
<input type="submit" value="Send" />
</form>

@endsection