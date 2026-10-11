<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($amount): string {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }
}

if (!function_exists('formatPoin')) {
    function formatPoin(int $poin): string {
        return number_format($poin, 0, ',', '.') . ' poin';
    }
}

if (!function_exists('clean')) {
    function clean(string $val): string {
        return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('currentUser')) {
    function currentUser() {
        return auth()->user();
    }
}
