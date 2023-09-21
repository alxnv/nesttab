<?php
# настройки, встроенные в систему. НЕ МЕНЯЙТЕ ИХ!

//var_dump(\yy::basePath());exit;
global $yy;

return [  
        'recs_per_page' => 10, // number of records per page (for list type tables)
        'are_tests_accessible' => true, // отображается гиперссылка на страницу тестов
	'table_types' => ['One record', 'List', 'Ordinary table', 'Catalogue'],
	'table_names' => ['one', 'list', 'ord', 'tree'],
        'table_names_short' => ['O', 'L', 'D', 'C'],
        'aliases' => ['app' => $yy->Engine_Path,
            'core' => $yy->Engine_Path . '/core'], # алиасы префиксов путей к файлам (классам)
	
        'col_categories' => [ 1 => __('Basic types of fields'),
            2 => __('Additional types of fields'),
            ],
        'max_txt_size' => 500000, // max size in bytes of txt field
        'max_html_size' => 500000, // max size in bytes of html field
        'time_to_lock_add_field' => 10, // time to lock process of adding or editing
              // field, in seconds
    ];
?>