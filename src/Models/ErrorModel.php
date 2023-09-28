<?php


namespace Alxnv\Nesttab\Models;

/**
 * Description of ErrorModel
 *
 * @author Alexandr
 */
class ErrorModel {
    
    public $err = [];
    /**
     * Устанавливаем значения массива $this->err занося туда сообщения об ошибках,
     *   соответствующие полям редактирования
     * @param string $field 
     * @param string $errorString
     */
    public function setErr($field, $errorString) {
        if (isset($this->err[$field])) {
            $this->err[$field] .= chr(13) . $errorString;
        } else {
            $this->err[$field] = $errorString;
        }
    }
    
    /**
     * получить все ошибки в виде одной строки
     * @return string
     */
    public function getAll() {
        $s = '';
        if (isset($this->err[''])) {
            $s .= ($this->err[''] . chr(13) . chr(10));
        }
        foreach ($this->err as $key => $value) {
            $s .= '[' . $key . ']' . chr(13) . chr(10);
            $s .= $value . chr(13) . chr(10);
        }
        return $s;
    }
    
    /**
     * возвращает div с ошибкой если есть ошибка для данного поля
     * @param string $field_name - ипя поля, для которого выводится ошибка
     */
    public function getErr($field_name) {
        if (isset($this->err[$field_name])) {
            return '<p class="error">' . 
                    nl2br(\yy::qs($this->err[$field_name])) . '</p>' .
                    ($field_name == '' ? '<br />' : '');
        } else {
            return '';
        }
    }
    
    
    public function hasErr() {
        if (is_null($this->err)) return false;
        return (count($this->err) <> 0);
    }
    /**
     * Определяет, есть ли в $this->err хотя бы один из ключей из $arr
     * @param array $arr
     * @return boolean
     */
    public function hasOneOf(array $arr) {
        foreach ($arr as $value) {
            if (isset($this->err[$value])) return true;
        }
        return false;
    }
    
}
