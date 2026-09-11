<?php

require __DIR__ . '/vendor/autoload.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

$arJsonPath = __DIR__ . '/resources/lang/ar.json';

// Read current Arabic translations (currently in English)
$arTranslations = json_decode(file_get_contents($arJsonPath), true);

// Initialize translator
$translator = new GoogleTranslate('ar');

$translatedCount = 0;
$failedCount = 0;

echo "Starting translation of " . count($arTranslations) . " keys...\n";

foreach ($arTranslations as $key => $value) {
    if (is_string($value) && !empty($value)) {
        try {
            // Translate to Arabic
            $translated = $translator->translate($value);
            $arTranslations[$key] = $translated;
            $translatedCount++;
            
            // Progress indicator
            if ($translatedCount % 50 == 0) {
                echo "Translated: $translatedCount keys...\n";
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        } catch (Exception $e) {
            echo "Failed to translate: $key - " . $e->getMessage() . "\n";
            $failedCount++;
        }
    }
}

// Write back to Arabic file
file_put_contents($arJsonPath, json_encode($arTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\nTranslation complete!\n";
echo "Successfully translated: $translatedCount keys\n";
echo "Failed: $failedCount keys\n";
