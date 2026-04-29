<?php

return [
    'otp_length' => (int) env('CLIENT_PORTAL_OTP_LENGTH', 6),
    'otp_ttl_minutes' => (int) env('CLIENT_PORTAL_OTP_TTL', 10),
    'otp_max_attempts' => (int) env('CLIENT_PORTAL_OTP_MAX_ATTEMPTS', 5),
    'otp_request_limit' => (int) env('CLIENT_PORTAL_OTP_REQUEST_LIMIT', 5),
    'otp_request_decay_seconds' => (int) env('CLIENT_PORTAL_OTP_REQUEST_DECAY_SECONDS', 60),
    'token_ttl_days' => (int) env('CLIENT_PORTAL_TOKEN_TTL_DAYS', 30),
];
