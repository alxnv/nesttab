<?php

/**
 * вид, включаемый слева в шаблоне на каждой странице
 *  выводит список всех таблиц верхнего уровня, отсортированный по названию таблицы
 *   (descr)
 *   элементы спика ведут к редактированию содержания таблиц
 */

global $yy;

$list = \Alxnv\Nesttab\Models\TablesModel::getAllByDescr();

echo '<hr />';
//echo '<h2>' . __("Tables list") . ':</h2>';
echo '<ul>';

foreach ($list as $tbl) {
    echo '<li><a href="' . $yy->baseurl . 'nesttab/edit/' . $tbl['id'] . '">' .
            \yy::qs($tbl['descr']) . '</a></li>';
}

echo '</ul>';