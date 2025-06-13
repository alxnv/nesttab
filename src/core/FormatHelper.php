<?php


/**
 * Description of FormatHelper
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class FormatHelper {
    /**
     * Возвращает дерево из массива $arr в виде ненумерованного списка
     * @param array $arr - массив вида [$parent_id => [$row, $row, ...]]
     * @param int $i - с какого $parent_id начинать обход
     * @param callable $getValue - функция, возвращающая выводимое значение в строке по значению элемента
     * @param callable $getId - функция, возвращающая айди элемента по значению элемента массива
     * @return string
     */
    public static function getTree(array &$arr, int $i, callable $getValue, callable $getId) {
        $s = '<ul>';
        if (!isset($arr[$i])) {
            return '';
        }
        foreach ($arr[$i] as $row) {
            $value = $getValue($row);
            $s .= ('<li>' . $value . '</li>');
            $id = $getId($row);
            if (isset($arr[$id])) {
                $s .= static::getTree($arr, $id, $getValue, $getId);
            }
        }
        $s .= '</ul>';
        return $s;
    }
    /**
     * Проверить содержится ли строка $needle в массиве строк $haystack (case insensitive)
     * @param string $needle
     * @param array $haystack
     * @return boolean
     */
    public static function inListCaseInsensitive(string $needle, array $haystack) {
        foreach ($haystack as $value) {
            if (mb_strtolower($needle) == mb_strtolower($value)) return true;
        }
        return false;
    }
    /**
     * Проверить, является ли имя расширения файла допустимым для размещения на сервере
     * @param string $a - имя расширения файла
     * @return boolean 
     */
    public static function validExt(string $a) {
        if (in_array(strtolower($a), ['py', 'pl', 'vb', 'js'])) return false;
        if (str_starts_with(strtolower($a), 'php')) return false;
        return true;
    }
    
    /**
     * сформировать хлебные крошки по $td
     * @global type $td
     * @param string $prefix - префикс урл
     * @param int $id - айди таблицы начального уровня хлебных крошек
     * @return array - [[url, $tbl->descr]]
     */
    public static function breadcrumbs(string $prefix, int $id) {
        global $td;
        $id2 = $id;
        $arr = [];
        while ($id2 <> 0) {
            if (isset($td['ind'][$id2])) {
                $ind = $td['ind'][$id2];
                if (!isset($td['dat'][$ind])) {
                    return [];
                }
            } else {
                return [];
            }
            $row = $td['dat'][$ind];
            $arr[] = [$prefix . $row[0],$row[3]];
            $id2 = $row[1];
        }
        return $arr;
    }
    /**
     * отобразить хлебные крошки из массива $arr
     *   (массив $arr создается, например, функцией $this::breadcrumbs())
     * @param array $arr
     * @param type $delimeter - разделитель отображения
     * @return string
     */
    public static function breadcrumbsShow(array $arr, $delimeter = ' -&gt; ') {
        $ar2 = [];
        for ($i = count($arr) - 1; $i>=0; $i--) {
            $ar2[] = '<a href="' . $arr[$i][0] . '">' . \yy::qs($arr[$i][1]) . '</a>';
        }
        return join($delimeter, $ar2);
    }
    
    /**
     * Возвращаем хлебные крошки для режима редактирования таблицы
     * @global \Alxnv\Nesttab\core\type $td
     * @global type $yy
     * @param int $id - id родительского элемента в структуре таблиц
     * @param int $rec_id - номер записи родительского элемента
     * @return string - строка с хлебными крошками
     */
    public static function breadcrumbsEdit(int $id, int $rec_id) {
        global $td, $yy;
        // получаем цепочку родительских таблиц
        $arr = [];
        $b = false;
        /*// получаем родительский элемент $id
        if (isset($td['ind'][$id])) {
            $ind = $td['ind'][$id];
            if (!isset($td['dat'][$ind])) {
                return '';
            }
            $id2 = $td['dat'][$ind][1];
        } else {
            return '';
        }*/
        $id2 = $id;

        while ($id2 <> 0) {
            if (isset($td['ind'][$id2])) {
                $ind = $td['ind'][$id2];
                if (!isset($td['dat'][$ind])) {
                    return '';
                }
            } else {
                return '';
            }
            $row = $td['dat'][$ind];
            if ($row[4] <> 'O') $b = true;
            $arr[] = $row;
            $id2 = $row[1];
        }
        $s = '';
        $ar2 = [];
        if (!$b) { // все родительские элементы - типа 'one'
            for ($i = 1; $i<count($arr);$i++) {
                $row = $arr[$i];
                $k = ($row[1] == 0 ? 0 : 1);
                $ar2[] = [$yy->nurl . 'edit/' . $k . '/' . $row[0], $row[3]];
            }
            $s = static::breadcrumbsShow($ar2);
        } else {
            // не все элементы типа '0ne' - делаем запрос содержимого цепочки таблиц
            $row4 = static::getBread($id, $rec_id, $arr);
            if (is_null($row4)) return '';
            $ar4 = static::breadToButter($row4); // перевести все из одной записи в массив
            $s = static::showBread($ar4);
        }
        return $s;
        
    }
    
    /**
     * перевести все из одной записи в массив     
     * @param array $row
     * @return array
     */
    public static function breadToButter(array $row) {
        $arr = [];
        $i = 1;
        $ss = 's' . $i . "_";
        while (isset($row[$ss . 'id'])) {
            $arr[] = [$row[$ss . 'id'], $row[$ss . 'type'], $row[$ss . 'tbln'], $row[$ss . 'tid'], $row[$ss . 'name']];
            $i++;
            $ss = 's' . $i . "_";
        }
        return $arr;
    }
    
    /**
     * получить массив со всеми данными цепочки родительских элементов в одной строке
     * @param int $id - id родительской таблицы
     * @param int $rec_id - id записи родительской таблицы
     * @param array $arr - массив цепочки родительских таблиц (начиная с родительской и до 0 уровня)
     * @return array - массив со всеми данными цепочки родительских элементов в одной строке
     */
    public static function getBread(int $id, int $rec_id, array $arr) {
        global $db;
        if (count($arr) < 2) return [];
        $arV = []; // "s1.id, s1.name, s2.id, s2.name"
        $arJ =[]; // left join
        for ($i = 0; $i < count($arr); $i++) {
            if ($i == 0) {
                $ss = 's1';
                $s1 = " left join " . $arr[$i+1][2] . " " . $ss . " on s0.parent_id = "
                        . $ss . ".id ";
                $arJ[] = $s1;
                continue;
            }
            $s8 = $db->escape($arr[$i][3]); // descr
            $ss = 's' . $i;
            $s1 = " " . $ss . ".id as " . $ss . "_id, '" . $arr[$i][4] . "' as " . $ss . "_type, "
                    .  $s8 . " as " . $ss . "_tbln, "
                    . $arr[$i][0] . " as " . $ss . "_tid";
            if ($arr[$i][4] == 'O') {
                $s1 .= ", '' as " . $ss . "_name";
            } else {
                $s1 .= ", " . $ss . ".name as " . $ss . "_name";
            }
            $arV[] = $s1;
            if ($i <> 1) {
                // все кроме первой записи
                $ss0 = 's' . ($i -1);
                $s1 = " left join " . $arr[$i][2] . " " . $ss . " on " . $ss0 . ".parent_id = "
                        . $ss . ".id ";
                $arJ[] = $s1;
            }
        }
        $s3 = join(', ', $arV);
        $s4 = join('', $arJ);
        $s = "select " . $s3 . " from " . $arr[0][2] . " s0 " . $s4 . " where s0.id = $rec_id";
        $row = $db->q($s);
        //$s .= print_r($row,true);
        // select from s1 left join s2 on s1.parent_id=s2.id, left join s3 on ...
        return $row;
    }
    /**
     * возвратить отображение в виде хтмл хлебных крошек
     * @param array $ar4 - [url, text, bool isRec(является ли страницей редактирования записи,
     *   или общей страницей таблицы),id,id2,id3]
     * @param string $delim - delimeter
     */
    public static function showBread(array $ar4, $delim = ' -&gt; ') {
        global $yy;
        //return print_r($ar4,true);
//            $arr[] = [$row[$ss . 'id'], $row[$ss . 'type'], $row[$ss . 'tbln'], $row[$ss . 'tid'], $row[$ss . 'name']];
        $s = '';
        for ($i = count($ar4) - 1; $i >= 0; $i --) {
            $id = ($i == count($ar4) - 1 ? 0 : $ar4[$i + 1][0]); //parent_id
            $s .= '<a href="' . $yy->nurl . 'edit/' . $id . '/' . $ar4[$i][3] . '">'
                    . \yy::qs($ar4[$i][2]) . '</a>';
                    
            if ($ar4[$i][1] <> 'O') {
                // not 'one' table type
                $s .= ' : &quot;<a href="' . $yy->nurl . 'editrec/' . $id . '/' . $ar4[$i][3] 
                    . '/' . $ar4[$i][0] . '">'
                    . \yy::qs($ar4[$i][4]) . '</a>&quot;';
                
            }
            $s .= $delim;
        }
        return $s;
    }
}
