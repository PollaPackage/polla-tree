<?php

namespace Rentalhost\PollaTree\Test;

use Illuminate\Support\Collection;
use Rentalhost\PollaTree\Tree;

/**
 * Class TreeTest
 * @package Rentalhost\PollaTree\Test
 */
class TreeTest extends Base
{
    /**
     * Returns a default collection type A.
     *
     * @return Collection
     */
    private static function getCollectionA()
    {
        return collect([
            // Default.
            (object) [ 'id' => 10, 'id_parent' => null, 'title' => 'A' ],
            (object) [ 'id' => 110, 'id_parent' => null, 'title' => 'B' ],
            (object) [ 'id' => 130, 'id_parent' => null, 'title' => 'C' ],
            (object) [ 'id' => 20, 'id_parent' => 10, 'title' => 'A.1' ],
            (object) [ 'id' => 30, 'id_parent' => 10, 'title' => 'A.2' ],
            (object) [ 'id' => 40, 'id_parent' => 30, 'title' => 'A.2.I' ],
            (object) [ 'id' => 50, 'id_parent' => 30, 'title' => 'A.2.II' ],
            (object) [ 'id' => 60, 'id_parent' => 30, 'title' => 'A.2.III' ],
            (object) [ 'id' => 70, 'id_parent' => 60, 'title' => 'A.2.III.a' ],
            (object) [ 'id' => 80, 'id_parent' => 60, 'title' => 'A.2.III.b' ],
            (object) [ 'id' => 90, 'id_parent' => 10, 'title' => 'A.3' ],
            (object) [ 'id' => 100, 'id_parent' => 90, 'title' => 'A.3.I' ],
            (object) [ 'id' => 120, 'id_parent' => 110, 'title' => 'B.1' ],

            // Unlinked.
            (object) [ 'id' => 220, 'id_parent' => 210, 'title' => '4' ],
            (object) [ 'id' => 260, 'id_parent' => 250, 'title' => '5' ],
            (object) [ 'id' => 230, 'id_parent' => 220, 'title' => '4.I' ],
            (object) [ 'id' => 240, 'id_parent' => 220, 'title' => '4.II' ],
            (object) [ 'id' => 270, 'id_parent' => 260, 'title' => '5.I' ],
        ]);
    }

    /**
     * Returns a default collection type B.
     * It's a reverse order collection.
     *
     * @return Collection
     */
    private static function getCollectionB()
    {
        return collect([
            (object) [ 'id' => 3, 'id_parent' => 2, 'title' => 'Level 3' ],
            (object) [ 'id' => 2, 'id_parent' => 1, 'title' => 'Level 2' ],
            (object) [ 'id' => 1, 'id_parent' => null, 'title' => 'Level 1' ],
        ]);
    }

    /**
     * Returns a default colleciton type C.
     * It's contain a self parent element, it'll be treated as unlinked branch.
     * @return Collection
     */
    private static function getCollectionC()
    {
        return collect([
            (object) [ 'id' => 1, 'id_parent' => 1, 'title' => 'Own Parent?' ],
        ]);
    }

