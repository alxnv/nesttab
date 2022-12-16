<?php

/* 
 * Базовый класс работы со структурой таблицы (от него наследуются все остальные классы
 *  в этом каталоге
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class BasicModel {

    /**
     * обрабатываем считанные из yy_columns данные и подготавливаем их для
     *   дальнейшей работы
     * @param type $ov
     * @return type
     */
    public function prepare_old_values($ov) {
        $ov2 = $ov;
        $ov2['parameters'] = json_decode($ov2['parameters']);
        return $ov2;
    }
}
