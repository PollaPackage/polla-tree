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
     * Collect base descendants respecting max depth, storing in the linear container.
     *
     * @param Collection $container     Linear container.
     * @param Branch     $base          Base branch.
     * @param int|null   $maxDepth      Max depth to respect.
     * @param boolean    $includeItself If should include the own base as a descendant.
     */
    private static function collectDescendants($container, $base, $maxDepth, $includeItself)
    {
        // If max depth is negative, then we need cancel the collect process.
        if ($maxDepth < 0) {
            return;
        }

        // Include the own base as descendant.
        if ($includeItself === true) {
            $container->push($base);
        }

        // Collect descendants.
        if ($base->children) {
            /** @var self[] $baseChildren */
            $baseChildren = $base->children;
            foreach ($baseChildren as $child) {
                self::collectDescendants($container, $child, $maxDepth === null ? null : $maxDepth - 1, true);
            }
        }
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
            /** @var self[] $children */
            $children = $this->children;
            foreach ($children as $child) {
                $depth = max($depth, $child->getDepth());
            }
        }

        return $depth + 1;
    }

    /**
     * Get all branch descentants, in any depth, as linear.
     *
     * @param int|null $maxDepth      Depth limit (default is unlimited, null).
     * @param bool     $includeItself Include own branch as descendant.
     *
     * @return Collection
     */
    public function getDescendants($maxDepth = null, $includeItself = false)
    {
        $descentantsContainer = collect();

        self::collectDescendants($descentantsContainer, $this, $maxDepth, $includeItself);

        return $descentantsContainer;
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
     * Returns if branch is a base branch.
     * @return bool
     */
    public function isBase()
    {
        return $this->base === $this;
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
}
