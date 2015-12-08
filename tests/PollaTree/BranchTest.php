<?php

namespace Rentalhost\PollaTree\Test;

use Illuminate\Support\Collection;
use Rentalhost\PollaTree\Branch;
use Rentalhost\PollaTree\Tree;

/**
 * Class BranchTest
 * @package Rentalhost\PollaTree\Test
 */
class BranchTest extends Base
{
    /**
     * Get the a linked branch.
     * @return Collection
     */
    private function getLinkedBranches()
    {
        $branchA    = new Branch((object) [ 'id' => 1, 'id_parent' => null, 'title' => 'A' ]);
        $branchA1   = new Branch((object) [ 'id' => 2, 'id_parent' => 1, 'title' => 'A.1' ]);
        $branchA2   = new Branch((object) [ 'id' => 3, 'id_parent' => 1, 'title' => 'A.2' ]);
        $branchA2I  = new Branch((object) [ 'id' => 4, 'id_parent' => 3, 'title' => 'A.2.I' ]);
        $branchA2II = new Branch((object) [ 'id' => 5, 'id_parent' => 3, 'title' => 'A.2.II' ]);
        $branchB    = new Branch((object) [ 'id' => 6, 'id_parent' => null, 'title' => 'B' ]);

        // Branch A.
        $branchA->base     = $branchA;
        $branchA->children = collect([ 2 => $branchA1, 3 => $branchA2 ]);

        // Branch A.1.
        $branchA1->parent = $branchA;
        $branchA1->root   = $branchA;
        $branchA1->base   = $branchA;

        // Branch A.2.
        $branchA2->parent   = $branchA;
        $branchA2->root     = $branchA;
        $branchA2->base     = $branchA;
        $branchA2->children = collect([ 4 => $branchA2I, 5 => $branchA2II ]);

        // Branch A.2.I.
        $branchA2I->parent = $branchA2;
        $branchA2I->root   = $branchA;
        $branchA2I->base   = $branchA;

        // Branch A.2.II.
        $branchA2II->parent = $branchA2;
        $branchA2II->root   = $branchA;
        $branchA2II->base   = $branchA;

        // Branch B.
        $branchB->base = $branchB;

        return collect([ 1 => $branchA, 6 => $branchB ]);
    }

    /**
     * Test public properties from branch.
     */
    public function testPublicProperties()
    {
        static::assertClassHasAttribute('object', Branch::class);
        static::assertClassHasAttribute('parent', Branch::class);
        static::assertClassHasAttribute('root', Branch::class);
        static::assertClassHasAttribute('base', Branch::class);
        static::assertClassHasAttribute('children', Branch::class);
    }

    /**
     * Test constructor.
     *
     * @covers Rentalhost\PollaTree\Branch::__construct
     */
    public function testConstruct()
    {
        // If id_parent is null, then the branch is the own root branch.
        $branchA = new Branch((object) [ 'id_parent' => null ]);

        static::assertSame($branchA, $branchA->root);

        // Else, it'll be null for now. setParent should fill this property.
        $branchB = new Branch((object) [ 'id_parent' => 1 ]);

        static::assertSame(null, $branchB->root);
    }

    /**
     * Test getDistance and getDistanceFromRoot methods.
     *
     * @covers Rentalhost\PollaTree\Branch::getDistance
     * @covers Rentalhost\PollaTree\Branch::getDistanceFromRoot
     */
    public function testGetDistance()
    {
        /**
         * @var Branch $branchA
         * @var Branch $branchA1
         * @var Branch $branchA2
         * @var Branch $branchA2I
         * @var Branch $branchA2II
         * @var Branch $branchB
         */
        $branch     = $this->getLinkedBranches();
        $branchA    = $branch->get(1);
        $branchA1   = $branchA->children->get(2);
        $branchA2   = $branchA->children->get(3);
        $branchA2I  = $branchA2->children->get(4);
        $branchA2II = $branchA2->children->get(5);
        $branchB    = $branch->get(6);

        static::assertInstanceOf(Branch::class, $branchA);
        static::assertSame(0, $branchA->getDistance());
        static::assertSame(0, $branchA->getDistanceFromRoot());

        static::assertInstanceOf(Branch::class, $branchA1);
        static::assertSame(1, $branchA1->getDistance());
        static::assertSame(1, $branchA1->getDistanceFromRoot());

        static::assertInstanceOf(Branch::class, $branchA2);
        static::assertSame(1, $branchA2->getDistance());
        static::assertSame(1, $branchA2->getDistanceFromRoot());

        static::assertInstanceOf(Branch::class, $branchA2I);
        static::assertSame(2, $branchA2I->getDistance());
        static::assertSame(2, $branchA2I->getDistanceFromRoot());

        static::assertInstanceOf(Branch::class, $branchA2II);
        static::assertSame(2, $branchA2II->getDistance());
        static::assertSame(2, $branchA2II->getDistanceFromRoot());

        static::assertInstanceOf(Branch::class, $branchB);
        static::assertSame(0, $branchB->getDistance());
        static::assertSame(0, $branchB->getDistanceFromRoot());
    }

