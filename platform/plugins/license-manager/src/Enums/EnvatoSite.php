<?php

namespace Botble\LicenseManager\Enums;

enum EnvatoSite: string
{
    case CodeCanyon = 'codecanyon.net';

    case ThemeForest = 'themeforest.net';

    case VideoHive = 'videohive.net';

    case AudioJungle = 'audiojungle.net';

    case GraphicRiver = 'graphicriver.net';

    case PhotoDune = 'photodune.net';

    case ThreeDOcean = '3docean.net';

    public static function tryFromName(string $name): ?self
    {
        return match ($name) {
            'CodeCanyon' => self::CodeCanyon,
            'ThemeForest' => self::ThemeForest,
            'VideoHive' => self::VideoHive,
            'AudioJungle' => self::AudioJungle,
            'GraphicRiver' => self::GraphicRiver,
            'PhotoDune' => self::PhotoDune,
            'ThreeDOcean' => self::ThreeDOcean,
            default => null
        };
    }
}
