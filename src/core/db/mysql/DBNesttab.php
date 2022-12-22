<?php
// функции для работы с БД mysql
namespace Alxnv\Nesttab\core\db\mysql;

use Illuminate\Support\Facades\DB;

class DbNesttab extends \Alxnv\Nesttab\core\db\BasicDbNesttab {
    /**
     * Проверяет валидность имени таблицы Mysql, и возвращает пустую строку в случае валидности,
     *   либо строку ошибки
     * @param string $tbl_name
     * @return string
     */
    public function valid_table_name(string $tbl_name) {
        global $yy;
        $err = '';
      	if (!preg_match('/^[a-z][a-z0-9\_]+$/', $tbl_name)) {
            $err .=  chr(13) . __("The name of the table is not correct. It must begin with a-z. Next symbols allowed: a-z, 0-9, '_'");
	}
        if (mb_strlen($tbl_name) > $yy->db_settings['max_table_name_size']) {
            $err .= chr(13) . __("The name of the table is too long. It mustn't exceed") .
                    $yy->db_settings['max_table_name_size'] . __("characters");
        }
        return $err;

    }
    
    /**
     * Проверяет валидность имени поля Mysql, и возвращает пустую строку в случае валидности,
     *   либо строку ошибки
     * @param string $tbl_name
     * @return string
     */
    public function valid_field_name(string $fld_name) {
        global $yy;
        $err = '';
      	if (!preg_match('/^[a-z][a-z0-9\_]+$/', $fld_name)) {
            $err .=  __("The name of the field is not correct. It must begin with a-z. Next symbols allowed: a-z, 0-9, '_'");
	}
        if (mb_strlen($fld_name) > $yy->db_settings['max_custom_field_size']) {
            if ($err <> '') $err .= chr(13);
            $err .= __("The name of the table's field is too long. It mustn't exceed") .
                    $yy->db_settings['max_custom_field_size'] . __("characters");
        }
        return $err;

    }

}