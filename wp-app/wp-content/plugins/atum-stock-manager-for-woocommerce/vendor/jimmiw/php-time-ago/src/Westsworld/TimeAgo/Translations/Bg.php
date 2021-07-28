<?php

namespace Westsworld\TimeAgo\Translations;

use \Westsworld\TimeAgo\Language;

/**
 * Bulgarian translations
 */
class Bg extends Language
{
    public function __construct()
    {
        $this->setTranslations([
            'aboutOneDay' => "преди един ден",
            'aboutOneHour' => "преди около час",
            'aboutOneMonth' => "преди около месец",
            'aboutOneYear' => "преди около една година",
            'days' => "преди %s дни",
            'hours' => "преди %s часа",
            'lessThanAMinute' => "преди по - малко от минута",
            'lessThanOneHour' => "преди %s минути",
            'months' => "преди %s месеца",
            'oneMinute' => "преди минута",
            'years' => "преди повече от %s години",
            'never' => 'никога'
        ]);
    }
}
