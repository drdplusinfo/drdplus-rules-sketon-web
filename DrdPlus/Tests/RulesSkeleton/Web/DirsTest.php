<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Web;

use Granam\WebContentBuilder\Dirs;
use PHPUnit\Framework\TestCase;

class DirsTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_get_project_root(): void
    {
        $dirs = Dirs::createFromGlobals();
        self::assertDirectoryExists($dirs->getProjectRoot(), 'Can not find project root');
        self::assertSame(
            \realpath(\DRD_PLUS_PROJECT_ROOT),
            \realpath($dirs->getProjectRoot()),
            'Expected different project root'
        );
    }

    /**
     * @test
     */
    public function I_can_get_web_root(): void
    {
        $dirs = Dirs::createFromGlobals();
        self::assertDirectoryExists($dirs->getWebRoot(), 'Can not find web root');
    }

    /**
     * @test
     */
    public function I_can_get_web_vendor_root(): void
    {
        $dirs = Dirs::createFromGlobals();
        self::assertDirectoryExists($dirs->getVendorRoot(), 'Can not find vendor root');
    }

    /**
     * @test
     */
    public function I_can_get_js_root(): void
    {
        $dirs = Dirs::createFromGlobals();
        self::assertDirectoryExists($dirs->getJsRoot(), 'Can not find javascript root');
    }

    /**
     * @test
     */
    public function I_can_get_css_root(): void
    {
        $dirs = Dirs::createFromGlobals();
        self::assertDirectoryExists($dirs->getCssRoot(), 'Can not find styles root');
    }
}