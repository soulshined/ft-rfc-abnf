<?php

use FT\RFC_ABNF\Core;
use FT\RFC_ABNF\Enums\Specs;
use FT\RFC_ABNF\RegexBuilder;
use FT\RFC_ABNF\Specs\RFC9110;
use PHPUnit\Framework\TestCase;

final class RegexBuilderABNFTest extends TestCase {

    /**
    * @test
    */
    public function simple_test() {
        $builder= new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::TCHAR()->regexp . RFC9110::DIGIT()->regexp,
            $builder->abnf(":tchar :digit")->build()
        );
    }

    /**
    * @test
    */
    public function simple_test2() {
        $builder= new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::TCHAR()->regexp . '(?:' . RFC9110::DIGIT()->regexp . ')*' . RFC9110::TIME_OF_DAY()->regexp . "GMT",
            $builder->abnf(":tchar (?::digit)* :time-of-day GMT")->build()
        );
    }

    /**
    * @test
    */
    public function comma_separated_test() {
        $builder = new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '(?:' . Core::OWS()->regexp . ',' . Core::OWS()->regexp . RFC9110::DIGIT()->regexp . ')',
            $builder->withCommaSeparated("digit")->build()
        );
    }

    /**
    * @test
    */
    public function should_not_throw_for_non_existant_method_test() {
        $builder = new RegexBuilder(Specs::RFC9110);
        $this->assertEquals(
            ":SOME_ABNF_IDENTIFIER_THAT_DOESNT_EXIST",
            $builder->abnf(":SOME_ABNF_IDENTIFIER_THAT_DOESNT_EXIST")->build()
        );
    }

}

?>