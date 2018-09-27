<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Web\RulesSkeleton\YamlReader;
use Granam\String\StringTools;
use PHPUnit\Framework\TestCase;

class YamlReaderTest extends TestCase
{
    private $createdYamlTempFiles = [];

    public function __destruct()
    {
        foreach ($this->createdYamlTempFiles as $createdYamlTempFile) {
            \unlink($createdYamlTempFile);
        }
    }

    /**
     * @test
     */
    public function I_can_get_values_from_yaml_file(): void
    {
        $yamlTestingDir = $this->getYamlTestingDir();
        $yamlFile = $this->createYamlLocalConfig($data = ['foo' => 'bar', 'baz' => ['qux' => true]], $yamlTestingDir);
        $yaml = new YamlReader($yamlFile);
        self::assertSame($data, $yaml->getValues());
        foreach ($data as $key => $value) {
            self::assertArrayHasKey($key, $yaml);
            self::assertSame($value, $yaml[$key]);
        }
    }

    protected function getYamlTestingDir(): string
    {
        $yamlTestingDir = \sys_get_temp_dir() . '/' . \uniqid(StringTools::getClassBaseName(static::class), true);
        self::assertTrue(\mkdir($yamlTestingDir), 'Testing temporary dir can not be created: ' . $yamlTestingDir);

        return $yamlTestingDir;
    }

    protected function createYamlLocalConfig(array $data, string $yamlTestingDir): string
    {
        $localYamlConfig = $yamlTestingDir . '/local_config.yml';
        $this->createYamlFile($data, $localYamlConfig);
        $this->createdYamlTempFiles[] = $localYamlConfig;

        return $localYamlConfig;
    }

    private function createYamlFile(array $data, string $file): void
    {
        self::assertTrue(\yaml_emit_file($file, $data), 'Yaml file has not been created: ' . $file);
    }

    protected function createYamlDistributionConfig(array $data, string $yamlTestingDir): string
    {
        $distributionYamlConfig = $yamlTestingDir . '/distribution_config.yml';
        $this->createYamlFile($data, $distributionYamlConfig);
        $this->createdYamlTempFiles[] = $distributionYamlConfig;

        return $distributionYamlConfig;
    }

    /**
     * @test
     * @expectedException \DrdPlus\Web\RulesSkeleton\Exceptions\YamlObjectContentIsReadOnly
     */
    public function I_can_not_set_value_on_yaml_object(): void
    {
        try {
            $yamlTestingDir = $this->getYamlTestingDir();
            $yamlFile = $this->createYamlLocalConfig([], $yamlTestingDir);
            $yaml = new YamlReader($yamlFile);
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());
        }
        /** @noinspection OnlyWritesOnParameterInspection */
        $yaml['foo'] = 'bar';
    }

    /**
     * @test
     * @expectedException \DrdPlus\Web\RulesSkeleton\Exceptions\YamlObjectContentIsReadOnly
     */
    public function I_can_not_remove_value_on_yaml_object(): void
    {
        try {
            $yamlTestingDir = $this->getYamlTestingDir();
            $yamlFile = $this->createYamlLocalConfig(['foo' => 'bar'], $yamlTestingDir);
            $yaml = new YamlReader($yamlFile);
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());
        }
        /** @noinspection PhpUndefinedVariableInspection */
        unset($yaml['foo']);
    }

}