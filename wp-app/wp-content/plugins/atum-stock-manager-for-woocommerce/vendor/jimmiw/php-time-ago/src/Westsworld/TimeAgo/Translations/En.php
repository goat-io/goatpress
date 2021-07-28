<?php

namespace Westsworld\TimeAgo\Translations;

use \Westsworld\TimeAgo\Language;

/**
 * English translations
 */
class En extends Language
{
    public function __construct()
    {
        $this->setTranslations([
            'aboutOneDay' => "1 day ago",
            'aboutOneHour' => "about 1 hour ago",
            'aboutOneMonth' => "about 1 month ago",
            'aboutOneYear' => "about 1 year ago",
            'days' => "%s days ago",
            'hours' => "%s hours ago",
            'lessThanAMinute' => "less than a minute ago",
            'lessThanOneHour' => "%s minutes ago",
            'months' => "%s months ago",
            'oneMinute' => "1 minute ago",
            'years' => "over %s years ago",
            'never' => 'Never'
        ]);
    }
}
