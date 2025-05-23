<?php
// Gemini AI Configuration
define('GEMINI_ENABLED', false); // Set to true when you want to enable Gemini features
define('GEMINI_API_KEY', ''); // Your Gemini API key
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent');
define('GEMINI_LOG_USAGE', false); // Whether to log API usage
define('GEMINI_LOG_PATH', __DIR__ . '/../logs/gemini_usage.log');

// Default parameters for Gemini API requests
$GEMINI_DEFAULT_PARAMS = [
    'temperature' => 0.7,
    'topK' => 40,
    'topP' => 0.95,
    'maxOutputTokens' => 1024,
];
?>