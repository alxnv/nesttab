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
        'V' => ['id'],
        'L' => ['id', 'ordr', 'name'],
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
}