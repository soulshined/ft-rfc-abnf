<?php

use FT\RFC_ABNF\Core;
use FT\RFC_ABNF\Enums\Specs;
use FT\RFC_ABNF\Exceptions\ABNFDoesNotExistForSpecException;
use FT\RFC_ABNF\RegexBuilder;
use FT\RFC_ABNF\Specs\RFC3986;
use FT\RFC_ABNF\Specs\RFC9110;
use PHPUnit\Framework\TestCase;

final class RegexBuilderTest extends TestCase {

    /**
    * @test
    */
    public function simple_test() {
        $builder= new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::TCHAR()->regexp . RFC9110::DIGIT()->regexp,
            $builder->with("tchar")->with("digit")->build()
        );
    }

    /**
    * @test
    */
    public function simple_test2() {
        $builder= new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::TCHAR()->regexp . RFC9110::DIGIT()->regexp . "*abc123",
            $builder->with("tchar")->with("digit")->text("*abc123")->build()
        );
    }

    /**
    * @test
    */
    public function non_capturing_group_test() {
        $builder= new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '(?:' . RFC3986::IPV6ADDRESS()->regexp . ')',
            $builder->with("digit")->group(
                (new RegexBuilder(Specs::RFC3986))->with("ipv6address")
            )->build()
        );
    }

    /**
    * @test
    */
    public function capturing_group_test() {
        $builder= new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::TCHAR()->regexp . RFC9110::DIGIT()->regexp . '(' . RFC3986::HOST()->regexp . ')',
            $builder->with("tchar")->with("digit")->captureGroup(
                (new RegexBuilder(Specs::RFC3986))->with("host")
            )->build()
        );
    }

    /**
    * @test
    */
    public function named_capture_group_test() {
        $builder= new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::TCHAR()->regexp . RFC9110::DIGIT()->regexp . '(?<myGroup>' . RFC3986::HOST()->regexp . ')',
            $builder->with("tchar")->with("digit")->captureGroup(
                (new RegexBuilder(Specs::RFC3986))->with("host"),
                "myGroup"
            )->build()
        );
    }

    /**
    * @test
    */
    public function optional_test() {
        $builder = new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '?',
            $builder->with("digit")->optional()->build()
        );
    }

    /**
    * @test
    */
    public function zeroOrMore_test() {
        $builder = new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '*',
            $builder->with("digit")->zeroOrMore()->build()
        );
    }

    /**
    * @test
    */
    public function oneOrMore_test() {
        $builder = new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '+',
            $builder->with("digit")->oneOrMore()->build()
        );
    }

    /**
    * @test
    */
    public function repition_test() {
        $builder = new RegexBuilder(Specs::RFC9110);

        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '{0}',
            $builder->with("digit")->repitition(0, null)->build()
        );

        $builder = new RegexBuilder(Specs::RFC9110);
        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '{1}',
            $builder->with("digit")->repitition(1, null)->build()
        );

        $builder = new RegexBuilder(Specs::RFC9110);
        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '{99,}',
            $builder->with("digit")->repitition(99)->build()
        );

        $builder = new RegexBuilder(Specs::RFC9110);
        $this->assertEquals(
            RFC9110::DIGIT()->regexp . '{1,5}',
            $builder->with("digit")->repitition(1, 5)->build()
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
    public function should_throw_for_non_existant_method_test() {
        $this->expectException(ABNFDoesNotExistForSpecException::class);

        $builder = new RegexBuilder(Specs::RFC9110);
        $builder->with("SOME_ABNF_IDENTIFIER_THAT_DOESNT_EXIST")->build();
    }

}

?>