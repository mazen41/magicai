<?php

$enJsonPath = __DIR__ . '/lang/en.json';
$arJsonPath = __DIR__ . '/resources/lang/ar.json';
$langArJsonPath = __DIR__ . '/lang/ar.json';

// Read English translations
$enTranslations = json_decode(file_get_contents($enJsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Invalid JSON in en.json: " . json_last_error_msg() . "\n");
}

// Read Arabic translations (resources/lang/ar.json)
$arTranslations = json_decode(file_get_contents($arJsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Invalid JSON in resources/lang/ar.json: " . json_last_error_msg() . "\n");
}

// Read Arabic translations (lang/ar.json)
$langArTranslations = json_decode(file_get_contents($langArJsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Invalid JSON in lang/ar.json: " . json_last_error_msg() . "\n");
}

// Validation results
$validation = [
    'english_keys' => count($enTranslations),
    'arabic_keys' => count($arTranslations),
    'lang_ar_keys' => count($langArTranslations),
    'missing_arabic_keys' => [],
    'extra_arabic_keys' => [],
    'null_values' => [],
    'placeholder_mismatches' => [],
    'html_tag_mismatches' => [],
    'json_valid' => true,
];

echo "=== Arabic Translation Validation Report ===\n\n";

// Check key counts
echo "English keys: " . $validation['english_keys'] . "\n";
echo "Arabic keys (resources/lang/ar.json): " . $validation['arabic_keys'] . "\n";
echo "Arabic keys (lang/ar.json): " . $validation['lang_ar_keys'] . "\n\n";

// Check for missing keys
$enKeys = array_keys($enTranslations);
$arKeys = array_keys($arTranslations);

$missingKeys = array_diff($enKeys, $arKeys);
$extraKeys = array_diff($arKeys, $enKeys);

if (!empty($missingKeys)) {
    echo "⚠️  Missing Arabic keys (" . count($missingKeys) . "):\n";
    foreach (array_slice($missingKeys, 0, 10) as $key) {
        echo "  - $key\n";
    }
    if (count($missingKeys) > 10) {
        echo "  ... and " . (count($missingKeys) - 10) . " more\n";
    }
    $validation['missing_arabic_keys'] = $missingKeys;
} else {
    echo "✓ No missing Arabic keys\n";
}

if (!empty($extraKeys)) {
    echo "⚠️  Extra Arabic keys (" . count($extraKeys) . "):\n";
    foreach (array_slice($extraKeys, 0, 10) as $key) {
        echo "  - $key\n";
    }
    if (count($extraKeys) > 10) {
        echo "  ... and " . (count($extraKeys) - 10) . " more\n";
    }
    $validation['extra_arabic_keys'] = $extraKeys;
} else {
    echo "✓ No extra Arabic keys\n";
}

// Check for null values
$nullCount = 0;
foreach ($arTranslations as $key => $value) {
    if ($value === null) {
        $nullCount++;
        $validation['null_values'][] = $key;
    }
}

echo "\nNull Arabic translations: $nullCount\n";
if ($nullCount > 0) {
    echo "⚠️  Found $nullCount null values\n";
    foreach (array_slice($validation['null_values'], 0, 10) as $key) {
        echo "  - $key\n";
    }
} else {
    echo "✓ No null values\n";
}

// Check for placeholder preservation
$placeholderMismatches = 0;
$placeholderPatterns = [
    '/:([a-z_]+)/i',  // Laravel placeholders like :name
    '/\{([a-z_]+)\}/i',  // Blade placeholders like {name}
    '/\{\{([a-z_]+)\}\}/i',  // Blade echo like {{name}}
    '/%([a-z_]+)%/i',  // sprintf placeholders like %s
];

foreach ($enTranslations as $key => $enValue) {
    if (!isset($arTranslations[$key])) continue;
    $arValue = $arTranslations[$key];
    
    if (!is_string($enValue) || !is_string($arValue)) continue;
    
    foreach ($placeholderPatterns as $pattern) {
        preg_match_all($pattern, $enValue, $enMatches);
        preg_match_all($pattern, $arValue, $arMatches);
        
        if ($enMatches[0] !== $arMatches[0]) {
            $placeholderMismatches++;
            $validation['placeholder_mismatches'][] = [
                'key' => $key,
                'english' => $enMatches[0],
                'arabic' => $arMatches[0],
            ];
            break;
        }
    }
}

echo "\nPlaceholder mismatches: $placeholderMismatches\n";
if ($placeholderMismatches > 0) {
    echo "⚠️  Found $placeholderMismatches placeholder mismatches\n";
    foreach (array_slice($validation['placeholder_mismatches'], 0, 5) as $mismatch) {
        echo "  - {$mismatch['key']}: " . implode(', ', $mismatch['english']) . " vs " . implode(', ', $mismatch['arabic']) . "\n";
    }
} else {
    echo "✓ All placeholders preserved\n";
}

// Check for HTML tag preservation
$htmlTagMismatches = 0;
$htmlTagPattern = '/<([a-z][a-z0-9]*)[^>]*>/i';

foreach ($enTranslations as $key => $enValue) {
    if (!isset($arTranslations[$key])) continue;
    $arValue = $arTranslations[$key];
    
    if (!is_string($enValue) || !is_string($arValue)) continue;
    
    preg_match_all($htmlTagPattern, $enValue, $enMatches);
    preg_match_all($htmlTagPattern, $arValue, $arMatches);
    
    if ($enMatches[0] !== $arMatches[0]) {
        $htmlTagMismatches++;
        $validation['html_tag_mismatches'][] = [
            'key' => $key,
            'english' => $enMatches[0],
            'arabic' => $arMatches[0],
        ];
    }
}

echo "\nHTML tag mismatches: $htmlTagMismatches\n";
if ($htmlTagMismatches > 0) {
    echo "⚠️  Found $htmlTagMismatches HTML tag mismatches\n";
    foreach (array_slice($validation['html_tag_mismatches'], 0, 5) as $mismatch) {
        echo "  - {$mismatch['key']}: " . implode(', ', $mismatch['english']) . " vs " . implode(', ', $mismatch['arabic']) . "\n";
    }
} else {
    echo "✓ All HTML tags preserved\n";
}

// Final summary
echo "\n=== Summary ===\n";
echo "English keys: {$validation['english_keys']}\n";
echo "Arabic keys: {$validation['arabic_keys']}\n";
echo "Missing Arabic translations: " . count($validation['missing_arabic_keys']) . "\n";
echo "Null Arabic translations: $nullCount\n";
echo "Placeholder mismatches: $placeholderMismatches\n";
echo "HTML/tag mismatches: $htmlTagMismatches\n";
echo "JSON valid: YES\n";

// Check if both Arabic files are identical
$filesIdentical = json_encode($arTranslations) === json_encode($langArTranslations);
echo "\nresources/lang/ar.json and lang/ar.json are " . ($filesIdentical ? "IDENTICAL" : "DIFFERENT") . "\n";

// Save validation report
file_put_contents(__DIR__ . '/arabic_validation_report.json', json_encode($validation, JSON_PRETTY_PRINT));
echo "\nValidation report saved to: arabic_validation_report.json\n";
