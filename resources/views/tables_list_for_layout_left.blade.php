<?php

/**
 * вид, включаемый слева в шаблоне на каждой странице
 *  выводит список всех таблиц верхнего уровня, отсортированный по названию таблицы
 *   (descr)
 *   элементы спика ведут к редактированию содержания таблиц
 */

global $yy, $td;

//$list = \Alxnv\Nesttab\Models\TablesModel::getAllByDescr();

echo '<ul>';

/*foreach ($list as $tbl) {
    echo '<li><a href="' . $yy->nurl . 'edit/' . $tbl['id'] . '">' .
            \yy::qs($tbl['descr']) . '</a></li>';
}*/
/*echo '<pre>';
var_dump($td);*/
if (isset($td['cat'][0])) {
    foreach ($td['cat'][0] as $ind) {
        $tbl = $td['dat'][$td['ind'][$ind]];
        echo '<li><a href="' . $yy->nurl . 'edit/0/' . $tbl[0] . '">' .
            \yy::qs($tbl[3]) . '</a></li>';
        
    }
}

echo '</ul>';