<ul class="left_menu">
    <li>
    <a href="<?=asset('/' . config('nesttab.nurl') . '/change-struct-list')?>"><?=__('Tables')?></a><br />
    </li>
    <li>
    <a href="<?=asset('/' . config('nesttab.nurl') . '/struct-add-table')?>"><?=__('Add table')?></a><br />
    </li>
<?php
global $yy;
if ($yy->settings2['are_tests_accessible']) {
    echo '<br /><li><a href="' . asset('/' . config('nesttab.nurl') . '/tests') . '">Тесты</a><br /><br /></li>';
}
?>
</ul>
