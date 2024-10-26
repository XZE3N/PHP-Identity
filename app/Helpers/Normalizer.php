<?php
class Normalizer {
    public static function normalize($str) {
        $normalized = strtoupper($str);
        
        // Remove any illegal characters (allow only A-Z, 0-9, @, _, - and .)
        $normalized = preg_replace('/[^A-Z0-9@._-]/', '', $normalized);
        
        return $normalized;
    }
}
?>
