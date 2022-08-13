<?php
namespace FT\RFC_ABNF\Specs;

use FT\RFC_ABNF\ {
    Core,
    ABNF,

    Interface\IDateTime,
    Interface\IField,
    Interface\IParameters,
    Interface\IQuotedText,
    Interface\IUriReference,

    function Utils\unique_abnf_chrs,
    function Utils\build_chrs_for_unicode_range,
    function Utils\build_or_regexp
};

final class RFC9110 extends Core
    implements IField, IParameters, IDateTime, IQuotedText, IUriReference
{

    public static function TCHAR() : ABNF {
        return new ABNF("TCHAR", "'!' / '#' / '$' / '%' / '&' / ''' / '*' / '+' / '-' / '.' / '^' / '_' / '`' / '|' / '~' / DIGIT / ALPHA", "any VCHAR, except delimiters",
            unique_abnf_chrs(
                static::DIGIT(),
                static::ALPHA(),
                "!#$%&'*+-.^_`|~"
            ),
            '[\!\#\$\%\&\'\*\+\-\.\^\_\`\|\~0-9a-zA-Z]'
        );
    }

    public static function TOKEN() : ABNF {
        return new ABNF("TOKEN", "1*tchar", "one or more tchar",
            static::TCHAR()->chars,
            static::TCHAR()->regexp . '+'
        );
    }

    public static function OBS_TEXT(): ABNF
    {
        return new ABNF(
            "OBS-TEXT",
            "%x80-FF",
            "Obsolete text",
            build_chrs_for_unicode_range('\x80', '\xFF'),
            '[\x80-\xFF]'
        );
    }

    public static function QUOTED_STRING(): ABNF
    {
        return new ABNF(
            "QUOTED-STRING",
            "DQUOTE *(qdtext / quoted-pair) DQUOTE",
            "'\"' zero or more text '\"'",
            unique_abnf_chrs(
                static::DQUOTE(),
                static::QDTEXT(),
                static::QUOTED_PAIR()
            ),
            static::DQUOTE()->regexp . build_or_regexp(static::QDTEXT(), static::QUOTED_PAIR()) . "*" . static::DQUOTE()->regexp
        );
    }

    public static function QDTEXT(): ABNF
    {
        return new ABNF(
            "QDTEXT",
            "HTAB / SP / %x21 / %x23-5B / %x5D-7E / obs-text",
            '',
            unique_abnf_chrs(
                static::HTAB(),
                static::SP(),
                build_chrs_for_unicode_range('\x23', '\x5B'),
                build_chrs_for_unicode_range('\x5D', '\x7E'),
                static::OBS_TEXT(),
                '!'
            ),
            build_or_regexp(
                static::HTAB(),
                static::SP(),
                '\x21',
                '[\x23-\x5B]',
                '[\x5D-\x7E]',
                static::OBS_TEXT()
            )
        );
    }

    public static function QUOTED_PAIR(): ABNF
    {
        return new ABNF(
            "QUOTED-PAIR",
            "'\' ( HTAB / SP / VCHAR / obs-text )",
            '',
            unique_abnf_chrs(
                '\\',
                static::HTAB(),
                static::SP(),
                static::VCHAR(),
                static::OBS_TEXT()
            ),
            '\\' . build_or_regexp(
                static::HTAB(),
                static::SP(),
                static::VCHAR(),
                static::OBS_TEXT()
            )
        );
    }

    public static function COMMENT(): ABNF
    {
        return new ABNF(
            'COMMENT',
            "'(' *( ctext / quoted-pair / comment ) ')'",
            '',
            unique_abnf_chrs(
                '()',
                static::CTEXT(),
                static::QUOTED_PAIR()
            ),
            '\(' . build_or_regexp(static::CTEXT(), static::QUOTED_PAIR(), "?R") . '*\)'
        );
    }

    public static function CTEXT(): ABNF
    {
        return new ABNF(
            'CTEXT',
            'HTAB / SP / %x21-27 / %x2A-5B / %x5D-7E / obs-text',
            '',
            unique_abnf_chrs(
                static::HTAB(),
                static::SP(),
                build_chrs_for_unicode_range('\x21', '\x27'),
                build_chrs_for_unicode_range('\x2A', '\x5B'),
                build_chrs_for_unicode_range('\x5D', '\x7E'),
                static::OBS_TEXT()
            ),
            build_or_regexp(
                static::HTAB(),
                static::SP(),
                '[\x21-\x27]',
                '[\x2A-\x5B]',
                '[\x5D-\x7E]',
                static::OBS_TEXT()
            )
        );
    }

    public static function HTTP_DATE(): ABNF {
        return new ABNF("HTTP-DATE", "IMF-fixdate / obs-date", "GMT date time or obsolete date",
            unique_abnf_chrs(static::IMF_FIXDATE(), static::OBS_DATE()),
            build_or_regexp(static::IMF_FIXDATE(), static::OBS_DATE())
        );
    }

    public static function DAY_NAME(): ABNF
    {
        return new ABNF(
            "DAY-NAME",
            "%s'Mon' / %s 'Tue' / %s 'Wed' / %s 'Thu' / %s 'Fri' / %s 'Sat' / %s 'Sun'",
            'Case sensitive day name abbreviation',
            unique_abnf_chrs(
                ["Mon", 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            ),
            '(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun)'
        );
    }

    public static function DAY(): ABNF
    {
        return new ABNF("DAY", "2DIGIT", "Two numbers", static::DIGIT()->chars, static::DIGIT()->regexp . "{2}");
    }

    public static function MONTH(): ABNF
    {
        return new ABNF(
            "MONTH",
            "%s 'Jan' / %s 'Feb' / %s 'Mar' / %s 'Apr' / %s 'May' / %s 'Jun' / %s 'Jul' / %s 'Aug' / %s 'Sep' / %s 'Oct' / %s 'Nov' / %s 'Dec'",
            'Valid month abbreviation',
            unique_abnf_chrs(
                ["Jan", 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            ),
            '(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)'
        );
    }

    public static function YEAR(): ABNF
    {
        return new ABNF("YEAR", "4DIGIT", 'Fully qualified year', static::DIGIT()->chars, static::DIGIT()->regexp . "{4}");
    }

    public static function TIME_OF_DAY(): ABNF
    {
        return new ABNF('TIME-OF-DAY', "hour ':' minute ':' second", "00:00:00 - 23:59:60 (leap second)",
            unique_abnf_chrs(static::DIGIT(), ':'),
            '\d\d\:\d\d\:\d\d'
        );
    }

    public static function HOUR(): ABNF
    {
        return new ABNF("HOUR", "2DIGIT", "Two numbers", static::DIGIT()->chars, static::DIGIT()->regexp . "{2}");
    }

    public static function MINUTE(): ABNF
    {
        return new ABNF("MINUTE", "2DIGIT", "Two numbers", static::DIGIT()->chars, static::DIGIT()->regexp . "{2}");
    }

    public static function SECOND(): ABNF
    {
        return new ABNF("SECOND", "2DIGIT", "Two numbers", static::DIGIT()->chars, static::DIGIT()->regexp . "{2}");
    }

    public static function DATE(): ABNF
    {
        return static::HTTP_DATE()->createAlias('DATE');
    }

    public static function IMF_FIXDATE() : ABNF {
        return new ABNF("IMF-FIXDATE", "day-name ',' SP date1 SP time-of-day SP GMT", 'fixed length/zone/capitalization subset of the format',
            unique_abnf_chrs(
                static::DAY_NAME(),
                ',:GMT',
                static::SP(),
                static::DIGIT(),
                static::MONTH()
            ),
            static::DAY_NAME()->regexp . ','
            . static::SP()->regexp . static::DATE1()->regexp
            . static::SP()->regexp . static::TIME_OF_DAY()->regexp
            . static::SP()->regexp
            . 'GMT'
        );
    }

    public static function DATE1() : ABNF {
        return new ABNF("DATE1", "day SP month SP year",
            "",
            unique_abnf_chrs(
                static::SP(),
                static::DAY(),
                static::MONTH(),
                static::YEAR()
            ),
            static::DAY()->regexp,
            static::SP()->regexp,
            static::MONTH()->regexp,
            static::SP()->regexp,
            static::YEAR()->regexp
        );
    }

    public static function OBS_DATE() : ABNF {
        return new ABNF("OBS-DATE", 'rfc850-date / asctime-date', 'Obsolete date',
            unique_abnf_chrs(
                static::RFC850_DATE(),
                static::ASCTIME_DATE()
            ),
            build_or_regexp(static::RFC850_DATE(), static::ASCTIME_DATE())
        );
    }

    public static function ASCTIME_DATE() : ABNF {
        return new ABNF("ASCTIME-DATE", "day-name SP date3 SP time-of-day SP year",
            '',
            unique_abnf_chrs(
                static::SP(),
                static::DAY_NAME(),
                static::TIME_OF_DAY(),
                static::YEAR(),
                static::MONTH(),
                static::DIGIT()
            ),
            static::DAY_NAME()->regexp . static::SP()->regexp
            . static::MONTH()->regexp . static::SP()->regexp
                . "(?:" . static::DIGIT()->regexp . "{2}|(?:" . static::SP()->regexp . static::DIGIT()->regexp . ")?)" . static::SP()->regexp
                . static::TIME_OF_DAY()->regexp . static::SP()->regexp
                . static::YEAR()->regexp
        );
    }

    public static function RFC850_DATE() : ABNF {
        return new ABNF('RFC850-DATE', 'day-name-1 \',\' SP date2 SP time-of-day SP GMT',
            '',
            unique_abnf_chrs(
                [ "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
                '\-\,\:GMT',
                static::SP(),
                static::DAY(),
                static::MONTH(),
                static::HOUR(),
                static::MINUTE(),
                static::SECOND(),
                static::DIGIT()
            ),
            "(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)," . static::SP()->regexp
                . static::DAY()->regexp . '-'
                . static::MONTH()->regexp . '-'
                . static::DIGIT()->regexp . '{2}' . static::SP()->regexp
                . static::TIME_OF_DAY()->regexp . static::SP()->regexp
                . 'GMT'
        );
    }

    public static function ETAG() : ABNF {
        $etagc = '(?:\x21|[\x23-\xFE]|' . static::OBS_TEXT()->regexp . ')';
        return new ABNF("ETAG", '[ weak ] opaque-tag',
            'VCHAR except double quotes, plus obs-text',
            unique_abnf_chrs(
                'W/!',
                build_chrs_for_unicode_range('\x23', '\xFE'),
                static::OBS_TEXT(),
                $etagc,
                static::DQUOTE()
            ),
            '(?:W/)?' . static::DQUOTE()->regexp . $etagc . '*' . static::DQUOTE()->regexp);
    }

    public static function FIELD_NAME() : ABNF {
        return static::TOKEN()->createAlias("FIELD-NAME");
    }

    public static function FIELD_VALUE() : ABNF {
        return new ABNF('FIELD-VALUE', '*field-content',
            'Field value',
            static::FIELD_CONTENT()->chars,
            '(?:' . static::FIELD_CONTENT()->regexp . ')*'
        );
    }

    public static function FIELD_CONTENT(): ABNF
    {
        return new ABNF('FIELD-CONTENT', 'field-vchar [ 1*( SP / HTAB / field-vchar ) field-vchar ]',
            'Valid field char then optionally more chars',
            unique_abnf_chrs(static::FIELD_VCHAR(), static::SP(), static::HTAB()),
            static::FIELD_VCHAR()->regexp .
                "(?:" .
                build_or_regexp(static::SP(), static::HTAB(), static::FIELD_VCHAR()) . "+" .
                 static::FIELD_VCHAR()->regexp .
                 ')?'
        );
    }

    public static function FIELD_VCHAR(): ABNF
    {
        return new ABNF("FIELD-VCHAR", 'VCHAR / obs-text',
            'Any valid char',
            unique_abnf_chrs(static::VCHAR(), build_chrs_for_unicode_range('\x80', '\xFF')),
            build_or_regexp(static::VCHAR(), static::OBS_TEXT())
        );
    }

    public static function PARAMETERS(): ABNF
    {
        return new ABNF("PARAMETERS", "*( OWS ';' OWS [ parameter ])",
            '',
            unique_abnf_chrs(
                ';',
                static::OWS(),
                static::PARAMETER()
            ),
            '(?:' . static::OWS()->regexp . ';' . static::OWS()->regexp . '(?:' . static::PARAMETER()->regexp . ')?' . ')*'
        );
    }

    public static function PARAMETER() : ABNF {
        return new ABNF("PARAMETER", "parameter-name '=' parameter-value",
            'Parameter key value pair',
            unique_abnf_chrs(
                '=',
                static::PARAMETER_NAME(),
                static::PARAMETER_VALUE()
            ),
            static::PARAMETER_NAME()->regexp . '\=' . static::PARAMETER_VALUE()->regexp
        );
    }

    public static function PARAMETER_NAME(): ABNF
    {
        return static::TOKEN()->createAlias('PARAMETER-NAME');
    }

    public static function PARAMETER_VALUE(): ABNF
    {
        return new ABNF('PARAMETER-VALUE', '( token / quoted-string )',
            '',
            unique_abnf_chrs(
                static::TOKEN(),
                static::QUOTED_STRING()
            ),
            build_or_regexp(
                static::TOKEN(),
                static::QUOTED_STRING()
            )
        );
    }

    public static function URI_REFERENCE(): ABNF
    {
        return RFC3986::URI_REFERENCE();
    }

    public static function ABSOLUTE_URI(): ABNF
    {
        return RFC3986::ABSOLUTE_URI();
    }

    public static function RELATIVE_PART(): ABNF
    {
        return RFC3986::RELATIVE_PART();
    }

    public static function AUTHORITY(): ABNF
    {
        return RFC3986::AUTHORITY();
    }

    public static function HOST(): ABNF
    {
        return RFC3986::HOST();
    }

    public static function URI_HOST(): ABNF {
        return static::HOST()->createAlias('URI-HOST');
    }

    public static function PORT(): ABNF
    {
        return RFC3986::PORT();
    }

    public static function PATH_ABEMPTY(): ABNF
    {
        return RFC3986::PATH_ABEMPTY();
    }

    public static function SEGMENT(): ABNF
    {
        return RFC3986::SEGMENT();
    }

    public static function QUERY(): ABNF
    {
        return RFC3986::QUERY();
    }

    public static function PATH_ABSOLUTE(): ABNF
    {
        return RFC3986::PATH_ABSOLUTE();
    }

    public static function ABSOLUTE_PATH(): ABNF {
        return static::PATH_ABSOLUTE()->createAlias('ABSOLUTE-PATH');
    }

    public static function PARTIAL_URI() : ABNF {
        return new ABNF("PARTIAL-URI", "relative-part [ '?' query ]", 'Like relative-ref excluding fragment',
            unique_abnf_chrs(
                static::RELATIVE_PART(),
                '?',
                static::QUERY()
            ),
            static::RELATIVE_PART()->regexp . '(?:\?' . static::QUERY()->regexp . ')?'
        );
    }

    public static function HTTP_URI() : ABNF {
        return new ABNF("HTTP-URI", "'http' '://' authority path-abempty [ '?' query ]",
            'Mints identifiers within the hierarchical namespace governed by a potential HTTP origin server listening for TCP ([TCP]) connections on a given port',
            unique_abnf_chrs(
                'http://?',
                static::AUTHORITY(),
                static::PATH_ABEMPTY(),
                static::QUERY()
            ),
            'http:\/\/' . static::AUTHORITY()->regexp . static::PATH_ABEMPTY()->regexp . '(?:\?' . static::QUERY()->regexp . ')?'
        );
    }

    public static function HTTPS_URI() : ABNF {
        return new ABNF("HTTPS-URI", "'https' '://' authority path-abempty [ '?' query ]",
            'Mints identifiers within the hierarchical namespace governed by a potential origin server listening for TCP connections on a given port and capable of establishing a TLS ([TLS13]) connection that has been secured for HTTP communication',
            unique_abnf_chrs(
                'https://?',
                static::AUTHORITY(),
                static::PATH_ABEMPTY(),
                static::QUERY()
            ),
            'https:\/\/' . static::AUTHORITY()->regexp . static::PATH_ABEMPTY()->regexp . '(?:\?' . static::QUERY()->regexp . ')?'
        );
    }
}
?>