    /**
     * Test getDepth method.
     *
     * @covers Rentalhost\PollaTree\Branch::getDepth
     */
    public function testGetDepth()
    {
        $tree     = new Tree(collect([
            (object) [ 'id' => 1, 'id_parent' => null ],
            (object) [ 'id' => 2, 'id_parent' => 1 ],
            (object) [ 'id' => 3, 'id_parent' => 2 ],
            (object) [ 'id' => 4, 'id_parent' => 2 ],
            (object) [ 'id' => 5, 'id_parent' => 2 ],
            (object) [ 'id' => 6, 'id_parent' => 4 ],
            (object) [ 'id' => 7, 'id_parent' => 6 ],
        ]));
        $branches = $tree->getLinkedBranch(Tree::TYPE_LINEAR);

        static::assertSame(4, $branches->get(1)->getDepth());
        static::assertSame(3, $branches->get(2)->getDepth());
        static::assertSame(0, $branches->get(3)->getDepth());
        static::assertSame(2, $branches->get(4)->getDepth());
        static::assertSame(0, $branches->get(5)->getDepth());
        static::assertSame(1, $branches->get(6)->getDepth());
        static::assertSame(0, $branches->get(7)->getDepth());
    }

    /**
     * Test setParent method.
     *
     * @covers Rentalhost\PollaTree\Branch::setParent
     */
    public function testSetParent()
    {
        // Linked.
        $branchA   = new Branch((object) [ 'id' => 1, 'id_parent' => null ]);
        $branchA1  = new Branch((object) [ 'id' => 2, 'id_parent' => 1 ]);
        $branchA1I = new Branch((object) [ 'id' => 3, 'id_parent' => 1 ]);

        // Branch A.
        $branchA->setParent(null);
        $branchA->setChildren(collect([ 2 => $branchA1 ]));

        static::assertNull($branchA->parent);
        static::assertSame($branchA, $branchA->root);
        static::assertSame($branchA, $branchA->base);

        // Branch A.1.
        $branchA1->setParent($branchA);

        static::assertSame($branchA, $branchA1->parent);
        static::assertSame($branchA, $branchA1->root);
        static::assertSame($branchA, $branchA1->base);

        // Branch A.1.I.
        $branchA1I->setParent($branchA1);

        static::assertSame($branchA, $branchA1->parent);
        static::assertSame($branchA, $branchA1I->root);
        static::assertSame($branchA, $branchA1I->base);

        // Unlinked.
        $branchUnlinked = new Branch((object) [ 'id' => 4, 'id_parent' => 1 ]);
        $branchUnlinked->setParent(null);

        static::assertNull($branchUnlinked->parent);
        static::assertNull($branchUnlinked->root);
        static::assertSame($branchUnlinked, $branchUnlinked->base);
    }

    /**
     * Test setChildren method.
     *
     * @covers Rentalhost\PollaTree\Branch::setChildren
     */
    public function testSetChildren()
    {
        $branchA  = new Branch((object) [ 'id' => 1, 'id_parent' => null ]);
        $branchA1 = new Branch((object) [ 'id' => 2, 'id_parent' => 2 ]);

        $branchA->setChildren(new Collection);

        static::assertNull($branchA->children);

        $branchA->setChildren(collect([ $branchA1 ]));

        static::assertSame([ $branchA1 ], $branchA->children->toArray());
    }

    /**
     * Test isLinked, isRoot and isBase methods.
     *
     * @covers Rentalhost\PollaTree\Branch::isLinked
     * @covers Rentalhost\PollaTree\Branch::isRoot
     * @covers Rentalhost\PollaTree\Branch::isBase
     */
    public function testIsMethods()
    {
        // Linked.
        $branchA   = new Branch((object) [ 'id' => 1, 'id_parent' => null ]);
        $branchA1  = new Branch((object) [ 'id' => 2, 'id_parent' => 1 ]);
        $branchA1I = new Branch((object) [ 'id' => 3, 'id_parent' => 1 ]);

        // Branch A.
        $branchA->setParent(null);
        $branchA->setChildren(collect([ 2 => $branchA1 ]));

        static::assertTrue($branchA->isLinked());
        static::assertTrue($branchA->isBase());
        static::assertTrue($branchA->isRoot());

        // Branch A.1.
        $branchA1->setParent($branchA);

        static::assertTrue($branchA1->isLinked());
        static::assertFalse($branchA1->isBase());
        static::assertFalse($branchA1->isRoot());

        // Branch A.1.I.
        $branchA1I->setParent($branchA1);

        static::assertTrue($branchA1I->isLinked());
        static::assertFalse($branchA1I->isBase());
        static::assertFalse($branchA1I->isRoot());

        // Unlinked.
        $branchUnlinked = new Branch((object) [ 'id' => 4, 'id_parent' => 1 ]);
        $branchUnlinked->setParent(null);

        static::assertFalse($branchUnlinked->isLinked());
        static::assertTrue($branchUnlinked->isBase());
        static::assertFalse($branchUnlinked->isRoot());
    }
}
