<?php
/* 
 * Основные поля для отображения в режиме редактирования структуры полей
 */
if (!isset($r['name'])) $r['name'] = '';
if (!isset($r['descr'])) $r['descr'] = '';
$r['name'] = mb_substr($r['name'], 0, 200);
$r['descr'] = mb_substr($r['descr'], 0, 200);

if (isset($r['id'])) echo '<input type="hidden" name="id" value="' . intval($r['id']) . '" />';
?>
<input type="hidden" name="field_type_id" value="<?=intval($r['field_type_id'])?>" />
<?=$e->getErr('descr')?>
<?=__('Description')?> : <input type="text" name="descr" size="40" value="<?=\yy::qs($r['descr'])?>" /><br/>
