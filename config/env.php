<?php
/**
 * Trình đọc file .env đơn giản (không cần composer/thư viện ngoài).
 * Đọc các dòng dạng KEY=VALUE từ file .env ở thư mục gốc project
 * và nạp vào getenv()/putenv() để config/*.php sử dụng.
 *
 * File .env KHÔNG được đẩy lên Git (đã có trong .gitignore).
 * Dùng .env.example làm mẫu để biết cần khai báo biến gì.
 */

function loadEnv(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        // Bỏ dấu nháy nếu có: KEY="value" hoặc KEY='value'
        $value = trim($value, "\"'");

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');
