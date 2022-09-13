<?php

namespace extend\lib;

class OFMath
{
    const SCALE = 2;

    public static function add($left, $right, $scale = self::SCALE)
    {
        return (string)bcadd($left, $right, $scale);
    }

    public static function sub($left, $right, $scale = self::SCALE)
    {
        return (string)bcsub($left, $right, $scale);
    }

    public static function div($left, $right, $scale = self::SCALE)
    {
        return (string)bcdiv($left, $right, $scale);
    }

    public static function mul($left, $right, $scale = self::SCALE)
    {
        return (string)bcmul($left, $right, $scale);
    }

}
