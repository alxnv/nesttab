<?php
# настройки, встроенные в систему. НЕ МЕНЯЙТЕ ИХ!

//var_dump(\yy::basePath());exit;
global $yy;

return [  
	'table_types' => ['One record', 'List', 'Vocabulary', 'Catalogue'],
	'table_names' => ['one', 'list', 'ord', 'cat'],
        'table_names_short' => ['O', 'L', 'V', 'C'],
        'aliases' => ['app' => $yy->Engine_Path,
            'core' => $yy->Engine_Path . '/core'], # алиасы префиксов путей к файлам (классам)
	
        'col_categories' => [ 1 => __('Basic types of fields'),
            2 => __('Additional types of fields'),
            ],

    ];
?>