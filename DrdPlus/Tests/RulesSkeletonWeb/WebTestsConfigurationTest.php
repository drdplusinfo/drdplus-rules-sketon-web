<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeletonWeb;

class WebTestsConfigurationTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function I_can_disable_test_of_table_of_contents(): void
    {
        $testsConfiguration = $this->createTestsConfiguration();
        self::assertTrue($testsConfiguration->hasTableOfContents(), 'Table of contents should be expected to test by default');
        $testsConfiguration = $this->createTestsConfiguration([WebTestsConfiguration::HAS_TABLE_OF_CONTENTS => false]);
        self::assertFalse($testsConfiguration->hasTableOfContents(), 'Test of table of contents should be disabled now');
    }

    protected function createTestsConfiguration(array $config = []): WebTestsConfiguration
    {
        $sutClass = static::getSutClass();

        return new $sutClass(\array_merge($this->getTestsConfigurationDefaultValues(), $config));
    }

    protected function getTestsConfigurationDefaultValues(): array
    {
        return [WebTestsConfiguration::SOME_EXPECTED_TABLE_IDS => []];
    }

    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~(.+)Test$~'): string
    {
        return parent::getSutClass($sutTestClass, $regexp);
    }

    /**
     * @test
     */
    public function I_can_disable_test_of_tables(): void
    {
        $testsConfiguration = $this->createTestsConfiguration();
        self::assertTrue($testsConfiguration->hasTables(), 'Tables should be expected to test by default');
        $testsConfiguration = $this->createTestsConfiguration([WebTestsConfiguration::HAS_TABLES => false]);
        self::assertFalse($testsConfiguration->hasTables(), 'Test of tables should be disabled now');
    }

    /**
     * @test
     */
    public function I_can_disable_expected_table_ids(): void
    {
        $testsConfiguration = $this->createTestsConfiguration([WebTestsConfiguration::SOME_EXPECTED_TABLE_IDS => ['foo', 'bar']]);
        self::assertSame(['foo', 'bar'], $testsConfiguration->getSomeExpectedTableIds());
    }

    /**
     * @test
     */
    public function Expected_some_table_ids_are_empty_if_no_tables_are_expected_at_all(): void
    {
        $testsConfiguration = $this->createTestsConfiguration([WebTestsConfiguration::SOME_EXPECTED_TABLE_IDS => ['foo', 'bar']]);
        self::assertTrue($testsConfiguration->hasTables(), 'Tables should be expected to test by default');
        self::assertSame(['foo', 'bar'], $testsConfiguration->getSomeExpectedTableIds());
        $testsConfiguration = $this->createTestsConfiguration(
            [WebTestsConfiguration::HAS_TABLES => false, WebTestsConfiguration::SOME_EXPECTED_TABLE_IDS => ['foo', 'bar']]
        );
        self::assertFalse($testsConfiguration->hasTables(), 'Test of tables should be disabled now');
        self::assertSame([], $testsConfiguration->getSomeExpectedTableIds(), 'No table IDs expected as tables tests have been disabled');
    }

}