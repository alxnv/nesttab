<?php


/**
 * Helper-функции для даты и времени
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class DateTimeHelper {

/*
$t = time();
echo 'Current: ' . \yy::ds($t) . '<br />';
echo 'Span 10 min: ' . \yy::ds(\Alxnv\Nesttab\core\DateTimeHelper::dateSpan($t, 10 * 60)) . '<br />';
$t2 = $t - 60;
echo 'Current - 1 min: ' . \yy::ds($t2) . '<br />';
echo 'Span it 10 min: ' . \yy::ds(\Alxnv\Nesttab\core\DateTimeHelper::dateSpan($t2, 10 * 60)) . '<br />';
$t3 = $t - 10 * 60;
echo 'Current - 10 min: ' . \yy::ds($t3) . '<br />';
echo 'Span it 10 min: ' . \yy::ds(\Alxnv\Nesttab\core\DateTimeHelper::dateSpan($t3, 10 * 60)) . '<br />';
*/
    
    /**
     * Дата, выровненная на определенное количество секунд
     *   (то есть, для близкого диапазона дат выдает одно и то же значепие)
     * @param int $date - Unix datetime in seconds
     * @param int $span - выравнивание для даты
     * @return Int - выровненная дата
     */
    public static function dateSpan(int $date, int $span) {
        return floor($date / $span) * $span;
    }
    
}
