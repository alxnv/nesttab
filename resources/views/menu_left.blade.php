<ul class="left_menu">
    <li>
    <a href="<?=asset('/nesttab/change-struct-list')?>"><?=__('Tables')?></a><br />
    </li>
    <li>
    <a href="<?=asset('/nesttab/struct-add-table')?>"><?=__('Add table')?></a><br />
    </li>
<?php
global $yy;
if ($yy->settings2['are_tests_accessible']) {
    echo '<br /><li><a href="' . asset('/nesttab/tests') . '">Тесты</a><br /><br /></li>';
}
?>
</ul>
