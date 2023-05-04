var d=document;
var timerElement;
var timerSeconds;
var timerInterval = null;

 function restore_main_divs() {
   $('#error_div').hide();
   $('#hyperlinks').show();
   $('#main_contents').show();
   return false;
   
 }


function show_error_div() {
   $('#hyperlinks').hide();
   $('#main_contents').hide();
   
   $('#error_div').show();
    
}

function exec_ajax_2(funct, onSuccess) {
    return wrapJquery(
    [funct, onSuccess]);       
};
function wrapJquery(c) {
    return new Promise(function(resolve, reject) {
        c[0]
        .then(function(data) {
          resolve([data, c]);
        }, function(err) {
          reject([err, c]);
        });
     })
}
    
function showError(e) {
//    console.log("this: ");
//    console.log('<br /><br /><p class="center"><span class="backlink">' +
//'<a href="#" onclick="return destroy_dom_elt' + "('"+e[1][0].id+"'"+')">Назад</a></span></p> ');
    //debugger;
    div = $('#error_div');
    //console.log(e);
    
    $(div).html('<p><a href="#" onclick="return restore_main_divs()">' +
            __lang('Back') + '</a></p><br /><div class="red_contents">' + e +
            '</div>' + '<br /><br />');
    show_error_div();
    //debugger;
    /*if (e[0].status) {
        throw e[0].status; // return in jquery!
    } else {
        throw e[0];
    }*/
}
    
    
/**
 * Выполняет переданный ajax json
 * в случае ошибки открывает div с ошибкой и скрывает основной div и div 
 *   с гиперссылками ("назад", ...)
 * @returns {undefined}
 */
function exec_ajax_json(url, data, onSuccess) {
    // !!! todo: в дальнейшем предусмотреть возможность возвращения параметра ticket,
    //    означающего, что нужно продолжать вызывать этот url пока возвращается этот параметр
    //     ticket
    //  может возвращать в массиве json сообщение об ошибке
    exec_ajax_2($.ajax({
        dataType: 'json',
        url: url,
        data: {data:data}}), onSuccess).then(function (data) {
            if ('error' in data[0]) {
                showError(data[0]['error']);
            } else {
                // без ошибок
                let funct = data[1][1];
                funct(data);
            }

        })
    .catch(handleError);
 
 }
    
function handleError(e) {
    if (typeof e === 'undefined' || typeof e[0] === 'undefined') {
       showError('undefined returned, maybe there is an error in js code inside function call');
    } else {
    showError(e[0].responseText);
    }
}    
/**
 * Проходим по родителям dom элемента, ищем ближайшего с классом class_name
 * @param {type} class_name
 * @param {type} elt
 * @returns {e}
 */    
function findParentOfClass(class_name, el) {
    //debugger;
    while (el.parentNode) {
        el = el.parentNode;
        if (el.className === class_name)
            return el;
    }
    return null;
}

 


function ajaxErrorFunct(jqXHR, exception) {
        s = '';
	if (jqXHR.status === 0) {
		s = 'Not connect. Verify Network.';
	} else if (jqXHR.status == 404) {
		s = 'Requested page not found (404).';
	} else if (jqXHR.status == 500) {
		s = 'Internal Server Error (500).';
	} else if (exception === 'parsererror') {
		s = 'Requested JSON parse failed. ' + jqXHR.responseText;
	} else if (exception === 'timeout') {
		s = 'Time out error.';
	} else if (exception === 'abort') {
		s = 'Ajax request aborted.';
	} else {
		s = 'Uncaught Error. ' + jqXHR.responseText;
	}
        showError(s);
    
}

function start_ajax_timer(elt) {
    if (timerInterval !== null) clearInterval(timerInterval);
    timerElement = elt;
    timerSeconds = 0;
    show_timer_seconds();
    timerInterval = window.setInterval(function() {
        timerSeconds++;
        show_timer_seconds();
    }, 1000);
}

function show_timer_seconds() {
    timerElement.seconds = timerSeconds;
}

function __lang(s) {
	// language translation of the string
	if (s in lang_data) {
		return lang_data[s];
	} else {
		return s;
	}
}