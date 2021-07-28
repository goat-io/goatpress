<?php

namespace Westsworld\TimeAgo\Translations;

use \Westsworld\TimeAgo\Language;

/**
 * Vietnamese translations
 */
class Vi extends Language
{
    public function __construct()
    {
        $this->setTranslations([
            'aboutOneDay' => "1 ngày trước",
            'aboutOneHour' => "1 giờ trước",
            'aboutOneMonth' => "1 tháng trước",
            'aboutOneYear' => "1 năm trước",
            'days' => "%s ngày trước",
            'hours' => "%s giờ trước",
            'lessThanAMinute' => "Gần 1 phút trước",
            'lessThanOneHour' => "%s phút trước",
            'months' => "%s tháng trước",
            'oneMinute' => "1 phút trước",
            'years' => "%s năm trước",
            'never' => 'Không xác định'
        ]);
    }
}
