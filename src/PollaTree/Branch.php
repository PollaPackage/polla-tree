<?php

namespace Rentalhost\PollaTree;

use Illuminate\Support\Collection;

/**
 * Class Branch
 * @package Rentalhost\PollaTree
 */
class Branch
{
    /**
     * Object reference.
     * @var mixed
     */
    public $object;

    /**
     * Reference to parent branch.
     * @var self
     */
    public $parent;

    /**
     * Reference to root branch.
     * @var self
     */
    public $root;

    /**
     * Reference to base branch.
     * @var self
     */
    public $base;

    /**
     * Reference to children branches.
     * @var Collection|null
     */
    public $children;

    /**
     * Distance of this branch to base branch (cache).
     * @var int
     */
    private $distance;

    /**
     * @internal
     * Branch constructor.
     *
     * @param object $object Object reference.
     */
    public function __construct($object)
    {
        $this->object = $object;
        $this->root   = $object->id_parent === null ? $this : null;
    }

    /**
     * @internal
     * Set the parent branch.
     *
     * @param Branch|null $parent Parent branch.
     */
    public function setParent($parent)
    {
        if ($parent) {
            $this->parent = $parent;
            $this->root   = $parent->root;
            $this->base   = $parent->base;
        }
        else {
            $this->base = $this;
        }
    }

    /**
     * @internal
     * Set the children branches.
     *
     * @param Collection $children Children branches.
     */
    public function setChildren($children)
    {
        if ($children->count()) {
            $this->children = $children;
        }
    }

    /**
     * Get the distance of this branch to base branch.
     * Zero mean the own node, positive numbers mean how much nodes there are until base.
     * @return int
     */
    public function getDistance()
    {
        if ($this->distance === null) {
            $this->distance = 0;

            $node = $this;
            while (( $node = $node->parent ) !== null) {
                $this->distance++;
            }
        }

        return $this->distance;
    }

    /**
     * Get the distance of this branch to root.
     * Basically it will return same that base branch, except if it is unlinked, then will returns null.
     *
     * @return int|null
     */
    public function getDistanceFromRoot()
    {
        return $this->root ? $this->getDistance() : null;
    }

    /**
     * Get the children depth (zero-based).
     * It'll returns zero if not has children.
     * @return int
     */
    public function getDepth()
    {
        $depth = -1;

        if ($this->children) {
            /** @var self $child */
            foreach ($this->children as $child) {
                $depth = max($depth, $child->getDepth());
            }
        }

        return $depth + 1;
    }

    /**
     * Returns if branch is linked to root.
     * @return bool
     */
    public function isLinked()
    {
        return $this->root !== null;
    }

    /**
     * Returns if branch is a root branch.
     * By nature it'll not work with unlinked branches.
     * To check if it is most closest from root, use `isBase()` method.
     * @return bool
     */
    public function isRoot()
    {
        return $this->root === $this;
    }

    /**
     * Returns if branch is a base branch.
     * @return bool
     */
    public function isBase()
    {
        return $this->base === $this;
    }
}
