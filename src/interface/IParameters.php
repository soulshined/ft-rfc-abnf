<?php
namespace FT\RFC_ABNF\Interface;

use FT\RFC_ABNF\ABNF;

interface IParameters {
    public static function PARAMETERS(): ABNF;
    public static function PARAMETER() : ABNF;
    public static function PARAMETER_NAME(): ABNF;
    public static function PARAMETER_VALUE(): ABNF;
}
?>