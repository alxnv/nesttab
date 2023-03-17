<p>
    <a href="<?=asset('/nesttab/struct-add-table')?>"><?=__('Add table')?></a><br />
    <a href="<?=asset('/nesttab/change-struct-list')?>"><?=__('All upper level tables list')?></a><br />
<?php
global $yy;
if ($yy->settings2['are_tests_accessible']) {
    echo '<br /><a href="' . asset('/nesttab/tests') . '">Тесты</a><br /><br />';
}
?>

</p>
