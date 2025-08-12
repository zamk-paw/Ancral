<?php
namespace App\Services;

final class ImageOptimizer
{
    /** Exécute l'optimisation GD. Retourne infos pour mise à jour DB. */
    public static function run(string $path, string $mime, int $jpegQ=82, int $pngLevel=6, int $webpQ=82): array
    {
        if (!is_file($path) || !is_writable($path)) {
            throw new \RuntimeException('Fichier introuvable ou non modifiable.');
        }
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
            return ['changed'=>false, 'old'=>filesize($path) ?: 0, 'size'=>filesize($path) ?: 0, 'sha256'=>null];
        }

        self::ensureMemoryForImage($path);

        $tmp = $path . '.tmp-' . bin2hex(random_bytes(4));
        $ok  = false;

        // Convertir warnings en exceptions pour capter les erreurs GD
        set_error_handler(function($severity, $message){
            throw new \ErrorException($message, 0, $severity);
        });

        try {
            if ($mime === 'image/jpeg') {
                if (!function_exists('imagecreatefromjpeg')) throw new \RuntimeException('GD JPEG indisponible');
                $im = @imagecreatefromjpeg($path);
                if ($im) { $ok = imagejpeg($im, $tmp, $jpegQ); imagedestroy($im); }
            } elseif ($mime === 'image/png') {
                if (!function_exists('imagecreatefrompng')) throw new \RuntimeException('GD PNG indisponible');
                $im = @imagecreatefrompng($path);
                if ($im) { imagesavealpha($im, true); $ok = imagepng($im, $tmp, $pngLevel); imagedestroy($im); }
            } elseif ($mime === 'image/webp') {
                if (!function_exists('imagecreatefromwebp') || !function_exists('imagewebp'))
                    throw new \RuntimeException('GD WebP indisponible');
                $im = @imagecreatefromwebp($path);
                if ($im) { $ok = imagewebp($im, $tmp, $webpQ); imagedestroy($im); }
            }

            if (!$ok || !is_file($tmp)) { @unlink($tmp); return ['changed'=>false, 'old'=>filesize($path) ?: 0, 'size'=>filesize($path) ?: 0, 'sha256'=>null]; }

            $old = filesize($path) ?: 0;
            $new = filesize($tmp) ?: \PHP_INT_MAX;

            if ($new < $old) {
                if (!@rename($tmp, $path)) { @copy($tmp, $path); @unlink($tmp); }
                @chmod($path, 0664);
                $sha = hash_file('sha256', $path);
                return ['changed'=>true, 'old'=>$old, 'size'=>filesize($path) ?: $old, 'sha256'=>$sha];
            } else {
                @unlink($tmp);
                return ['changed'=>false, 'old'=>$old, 'size'=>$old, 'sha256'=>null];
            }
        } finally {
            restore_error_handler();
        }
    }

    /** Monte le memory_limit si nécessaire d'après la taille/bit-depth de l'image. */
    private static function ensureMemoryForImage(string $file): void
    {
        $info = @getimagesize($file);
        if (!$info) return;
        $w = $info[0] ?? 0; $h = $info[1] ?? 0;
        $bits = $info['bits'] ?? 8;
        $channels = $info['channels'] ?? 4; // worst-case
        // estimation large + 10 Mo de marge
        $need = (int)ceil($w * $h * ($bits/8) * max(3, $channels) * 1.8 + (10 * 1024 * 1024));
        $cur = self::iniBytes(ini_get('memory_limit') ?: '128M');
        if ($cur > 0) {
            $used = memory_get_usage(true);
            $remain = $cur - $used;
            if ($remain < $need) {
                $target = min((int)ceil(($used + $need) * 1.5), 1024*1024*1024); // cap à 1 Go
                $mb = (int)ceil($target / (1024*1024));
                @ini_set('memory_limit', $mb.'M');
            }
        }
    }

    private static function iniBytes(string $val): int
    {
        $val = trim($val);
        if ($val === '' || $val === '-1') return 0;
        $unit = strtolower(substr($val, -1));
        $n = (int)$val;
        return match($unit) {
            'g' => $n * 1024 * 1024 * 1024,
            'm' => $n * 1024 * 1024,
            'k' => $n * 1024,
            default => (int)$val
        };
    }
}
