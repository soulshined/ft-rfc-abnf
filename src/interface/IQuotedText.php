<?php
namespace FT\RFC_ABNF\Interface;

use FT\RFC_ABNF\ABNF;

interface IQuotedText {
    public static function OBS_TEXT(): ABNF;
    public static function QUOTED_STRING(): ABNF;
    public static function QDTEXT(): ABNF;
    public static function QUOTED_PAIR(): ABNF;
}
?>