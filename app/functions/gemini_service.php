<?php
/**
 * Gemini Service
 * 
 * This file contains functions for interacting with the Gemini API.
 */

require_once __DIR__ . '/../config/gemini_config.php';

/**
 * Check if Gemini API is enabled
 * 
 * @return bool True if Gemini is enabled, false otherwise
 */
function is_gemini_enabled() {
    return GEMINI_ENABLED;
}

/**
 * Generate content using Gemini API
 * 
 * @param string $prompt The prompt to send to Gemini
 * @param array $params Optional parameters to override defaults
 * @return array|null Response from Gemini API or null if error
 */
function gemini_generate_content($prompt, $params = []) {
    global $GEMINI_DEFAULT_PARAMS;
    
    if (!is_gemini_enabled()) {
        return ['error' => 'Gemini API is not enabled'];
    }
    
    // Merge default parameters with user-provided ones
    $requestParams = array_merge($GEMINI_DEFAULT_PARAMS, $params);
    
    // Prepare request data
    $data = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => $requestParams['temperature'],
            'topK' => $requestParams['topK'],
            'topP' => $requestParams['topP'],
            'maxOutputTokens' => $requestParams['maxOutputTokens'],
        ]
    ];
    
    // API URL with API key
    $url = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;
    
    // Set up cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log API usage if enabled
    if (GEMINI_LOG_USAGE) {
        log_gemini_usage($prompt, $statusCode);
    }
    
    if ($statusCode !== 200) {
        return [
            'error' => 'API Error',
            'status_code' => $statusCode,
            'response' => $response
        ];
    }
    
    return json_decode($response, true);
}

/**
 * Extract text content from Gemini API response
 * 
 * @param array $response The API response from gemini_generate_content()
 * @return string|null The generated text or null if error
 */
function gemini_extract_content($response) {
    if (isset($response['error'])) {
        return null;
    }
    
    if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }
    
    return null;
}

/**
 * Log Gemini API usage to file
 * 
 * @param string $prompt The prompt sent to the API
 * @param int $statusCode HTTP status code from the API response
 */
function log_gemini_usage($prompt, $statusCode) {
    if (!file_exists(dirname(GEMINI_LOG_PATH))) {
        mkdir(dirname(GEMINI_LOG_PATH), 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . " | Status: $statusCode | Prompt: " . substr($prompt, 0, 100) . "...\n";
    file_put_contents(GEMINI_LOG_PATH, $logEntry, FILE_APPEND);
}