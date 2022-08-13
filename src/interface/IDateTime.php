<?php
namespace FT\RFC_ABNF\Interface;

use FT\RFC_ABNF\ABNF;

interface IDateTime {
    public static function DAY_NAME(): ABNF;
    public static function DAY(): ABNF;
    public static function MONTH(): ABNF;
    public static function YEAR(): ABNF;

    public static function TIME_OF_DAY(): ABNF;
    public static function HOUR(): ABNF;
    public static function MINUTE(): ABNF;
    public static function SECOND(): ABNF;

    public static function HTTP_DATE(): ABNF;
    public static function OBS_DATE(): ABNF;
}
?>