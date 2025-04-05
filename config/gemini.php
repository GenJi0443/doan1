<?php
// Gemini API Configuration
define('GEMINI_API_KEY', 'AIzaSyAAM5IL42WWsX6JnEc9Q0SkzlAQzUU0UMk'); // Your Gemini API key
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// Context settings
define('MAX_CONTEXT_LENGTH', 10); // Maximum number of previous messages to maintain context
define('MAX_TOKENS', 1000); // Maximum tokens for response

// Temperature settings for different scenarios
define('GENERAL_TEMP', 0.7);  // For general conversation
define('SPECIFIC_TEMP', 0.3); // For specific tour/hotel information

// Debug mode
define('DEBUG_MODE', true); // Enable debug logging 