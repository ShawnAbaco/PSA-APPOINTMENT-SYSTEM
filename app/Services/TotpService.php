<?php
namespace App\Services;

class TotpService
{
    // Base32 alphabet
    private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret($length = 16)
    {
        $bytes = random_bytes($length);
        return self::base32Encode($bytes);
    }

    public static function base32Encode($data)
    {
        $alphabet = self::$base32Chars;
        $bits = 0;
        $value = 0;
        $output = '';
        foreach (str_split($data) as $char) {
            $value = ($value << 8) | ord($char);
            $bits += 8;
            while ($bits >= 5) {
                $output .= $alphabet[($value >> ($bits - 5)) & 31];
                $bits -= 5;
            }
        }
        if ($bits > 0) {
            $output .= $alphabet[($value << (5 - $bits)) & 31];
        }
        return $output;
    }

    public static function base32Decode($b32)
    {
        $alphabet = self::$base32Chars;
        $b32 = strtoupper($b32);
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($b32) as $char) {
            $val = strpos($alphabet, $char);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }

    public static function getCounter($time = null, $period = 30)
    {
        $time = $time ?? time();
        return floor($time / $period);
    }

    public static function hotp($secret, $counter, $digits = 6)
    {
        $key = self::base32Decode($secret);
        $counterBytes = pack('J', $counter);
        if (strlen($counterBytes) !== 8) {
            // pack on 64-bit machines fallback
            $counterBytes = pack('NN', ($counter & 0xFFFFFFFF00000000) >> 32, $counter & 0xFFFFFFFF);
        }
        $hash = hash_hmac('sha1', $counterBytes, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = (ord($hash[$offset]) & 0x7f) << 24 |
                  (ord($hash[$offset + 1]) & 0xff) << 16 |
                  (ord($hash[$offset + 2]) & 0xff) << 8 |
                  (ord($hash[$offset + 3]) & 0xff);
        $otp = $binary % pow(10, $digits);
        return str_pad($otp, $digits, '0', STR_PAD_LEFT);
    }

    public static function totp($secret, $time = null, $period = 30, $digits = 6)
    {
        $counter = self::getCounter($time, $period);
        return self::hotp($secret, $counter, $digits);
    }

    public static function verify($secret, $code, $window = 1, $period = 30)
    {
        $time = time();
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::totp($secret, $time + ($i * $period), $period), str_pad($code, 6, '0', STR_PAD_LEFT))) {
                return true;
            }
        }
        return false;
    }

    public static function getQrCodeUrl($label, $secret, $issuer = 'PSA')
    {
        $otpauth = sprintf('otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30', rawurlencode($issuer), rawurlencode($label), $secret, rawurlencode($issuer));
        $chart = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . rawurlencode($otpauth);
        return $chart;
    }

    /**
     * Generate recovery codes array.
     * Returns plain recovery codes for display (should be shown once).
     */
    public static function generateRecoveryCodes($count = 8)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    public static function getCurrentCode($secret)
    {
        return self::totp($secret);
    }
}
