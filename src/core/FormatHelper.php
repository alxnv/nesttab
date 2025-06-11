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
            
}
