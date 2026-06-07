<?php
/**
 * Language System - Supports Persian (FA) and English (EN)
 */

class Language {
    private static ?array $translations = null;
    private static string $currentLang = 'fa';

    public static function init(string $lang = 'fa'): void {
        self::$currentLang = in_array($lang, ['fa', 'en']) ? $lang : 'fa';

        $langFile = __DIR__ . '/../lang/' . self::$currentLang . '.php';
        if (file_exists($langFile)) {
            self::$translations = include $langFile;
        } else {
            $fallbackFile = __DIR__ . '/../lang/en.php';
            self::$translations = file_exists($fallbackFile) ? include $fallbackFile : [];
        }
    }

    public static function get(string $key, array $replace = []): string {
        if (self::$translations === null) {
            self::init();
        }

        $text = self::$translations[$key] ?? $key;

        if (!empty($replace)) {
            foreach ($replace as $search => $replaceWith) {
                $text = str_replace('{' . $search . '}', $replaceWith, $text);
            }
        }

        return $text;
    }

    public static function setLanguage(string $lang): void {
        if (in_array($lang, ['fa', 'en'])) {
            self::$currentLang = $lang;
            self::init($lang);

            if (isset($_SESSION)) {
                $_SESSION['language'] = $lang;
            }
        }
    }

    public static function getCurrentLanguage(): string {
        return self::$currentLang;
    }

    public static function isRTL(): bool {
        return self::$currentLang === 'fa';
    }

    public static function getDirection(): string {
        return self::isRTL() ? 'rtl' : 'ltr';
    }

    public static function getAllLanguages(): array {
        return [
            'fa' => 'فارسی',
            'en' => 'English',
        ];
    }

    public static function t(string $key, array $replace = []): string {
        return self::get($key, $replace);
    }
}

/**
 * Helper function for easy translation
 */
function __(string $key, array $replace = []): string {
    return Language::get($key, $replace);
}
