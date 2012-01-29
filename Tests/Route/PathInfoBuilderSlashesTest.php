<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Filter;

use Sonata\DoctrinePHPCRAdminBundle\Route\PathInfoBuilderSlashes;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;

class PathInfoBuilderSlashesTest extends \PHPUnit_Framework_TestCase
{
    function testBuild()
    {
        $admin = $this->getMock('Sonata\\AdminBundle\\Admin\\AdminInterface');

        $collection = $this->getMock('Sonata\\AdminBundle\\Route\\RouteCollection', array(), array(), '', false);
        $collection->expects($this->exactly(6))
            ->method('add')
            ->with($this->anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }
}
