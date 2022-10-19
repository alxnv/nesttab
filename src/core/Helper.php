<?php

/**
 * Класс со вспомогательными функциями проекта
 */

namespace Alxnv\Nesttab\core;

class Helper {
    /**
     * Возвращает table_types, соответствующее заданному коду вида таблицы ('O','L','C','V')
     * @global type $yy
     * @param type $s
     * @return type
     */
    public static function table_types($s) {
        global $yy;
        $arrt = $yy->settings2['table_types'];
        $arr_ts = $yy->settings2['table_names_short'];

        $k = array_search($s, $arr_ts);
        $s2 = ($k === false ? '----' : $arrt[$k]);
        return $s2;
    }

    public static function get_prev_link_str($s) {
        global $yy;
        if (count($s) < 2) throw new Exception('Link data is not defined', 64003);
        switch ($s[0]) {
            case 1: 
                return $yy->baseurl . 'struct-change-table/edit/t/' . $s[1]; // !todo - еще добавить
                    // передачу prev->link
                break;
            default:
                throw new Exception('Link id is not defined', 64004);
                break;
        }
    }


    /**
     * отображаем ссылку "уровень вверх",
     *  если $controller->prev_link<>''
     *    формат $controller->prev_link: 'back_link_type,prev_table'
     *      prev_table - Id предыдущей таблицы
     *  ссылки "Назад" и "Выйти"
     * 
     * @param type $controller
     */
    public static function show_prev_link($controller) {
        global $yy;
        echo '<div id="hyperlinks">';
        echo '<div class="prev_link float_left">';
        if (isset($controller->prev_link) && $controller->prev_link <> '') {
            $s = split(',', $controller->prev_link);
            echo '<p><a href="' . \app\core\Helper::get_prev_link_str($s) . '">' . \yy::t('One level up') .'</a></p>br />';
        }
        echo '<p><a href="#" onClick="history.back()">' . \yy::t('Back') . '<a></p>';
        echo '</div><div class="float_right">';
        echo '<a href="' .($yy->user_data->use_custom_user_mangement_system
		? $yy->user_data->get_logout_url() : $yy->baseurl . 'logout.php'). '">'
                . \yy::t('Exit') . '</a>';
        echo '</div><div class="clear"></div>';
        echo '</div>';
    }

    /**
     * добавляет к первому массиву ключи из второго
     * @param array $arr
     * @param array $arr2
     * @return array
     */
    public function assignData(array $arr, array $arr2) {
        $arr3 = $arr;
        foreach ($arr2 as $key => $value) {
            $arr3[$key] = $value;
        }
        return $arr3;
    }
}