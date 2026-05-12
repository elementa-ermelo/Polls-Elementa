<?php

namespace App\Support;

class PollType
{
    public const YES_NO = 'yes_no';
    public const CLOSED = 'closed';
    public const OPEN_TEXT = 'open_text';
    public const AGREE_DISAGREE = 'agree_disagree';
    public const RATING_STARS = 'rating_stars';
    public const LIKERT_5 = 'likert_5';
    public const SCORE_1_10 = 'score_1_10';
    public const EVENT_DATES = 'event_dates';
    public const RANGE_BUCKETS = 'range_buckets';

    public static function labels(): array
    {
        return [
            self::YES_NO => 'Ja/Nee poll',
            self::CLOSED => 'Ja/Nee poll (variant)',
            self::OPEN_TEXT => 'Open poll (vrij antwoord)',
            self::AGREE_DISAGREE => 'Eens / oneens',
            self::RATING_STARS => 'Rating met sterren',
            self::LIKERT_5 => 'Helemaal mee eens t/m helemaal oneens',
            self::SCORE_1_10 => 'Cijfers 1-10',
            self::EVENT_DATES => 'Event poll met data',
            self::RANGE_BUCKETS => 'Bereik-categorieen (bijv. 1-5 jaar)',
        ];
    }

    public static function defaults(string $type): array
    {
        return match ($type) {
            self::YES_NO => ['Ja', 'Nee'],
            self::CLOSED => ['Ja', 'Nee'],
            self::OPEN_TEXT => [],
            self::AGREE_DISAGREE => ['Eens', 'Oneens'],
            self::RATING_STARS => ['1 ster', '2 sterren', '3 sterren', '4 sterren', '5 sterren'],
            self::LIKERT_5 => ['Helemaal mee eens', 'Eens', 'Neutraal', 'Oneens', 'Helemaal oneens'],
            self::SCORE_1_10 => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            self::EVENT_DATES => ['10-04-2026', '11-04-2026', '12-04-2026'],
            self::RANGE_BUCKETS => ['1-5 jaar', '5-10 jaar', '10+ jaar'],
            default => [],
        };
    }

    public static function needsDateParsing(string $type): bool
    {
        return $type === self::EVENT_DATES;
    }

    public static function isOpenTextType(string $type): bool
    {
        return $type === self::OPEN_TEXT;
    }

    public static function defaultsByType(): array
    {
        $result = [];

        foreach (array_keys(self::labels()) as $type) {
            $result[$type] = self::defaults($type);
        }

        return $result;
    }
}
