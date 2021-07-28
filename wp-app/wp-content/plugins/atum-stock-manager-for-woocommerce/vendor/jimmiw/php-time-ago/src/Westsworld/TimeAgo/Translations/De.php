<?php

namespace Westsworld\TimeAgo\Translations;

use \Westsworld\TimeAgo\Language;

/**
 * German translations
 */
class De extends Language
{
    public function __construct()
    {
        $this->setTranslations([
            'aboutOneDay' => "vor einem Tag",
            'aboutOneHour' => "vor etwa einer Stunde",
            'aboutOneMonth' => "vor etwa einem Monat",
            'aboutOneYear' => "vor etwa einem Jahr",
            'days' => "vor %s Tagen",
            'hours' => "vor %s Stunden",
            'lessThanAMinute' => "vor weniger als einer Minute",
            'lessThanOneHour' => "vor %s Minuten",
            'months' => "vor %s Monaten",
            'oneMinute' => "vor einer Minute",
            'years' => "vor über %s Jahren"
        ]);
    }
}
