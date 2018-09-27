<?php
declare(strict_types=1);

namespace DrdPlus\Tests\Web\RulesSkeleton;

use Granam\Tests\ExceptionsHierarchy\Exceptions\AbstractExceptionsHierarchyTest;

class WebRulesSkeletonExceptionsHierarchyTest extends AbstractExceptionsHierarchyTest
{
    protected function getTestedNamespace(): string
    {
        return $this->getRootNamespace();
    }

    protected function getRootNamespace(): string
    {
        return \str_replace('\\Tests', '', __NAMESPACE__);
    }

}