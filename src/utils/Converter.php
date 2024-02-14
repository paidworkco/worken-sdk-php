<?php
namespace Worken\Utils;

class Converter {
    public static function convertWEItoEther($wei) {
        return bcdiv($wei, bcpow('10', '18'), 18);
    }
}