<?php

namespace FT\RFC_ABNF\Specs;

use FT\RFC_ABNF\ {
    Core,
    ABNF,

    Interface\IUriReference,

    function Utils\unique_abnf_chrs,
    function Utils\build_chrs_for_unicode_range,
    function Utils\build_or_regexp
};

final class RFC3986 extends Core
    implements IUriReference
{

    public static function URI() : ABNF {
        return new ABNF("URI", "scheme ':' hier-part [ '?' query ] [ '#' fragment ]",
            'A URI is an identifier consisting of a sequence of characters matching the syntax rule named <URI> in Section 3.  It enables uniform identification of resources via a separately defined extensible set of naming schemes (Section 3.1).  How that identification is accomplished, assigned, or enabled is delegated to each scheme specification',
            unique_abnf_chrs(
                static::SCHEME(),
                static::HIER_PART(),
                static::QUERY(),
                static::FRAGMENT(),
                '?#:'
            ),
            static::SCHEME()->regexp . '\:' . static::HIER_PART()->regexp . '(?:\?' . static::QUERY()->regexp . ')?' . '(?:\#' . static::FRAGMENT()->regexp . ')?'
        );
    }

    public static function HIER_PART() : ABNF {
        return new ABNF('HIER-PART', "'//' authority path-abempty / path-absolute / path-rootless / path-empty",
            'Authority part optionally with leading slashes',
            unique_abnf_chrs(
                static::AUTHORITY(),
                static::PATH_ABEMPTY(),
                static::PATH_ABSOLUTE(),
                static::PATH_ROOTLESS(),
                static::PATH_EMPTY(),
                ['\/\/']
            ),
            build_or_regexp(
                '\/\/' . static::AUTHORITY()->regexp . static::PATH_ABEMPTY()->regexp,
                static::PATH_ABSOLUTE(),
                static::PATH_ROOTLESS(),
                static::PATH_EMPTY()
            )
        );
    }

    public static function URI_REFERENCE() : ABNF {
        //regex pattern provided by spec https://www.rfc-editor.org/rfc/rfc3986#appendix-B
        return new ABNF('URI-REFERENCE', 'URI / relative-ref',
            'A URI-reference is either a URI or a relative reference.  If the URI-reference\'s prefix does not match the syntax of a scheme followed by its colon separator, then the URI-reference is a relative reference',
            unique_abnf_chrs(
                [':', '#', '?'],
                static::SCHEME(),
                static::HIER_PART(),
                static::QUERY(),
                static::FRAGMENT(),
                static::RELATIVE_REF()
            ),
            '^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?'
        );
    }

    public static function ABSOLUTE_URI() : ABNF {
        return new ABNF('ABSOLUTE-URI', "scheme ':' hier-part [ '?' query ]",
            'Some protocol elements allow only the absolute form of a URI without a fragment identifier.  For example, defining a base URI for later use by relative references calls for an absolute-URI syntax rule that does not allow a fragment',
            unique_abnf_chrs(
                static::SCHEME(),
                static::HIER_PART(),
                static::QUERY(),
                ['?', ':']
            ),
            static::SCHEME()->regexp . '\:' . static::HIER_PART()->regexp .
                '(?:\?' . static::QUERY()->regexp . ')?'
        );
    }

    public static function RELATIVE_REF() : ABNF {
        return new ABNF('RELATIVE-REF', "relative-part [ '?' query ] [ '#' fragment ]",
            'A relative reference that begins with two slash characters is termed a network-path reference; such references are rarely used.  A relative reference that begins with a single slash character is termed an absolute-path reference.  A relative reference that does not begin with a slash character is termed a relative-path reference. A path segment that contains a colon character (e.g., "this:that") cannot be used as the first segment of a relative-path reference, as it would be mistaken for a scheme name.  Such a segment must be preceded by a dot-segment (e.g., "./this:that") to make a relative- path reference',
            unique_abnf_chrs(
                ['/'],
                static::AUTHORITY(),
                static::PATH_ABEMPTY(),
                static::PATH_ABSOLUTE(),
                static::PATH_NOSCHEME(),
                static::PATH_EMPTY()
            ),
            build_or_regexp(
                '\/\/' . static::AUTHORITY()->regexp . static::PATH_ABEMPTY()->regexp,
                static::PATH_ABSOLUTE(),
                static::PATH_NOSCHEME(),
                static::PATH_EMPTY()
            )
        );
    }

    public static function RELATIVE_PART() : ABNF {
        return new ABNF('RELATIVE-PART', "'//' authority path-abempty / path-absolute / path-noscheme / path-empty",
            static::RELATIVE_REF()->definition,
            unique_abnf_chrs(
                ['/'],
                static::AUTHORITY(),
                static::PATH_ABEMPTY(),
                static::PATH_ABSOLUTE(),
                static::PATH_NOSCHEME(),
                static::PATH_EMPTY()
            ),
            build_or_regexp(
                '\/\/' . static::AUTHORITY()->regexp . static::PATH_ABEMPTY()->regexp,
                static::PATH_ABSOLUTE(),
                static::PATH_NOSCHEME(),
                static::PATH_EMPTY()
            )
        );
    }

    public static function SCHEME() : ABNF {
        return new ABNF('SCHEME', "ALPHA *( APLHA / DIGIT / '+' / '-' / '.' )",
            'Scheme names consist of a sequence of characters beginning with a letter and followed by any combination of letters, digits, plus ("+"), period ("."), or hyphen ("-").  Although schemes are case- insensitive, the canonical form is lowercase and documents that specify schemes must do so with lowercase letters.  An implementation should accept uppercase letters as equivalent to lowercase in scheme names (e.g., allow "HTTP" as well as "http") for the sake of robustness but should only produce lowercase scheme names for consistency',
            unique_abnf_chrs(
                static::ALPHA(),
                static::DIGIT(),
                '+-.'
            ),
            static::ALPHA()->regexp .
                 build_or_regexp( static::ALPHA(), static::DIGIT(), '\+', '\-', '\.' )
            . '*'
        );
    }

    public static function AUTHORITY() : ABNF {
        return new ABNF('authority', "[ userinfo '@' ] host [ ':' port ]",
            'The generic syntax provides a common means for distinguishing an authority based on a registered name or server address, along with optional port and user information. The authority component is preceded by a double slash ("//") and is terminated by the next slash ("/"), question mark ("?"), or number sign ("#") character, or by the end of the URI',
            unique_abnf_chrs(
                static::USERINFO(),
                static::HOST(),
                static::PORT(),
                ['@']
            ),
            '(?:' . static::USERINFO()->regexp . '@)?' . static::HOST()->regexp . '(?:' . static::PORT()->regexp . ')?'
        );
    }

    public static function USERINFO() : ABNF {
        return new ABNF('USERINFO', "*( unreserved / pct-encoded / sub-delims / ':')",
            'The userinfo subcomponent may consist of a user name and, optionally, scheme-specific information about how to gain authorization to access the resource.  The user information, if present, is followed by a commercial at-sign ("@") that delimits it from the host. Usef of the format is deprecated',
            unique_abnf_chrs(
                static::UNRESERVED(),
                static::PCT_ENCODED(),
                static::SUB_DELIMS(),
                [':']
            ),
            build_or_regexp(
                static::UNRESERVED(),
                static::PCT_ENCODED(),
                static::SUB_DELIMS(),
                ':'
            ) . '*'
        );
    }

    public static function HOST() : ABNF {
        return new ABNF('HOST', 'IP-literal / IPv4address / reg-name',
            'The host subcomponent is case- insensitive. The data within the host component identifies a registered name that has nothing to do with an Internet host.  We use the name "host" for the ABNF rule because that is its most common purpose, not its only purpose. The syntax rule for host is ambiguous because it does not completely distinguish between an IPv4address and a reg-name.  In order to disambiguate the syntax, we apply the "first-match-wins" algorithm: If host matches the rule for IPv4address, then it should be considered an IPv4 address literal and not a reg-name',
            unique_abnf_chrs(
                static::IP_LITERAL(),
                static::IPV4ADDRESS(),
                static::REG_NAME()
            ),
            build_or_regexp(static::IP_LITERAL(), static::IPV4ADDRESS(), static::REG_NAME())
        );
    }

    public static function PORT() : ABNF {
        return new ABNF('PORT', '*DIGIT',
            'Designates an optional port number in decimal following the host and delimited from it by a single colon (":") character',
            static::DIGIT()->chars,
            static::DIGIT()->regexp . '*'
        );
    }

    public static function IP_LITERAL() : ABNF {
        return new ABNF('IP-LITERAL', "'[' ( IPv6address / IPvFuture ) ']'",
            '',
            unique_abnf_chrs(
                static::IPV6ADDRESS()->chars,
                static::IPVFUTURE()->chars,
                ['[', ']']
            ),
            '\['. build_or_regexp(static::IPV6ADDRESS(), static::IPVFUTURE()) .'\]'
        );
    }

    public static function IPVFUTURE() : ABNF {
        return new ABNF('IPVFUTURE', "'v' 1*HEXDIG '.' 1*( unreserved / sub-delims / ':' )",
            '',
            unique_abnf_chrs(
                static::HEXDIG(),
                static::UNRESERVED(),
                static::SUB_DELIMS(),
                'v.:'
            ),
            'v' . static::HEXDIG()->regexp . '+\.' . build_or_regexp(static::UNRESERVED(), static::SUB_DELIMS(), ':') . '+'
        );
    }

    public static function IPV6ADDRESS() : ABNF {
        return new ABNF('IPV6ADDRESS',
            "6( h16 ':' ) ls32 / '::' 5( h16 ':' ) ls32 / [ h16 ] '::' 4( h16 ':' ) ls32 / [ *1( h16 ':' ) h16 ] '::' 3( h16 ':' ) ls32 / [ *2( h16 ':' ) h16 ] '::' 2( h16 ':' ) ls32 / [ *3( h16 ':' ) h16 ] '::' h16 ':' ls32 / [ *4( h16 ':' ) h16 ] '::' ls32 / [ *5( h16 ':' ) h16 ] '::' h16 / [ *6( h16 ':' ) h16 ] '::'",
            'Represented inside the square brackets without a preceding version flag. A 128-bit IPv6 address is divided into eight 16-bit pieces.  Each piece is represented numerically in case-insensitive hexadecimal, using one to four hexadecimal digits (leading zeroes are permitted). The eight encoded pieces are given most-significant first, separated by colon characters.  Optionally, the least-significant two pieces may instead be represented in IPv4 address textual format.  A sequence of one or more consecutive zero-valued 16-bit pieces within the address may be elided, omitting all their digits and leaving exactly two consecutive colons in their place to mark the elision',
            unique_abnf_chrs(
                static::LS32(),
                '[]:'
            ),
            build_or_regexp(
                '(?:' . static::H16()->regexp . ':){6}' . static::LS32()->regexp,
                '::(?:' . static::H16()->regexp . ':){5}' . static::LS32()->regexp,
                '(?:' . static::H16()->regexp . ')?::(?:' . static::H16()->regexp . ':){4}' . static::LS32()->regexp,
                '(?:(?:' . static::H16()->regexp . ':)?' . static::H16()->regexp . ')?::(?:' . static::H16()->regexp . ':){3}' . static::LS32()->regexp,
                '(?:(?:' . static::H16()->regexp . ':){0,2}' . static::H16()->regexp . ')?::(?:' . static::H16()->regexp . ':){2}' . static::LS32()->regexp,
                '(?:(?:' . static::H16()->regexp . ':){0,3}' . static::H16()->regexp . ')?::' . static::H16()->regexp . ':' . static::LS32()->regexp,
                '(?:(?:' . static::H16()->regexp . ':){0,4}' . static::H16()->regexp . ')?::' . static::LS32()->regexp,
                '(?:(?:' . static::H16()->regexp . ':){0,5}' . static::H16()->regexp . ')?::' . static::H16()->regexp,
                '(?:(?:' . static::H16()->regexp . ':){0,6}' . static::H16()->regexp . ')?::'
            )
        );
    }

    public static function LS32() : ABNF {
        return new ABNF("LS32", "( h16 ':' h16 ) / IPv4address",
            'Least-significant 32 bits of address',
            unique_abnf_chrs(
                static::H16(),
                ':',
                static::IPV4ADDRESS()
            ),
            '(?:(?:' . static::H16()->regexp .':' . static::H16()->regexp . ')|' . static::IPV4ADDRESS()->regexp .')'
        );
    }

    public static function H16(): ABNF
    {
        return new ABNF('H16', "1*4HEXDIG",
            '16 bits of address represented in hexadecimal',
            static::HEXDIG()->chars,
            static::HEXDIG()->regexp . '{1,4}'
        );
    }

    public static function IPV4ADDRESS() : ABNF {
        return new ABNF('IPV4ADDRESS', "dec-octet '.' dect-octet '.' dect-octet '.' dec-octet",
            'Represented in dotted-decimal notation (a sequence of four decimal numbers in the range 0 to 255, separated by ".")',
            unique_abnf_chrs(static::DEC_OCTET(), '.'),
            static::DEC_OCTET()->regexp . '\.' .
            static::DEC_OCTET()->regexp . '\.' .
            static::DEC_OCTET()->regexp . '\.' .
            static::DEC_OCTET()->regexp
        );
    }

    public static function DEC_OCTET() : ABNF {
        return new ABNF("DEC-OCTET", "DIGIT / %x31-39 DIGIT / '1' 2DIGIT / '2' %x30-34 DIGIT / '25' %x30-35",
            '0-9 or 10-99 or 100-199 or 200-249 or 250-255',
            unique_abnf_chrs(
                static::DIGIT(),
                build_chrs_for_unicode_range('\x31', '\x39'),
                build_chrs_for_unicode_range('\x30', '\x34'),
                build_chrs_for_unicode_range('\x30', '\x35'),
                ['1', '2', '5']
            ),
            build_or_regexp(
                static::DIGIT(),
                '[\x31-\x39]' . static::DIGIT()->regexp,
                '1' . static::DIGIT()->regexp . '{2}',
                '2[\x30-\x34]' . static::DIGIT()->regexp,
                '25[\x30-\x35]'
            )
        );
    }

    public static function REG_NAME() : ABNF {
        return new ABNF("REG-NAME", "*( unreserved / pct-encoded / sub-delims )",
            'Allows percent-encoded octets in order to represent non-ASCII registered names in a uniform way that is independent of the underlying name resolution technology',
            unique_abnf_chrs(
                static::UNRESERVED(),
                static::PCT_ENCODED(),
                static::SUB_DELIMS()
            ),
            build_or_regexp(
                static::UNRESERVED(),
                static::PCT_ENCODED(),
                static::SUB_DELIMS()
            ) . '*'
        );
    }

    public static function PATH() : ABNF {
        return new ABNF("PATH", "path-abempty / path-absolute / path-noscheme / path-rootless / path-empty",
            'Fully qualified path portion of a uri',
            unique_abnf_chrs(
                static::PATH_ABEMPTY(),
                static::PATH_ABSOLUTE(),
                static::PATH_NOSCHEME(),
                static::PATH_ROOTLESS(),
                static::PATH_EMPTY()
            ),
            build_or_regexp(
                static::PATH_ABEMPTY(),
                static::PATH_ABSOLUTE(),
                static::PATH_NOSCHEME(),
                static::PATH_ROOTLESS(),
                static::PATH_EMPTY()
            )
        );
    }

    public static function PATH_ABEMPTY() : ABNF {
        return new ABNF("PATH-ABEMPTY", "*( '/' segment )",
            "Begins with '/' or is empty",
            unique_abnf_chrs(static::SEGMENT(), '/'),
            '(?:\/' . static::SEGMENT()->regexp . ')*'
        );
    }

    public static function PATH_ABSOLUTE() : ABNF {
        return new ABNF("PATH-ABSOLUTE", "'/' [ segment-nz *( '/' segment ) ]",
            "Begins with '/' but not '//'",
            unique_abnf_chrs(
                static::SEGMENT_NZ(),
                static::SEGMENT(),
                '/'
            ),
            '\/(?:' . static::SEGMENT_NZ()->regexp . '(?:\/' . static::SEGMENT()->regexp . ')*)?'
        );
    }

    public static function PATH_NOSCHEME() : ABNF {
        return new ABNF("PATH-NOSCHEME", "segment-nz-nc *( '/' segment )",
            'Begins with a non-colon non-zero length segment',
            unique_abnf_chrs(
                static::SEGMENT_NZ_NC(),
                static::SEGMENT(), '/'
            ),
            static::SEGMENT_NZ_NC()->regexp . '(\/' . static::SEGMENT()->regexp . ')*'
        );
    }

    public static function PATH_ROOTLESS() : ABNF {
        return new ABNF("PATH-ROOTLESS", "segment-nz *( '/' segment )",
            'Begins with a segment',
            unique_abnf_chrs(
                static::SEGMENT_NZ(),
                static::SEGMENT(), '/'
            ),
            static::SEGMENT_NZ()->regexp . '(\/' . static::SEGMENT()->regexp . ')*'
        );
    }

    public static function PATH_EMPTY() : ABNF {
        return new ABNF("PATH-EMPTY", "0<pchar>",
            'Zero characters',
            [],
            '(?!' . static::PCHAR()->regexp . ')'
        );
    }

    public static function SEGMENT() : ABNF {
        return new ABNF("SEGMENT", "*phar",
            'Zero or more pchars',
            static::PCHAR()->chars,
            static::PCHAR()->regexp . '*'
        );
    }

    public static function SEGMENT_NZ() : ABNF {
        return new ABNF("SEGMENT-NZ", "1*phar",
            'Non zero segment',
            static::PCHAR()->chars,
            static::PCHAR()->regexp . '+'
        );
    }

    public static function SEGMENT_NZ_NC() : ABNF {
        return new ABNF("SEGMENT-NZ-NC", "1*( unreserved / pct-encoded / sub-delims / '@' )",
            'Non zero no colon segement',
            unique_abnf_chrs(
                static::UNRESERVED(),
                static::PCT_ENCODED(),
                static::SUB_DELIMS(),
                '@'
            ),
            build_or_regexp(
                static::UNRESERVED(),
                static::PCT_ENCODED(),
                static::SUB_DELIMS(),
                '\@'
            ) . '+'
        );
    }

    public static function QUERY() : ABNF {
        return new ABNF("QUERY", "*( pchar / '/' / '?' )",
            'contains non-hierarchical data that, along with data in the path component, serves to identify a resource within the scope of the URI\'s scheme and naming authority (if any)',
            unique_abnf_chrs(static::PCHAR(), '/?'),
            build_or_regexp(
                static::PCHAR(),
                '\/',
                '\?'
            ) . '*'
        );
    }

    public static function PCHAR(): ABNF
    {
        return new ABNF("PCHAR", "unreserved / pct-encoded / sub-delims / ':' / '@'",
            'Unreserved or percent encoded or sub delimiters or \':\' or \'@\'',
            unique_abnf_chrs(static::UNRESERVED(), static::PCT_ENCODED(), static::SUB_DELIMS(), ':@'),
            build_or_regexp(
                static::UNRESERVED(),
                static::PCT_ENCODED(),
                static::SUB_DELIMS(),
                '\:',
                '\@'
            )
        );
    }

    public static function FRAGMENT() : ABNF {
        return static::QUERY()->createAlias("FRAGMENT");
    }

    public static function PCT_ENCODED() : ABNF {
        return new ABNF("PCT-ENCODED", "'%' HEXDIG HEXDIG",
            'Represents a data octet in a component when that octet\'s corresponding character is outside the allowed set or is being used as a delimiter of, or within, the component',
            unique_abnf_chrs('%', static::HEXDIG()),
            '\%' . static::HEXDIG()->regexp . '{2}'
        );
    }

    public static function UNRESERVED() : ABNF {
        return new ABNF("UNRESERVED", "ALPHA / DIGIT / '-' / '.' / '_' / '~'",
            'Characters that are allowed in a URI but do not have a reserved purpose are called unreserved',
            unique_abnf_chrs(static::ALPHA(), static::DIGIT(), '-_.~'),
            build_or_regexp(
                static::ALPHA(),
                static::DIGIT(),
                '\-',
                '\.',
                '\_',
                '\~'
            )
        );
    }

    public static function RESERVED() : ABNF {
        return new ABNF("RESERVED", "gen-delims / sub-delims",
            'These characters are called "reserved" because they may (or may not) be defined as delimiters by the generic syntax, by each scheme-specific syntax, or by the implementation-specific syntax of a URI\'s dereferencing algorithm. If data for a URI component would conflict with a reserved character\'s purpose as a delimiter, then the conflicting data must be percent-encoded before the URI is formed',
            unique_abnf_chrs(static::GEN_DELIMS(), static::SUB_DELIMS()),
            build_or_regexp(static::GEN_DELIMS(), static::SUB_DELIMS())
        );
    }

    public static function GEN_DELIMS() : ABNF {
        return new ABNF("GEN-DELIMS", "':' / '/' / '?' / '#' / '[' / ']' / '@'",
            'Generic delimiters - used as delimiters of the generic URI components described in https://www.rfc-editor.org/rfc/rfc3986#section-3',
            unique_abnf_chrs(':/?#[]@'),
            '[\:\/\?\#\[\]\@]'
        );
    }

    public static function SUB_DELIMS() : ABNF {
        return new ABNF("SUB_DELIMS", "'!' / '$' / '&' / ''' / '(' / ')' / '*' / '+' / ',' / ';' / '='",
            'Any of the following chars: !$&\'()*+,;=',
            unique_abnf_chrs('!$&\'()*+,;='),
            "[\!\$\&\'\(\)\*\+\,\;\=]"
        );
    }
}
?>