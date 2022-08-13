This is a growing resource that is always changing and will always have something that needs to be adjusted or a new spec that needs to be added. That is inherently the nature of RFC.

Please keep that in mind.

## Overview

There are two conceptual offerings of this library

1. Capturing the RFC specs in model form
2. Provide a single but flexible way for building regex patterns around any spec's ABNF notation

## Capturing the RFC specs in model form

The models **must** honor the following rules:

- All RFC spec based classes inherit `FT\RFC_ABNF\Core`
- The name of the class will be the name of the spec
- The classes **must** be self-documenting. The name of the class methods will be the name of the ABNF rule identifier provided in the spec's grammar

    For example, if the spec denotes the following rules:

    ```
    OWS = *(SP / HTAB )
    time-of-day = hour ':' minute ':' second
    ```

    They will be reflected in the class as such:

    ```php
    public static function OWS() : ABNF;
    public static function TIME_OF_DAY() : ABNF;
    ```

- All ABNF rule class methods will return an `FT\RFC_ABNF\ABNF` object
- All ABNF rule class methods will be static
- It is not the goal of this library to capture all the spec's ABNF rules in the model out-of-box, but the fundamentaly building blocks of the spec's ABNF *will* be captured so to be able to build patterns with them.

    For example, in RFC9110 there is a rule for `Trailer`:
    ```
    Trailer = [ field-name *( OWS "," OWS field-name ) ]
    ```

    A rule like this generally won't be focused on because you can build a regexp using the fundamental rules of `field-name` and `OWS` as they are provided.

   Moving forward, the goal is to facilitate all rules a spec mentions

## Provide a single flexible way for building spec related regex patterns

- You can build regexp for respective RFC specs using one of the two following patterns:

    - The builder method pattern

        ```php
        (new RegexBuilder(Specs::RFC9110))
            ->with("DQUOTE")
            ->group(
                (new RegexBuilder(Specs::RFC9110))
                    ->with("qdtext")
                    ->text("|")
                    ->with("quoted-pair")
            )
            ->with("DQUOTE")
            ->build()
        ```
    - The ABNF expression pattern using template variables

        ```php
        (new RegexBuilder(Specs::RFC9110))
            ->abnf(":dquote (?::qdtext|:quoted-pair)* :dquote")
            ->build();
        ```

    Review the [wiki for complete documentation](https://github.com/soulshined/ft-rfc-abnf/wiki/Documentation)