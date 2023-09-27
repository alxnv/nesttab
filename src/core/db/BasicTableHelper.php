<?php
/**
 * Methods for tables
 */
namespace Alxnv\Nesttab\core\db;


class BasicTableHelper {
    /**
     * array of fields defined by default for primary tables of each type
     * @var array
     */
    public $definedFields = [
        'O' => ['id'],
        'D' => ['id'],
        'L' => ['id', 'ordr'],
        'C' => ['id', 'parent_leaf', 'ordr', 'name'],
    ];
    
    /**
     * 
     * @param string $typeId - enum('O', 'L', 'C', 'V') (тип таблицы БД)
     * @param string $fieldName - тестируемое имя таблицы, которое пытается
     *   создать пользователь
     * @return '' or error string if this field name is reserved
     */
    public function testIfReservedField(string $typeId, string $fieldName) {
        if (in_array($fieldName, $this->definedFields[$typeId])) {
            return __('Field name') . " '" . $fieldName . "' " . __('is reserved');
        } else {
            return '';
        }
    }
    
    /**
     * Вернуть все типы полей, на которые может ссылаться поле типа select
     * @return array - массив типов полей
     */
    public static function getTypesForSelectFld() {
        return [1, 2, 3, 6, 9]; // bool, int, float, str, datetime
    }
}