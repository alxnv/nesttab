<?php

/* 
 * Дополнительные поля для отображения в режиме редактирования структуры полей
 */
?>
<input id="req" type="checkbox" name="req" <?=isset($r['req']) ? 'checked="checked"' : ''?> />
<label for="req"> <?=\yy::t('Not empty')?></label>
