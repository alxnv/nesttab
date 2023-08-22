<?php
global $yy;
//$pppp = 1;
?>
@extends(config('nesttab.layout'))
@section('content')
<div id="error_div"></div>
<div id="main_contents">
    Ajax до бесконечности, каждый запуск на сервере выполняется 100 секунд<br /><br />
    <?php
    echo 'max execution time on server: ' . $yy->settings2['max_exec'];
    ?>
    <br />    <br />    <br />
    <div id="elt">
        @{{ seconds }} : @{{ status }}
    <hr />
    <button @click="run">Запустить</button>
    &nbsp;
    <button @click="stop">Остановить</button>
    <br /><br />
        <div id="log">

        </div>
    </div>
</div>
Выполнилось 20 раз по 110 секунд (при max execution time 120) затем выдало ошибку
 "max execution time exceeded"
<script>
    var baseUrl = '<?=asset('/' . config('nesttab.nurl'))?>';
    var toStop = 0;
    var objRef;

    const Elt = {
        data() {
            return {
                seconds : '0',
                status : 'Ajax не выполняется',
            }
        },
        methods: {
            run() {
                    //alert(3);
                    start_ajax_timer(this.$data);
                    this.$data.status = 'Ajax выполняется';
                    //alert(this.$http); // undefined
                    objRef = this;
                    exec_ajax_json(baseUrl +'/tests/ajax_infinite_run', {},
                        function (data) {
                              //alert(data[0]);
                              $('#log').append('Получено: ' + data[0].message  + '<br />');
                              //setTimeout(runAgain, 100);
                              if (!toStop) objRef.run();
                        });
            },
            stop() {
                    toStop = 1;
                    clearInterval(timerInterval);
            },
        }
    }
    const app = Vue.createApp(Elt);
    app.mount('#elt');
    //alert(4);
</script>
@endsection
