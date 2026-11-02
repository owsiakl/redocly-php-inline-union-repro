<?php

// Echoes the request body back as a 201 response.
http_response_code(201);
header('Content-Type: application/json');
echo file_get_contents('php://input');
