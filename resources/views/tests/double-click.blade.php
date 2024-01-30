@extends(config('nesttab.layout'))
@section('content')
Для обработчика удаления записей в физической БД (по ajax)<br />
<script>
    var cntr = 0;
    var dt5 = new Date(0);
    function click5() {
        //alert(4);
        let dt = new Date();
        //alert(dt.getTime());alert(dt5.getTime);
        let delta = dt.getTime() - dt5.getTime();
        //alert('delta ' + delta);
        if (delta > 2000) {
            //alert(5);
            cntr++;
            $('#cnt').html(cntr);
            dt5 = dt;
        }
    }
</script>
<div id="cnt">0</div>
<button onclick="click5()">Click</button>
@endsection
