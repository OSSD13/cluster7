<?php
header('Content-Type: application/json');

// This is a simple test endpoint
echo json_encode([
    'status' => 'success',
    'message' => 'API endpoint is working',
    'endpoints' => [
        '/api/minor-cases',
        '/api/boards'
    ]
]); 