    /**
     * Test getProcessedCollection method.
     *
     * @covers Rentalhost\PollaTree\Tree::getProcessedCollection
     */
    public function testGetProcessedCollection()
    {
        // Linked nodes.
        $tree     = new Tree(self::getCollectionA());
        $branches = $tree->getLinkedBranch(Tree::TYPE_LINEAR);

        static::assertInstanceOf(Collection::class, $branches);

        // ID: 10
        $branch = $branches->get(10);
        static::assertNull($branch->parent);
        static::assertSame($branch, $branch->base);
        static::assertSame($branch, $branch->root);
        static::assertSame('A', $branch->object->title);
        static::assertEquals(collect([
            20 => $branches->get(20),
            30 => $branches->get(30),
            90 => $branches->get(90),
        ]), $branch->children);

        // ID: 20
        $branch = $branches->get(20);
        static::assertSame($branches->get(10), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.1', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 30
        $branch = $branches->get(30);
        static::assertSame($branches->get(10), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.2', $branch->object->title);
        static::assertEquals(collect([
            40 => $branches->get(40),
            50 => $branches->get(50),
            60 => $branches->get(60),
        ]), $branch->children);

        // ID: 40
        $branch = $branches->get(40);
        static::assertSame($branches->get(30), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.2.I', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 50
        $branch = $branches->get(50);
        static::assertSame($branches->get(30), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.2.II', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 60
        $branch = $branches->get(60);
        static::assertSame($branches->get(30), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.2.III', $branch->object->title);
        static::assertEquals(collect([
            70 => $branches->get(70),
            80 => $branches->get(80),
        ]), $branch->children);

        // ID: 70
        $branch = $branches->get(70);
        static::assertSame($branches->get(60), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.2.III.a', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 80
        $branch = $branches->get(80);
        static::assertSame($branches->get(60), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.2.III.b', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 90
        $branch = $branches->get(90);
        static::assertSame($branches->get(10), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.3', $branch->object->title);
        static::assertEquals(collect([
            100 => $branches->get(100),
        ]), $branch->children);

        // ID: 100
        $branch = $branches->get(100);
        static::assertSame($branches->get(90), $branch->parent);
        static::assertSame($branches->get(10), $branch->base);
        static::assertSame($branches->get(10), $branch->root);
        static::assertSame('A.3.I', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 110
        $branch = $branches->get(110);
        static::assertNull($branch->parent);
        static::assertSame($branches->get(110), $branch->base);
        static::assertSame($branches->get(110), $branch->root);
        static::assertSame('B', $branch->object->title);
        static::assertEquals(collect([
            120 => $branches->get(120),
        ]), $branch->children);

        // ID: 120
        $branch = $branches->get(120);
        static::assertSame($branches->get(110), $branch->parent);
        static::assertSame($branches->get(110), $branch->base);
        static::assertSame($branches->get(110), $branch->root);
        static::assertSame('B.1', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 130
        $branch = $branches->get(130);
        static::assertNull($branch->parent);
        static::assertSame($branches->get(130), $branch->base);
        static::assertSame($branches->get(130), $branch->root);
        static::assertSame('C', $branch->object->title);
        static::assertNull($branch->children);

        // Linked nodes.
        $tree     = new Tree(self::getCollectionA());
        $branches = $tree->getUnlinkedBranch(Tree::TYPE_LINEAR);

        static::assertInstanceOf(Collection::class, $branches);

        // ID: 220
        $branch = $branches->get(220);
        static::assertNull($branch->parent);
        static::assertSame($branches->get(220), $branch->base);
        static::assertNull($branch->root);
        static::assertSame('4', $branch->object->title);
        static::assertEquals(collect([
            230 => $branches->get(230),
            240 => $branches->get(240),
        ]), $branch->children);

        // ID: 230
        $branch = $branches->get(230);
        static::assertSame($branches->get(220), $branch->parent);
        static::assertSame($branches->get(220), $branch->base);
        static::assertNull($branch->root);
        static::assertSame('4.I', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 240
        $branch = $branches->get(240);
        static::assertSame($branches->get(220), $branch->parent);
        static::assertSame($branches->get(220), $branch->base);
        static::assertNull($branch->root);
        static::assertSame('4.II', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 260
        $branch = $branches->get(260);
        static::assertNull($branch->parent);
        static::assertSame($branches->get(260), $branch->base);
        static::assertNull($branch->root);
        static::assertSame('5', $branch->object->title);
        static::assertEquals(collect([
            270 => $branches->get(270),
        ]), $branch->children);

        // ID: 270
        $branch = $branches->get(270);
        static::assertSame($branches->get(260), $branch->parent);
        static::assertSame($branches->get(260), $branch->base);
        static::assertNull($branch->root);
        static::assertSame('5.I', $branch->object->title);
        static::assertNull($branch->children);
    }

    /**
     * Test getLinkedBranch method.
     *
     * @covers Rentalhost\PollaTree\Tree::__construct
     * @covers Rentalhost\PollaTree\Tree::getLinkedBranch
     * @covers Rentalhost\PollaTree\Tree::reorderCollection
     */
    public function testGetLinkedBranch()
    {
        $tree     = new Tree(self::getCollectionA());
        $branches = $tree->getLinkedBranch(/** Tree::TYPE_TREE */);

        // Tree type.
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame([ 'A', 'B', 'C' ], $branches->pluck('object.title')->toArray());

        // Linear type.
        $branches = $tree->getLinkedBranch(Tree::TYPE_LINEAR);
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame(explode(',', 'A,A.1,A.2,A.2.I,A.2.II,A.2.III,A.2.III.a,A.2.III.b,A.3,A.3.I,B,B.1,C'),
            $branches->pluck('object.title')->toArray());
    }

    /**
     * Test getUnlinkedBranch method.
     *
     * @covers Rentalhost\PollaTree\Tree::getUnlinkedBranch
     */
    public function testGetUnlinkedBranch()
    {
        $tree     = new Tree(self::getCollectionA());
        $branches = $tree->getUnlinkedBranch(/** Tree::TYPE_TREE */);

        // Tree type.
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame([ '4', '5' ], $branches->pluck('object.title')->toArray());

        // Linear type.
        $branches = $tree->getUnlinkedBranch(Tree::TYPE_LINEAR);
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame([ '4', '4.I', '4.II', '5', '5.I' ], $branches->pluck('object.title')->toArray());
    }

    /**
     * Test getBothBranches method.
     *
     * @covers Rentalhost\PollaTree\Tree::getBothBranches
     */
    public function testGetBothBranches()
    {
        $tree     = new Tree(self::getCollectionA());
        $branches = $tree->getBothBranches(/** Tree::TYPE_TREE, Tree::LINKED_FIRST */);

        // Tree type.
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame([ 'A', 'B', 'C', '4', '5' ], $branches->pluck('object.title')->toArray());

        // Linear type.
        $branches = $tree->getBothBranches(Tree::TYPE_LINEAR/**, Tree::LINKED_FIRST */);
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame(explode(',', 'A,A.1,A.2,A.2.I,A.2.II,A.2.III,A.2.III.a,A.2.III.b,A.3,A.3.I,B,B.1,C,4,4.I,4.II,5,5.I'),
            $branches->pluck('object.title')->toArray());

        // First unlinked.
        $branches = $tree->getBothBranches(null, Tree::FIRST_UNLINKED);

        // Tree type.
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame([ '4', '5', 'A', 'B', 'C' ], $branches->pluck('object.title')->toArray());

        // Linear type.
        $branches = $tree->getBothBranches(Tree::TYPE_LINEAR, Tree::FIRST_UNLINKED);
        static::assertInstanceOf(Collection::class, $branches);
        static::assertSame(explode(',', '4,4.I,4.II,5,5.I,A,A.1,A.2,A.2.I,A.2.II,A.2.III,A.2.III.a,A.2.III.b,A.3,A.3.I,B,B.1,C'),
            $branches->pluck('object.title')->toArray());
    }

    /**
     * Test if Collection B works correctly.
     * @coversNothing
     */
    public function testCollectionB()
    {
        $tree     = new Tree(self::getCollectionB());
        $branches = $tree->getBothBranches(Tree::TYPE_LINEAR);

        // ID: 3
        $branch = $branches->get(3);
        static::assertSame($branches->get(2), $branch->parent);
        static::assertSame($branches->get(1), $branch->base);
        static::assertSame($branches->get(1), $branch->root);
        static::assertSame('Level 3', $branch->object->title);
        static::assertNull($branch->children);

        // ID: 2
        $branch = $branches->get(2);
        static::assertSame($branches->get(1), $branch->parent);
        static::assertSame($branches->get(1), $branch->base);
        static::assertSame($branches->get(1), $branch->root);
        static::assertSame('Level 2', $branch->object->title);
        static::assertEquals(collect([
            3 => $branches->get(3),
        ]), $branch->children);

        // ID: 1
        $branch = $branches->get(1);
        static::assertNull($branch->parent);
        static::assertSame($branch, $branch->base);
        static::assertSame($branch, $branch->root);
        static::assertSame('Level 1', $branch->object->title);
        static::assertEquals(collect([
            2 => $branches->get(2),
        ]), $branch->children);
    }

    /**
     * Test if Collection C treats self-parented element as unlinked element without parent.
     * @coversNothing
     */
    public function testCollectionC()
    {
        $tree     = new Tree(self::getCollectionC());
        $branches = $tree->getUnlinkedBranch();

        // ID: 1
        $branch = $branches->get(1);
        static::assertNotNull($branch);
        static::assertNull($branch->parent);
        static::assertSame($branch, $branch->base);
        static::assertNull(null, $branch->root);
        static::assertSame('Own Parent?', $branch->object->title);
        static::assertNull($branch->children);
    }
}
