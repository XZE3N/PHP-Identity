<?php
class Normalizer {
    public static function normalize($str) {
        $normalized = strtoupper($str);
        
        // Remove any illegal characters (allow only A-Z, 0-9, @, _, - and .)
        $normalized = preg_replace('/[^A-Z0-9@._-]/', '', $normalized);
        
        return $normalized;
    }

    public static function trimString($str) {
        // Remove whitespace (spaces, tabs, newlines, etc.)
        $str = preg_replace('/\s+/', '', $str);
        
        return $str;
    }

    public static function sanitizeString($str) {
        $str = preg_replace('/[^A-Z0-9@._-]/', '', $str);
        
        return $str;
    }
}
?>
