<?php

namespace Rentalhost\PollaTree;

use Illuminate\Support\Collection;

/**
 * Class Tree
 * @package Rentalhost\PollaTree
 */
class Tree
{
    /**
     * Priority of link type when get both branches together.
     * @var string
     */
    const FIRST_LINKED = 'linked';
    const FIRST_UNLINKED = 'unlinked';
    const TYPE_LINEAR = 'linear';

    /**
     * Type of resulting collection constants.
     * @var string
     */
    const TYPE_TREE = 'tree';

    /**
     * Collection to work.
     * @var Collection
     */
    private $collection;

    /**
     * If collection was processed.
     * @var bool
     */
    private $processed = false;

    /**
     * Tree constructor.
     *
     * @param Collection $collection Collection to create tree.
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Reorder the collection by children order and push to container.
     *
     * @param Collection $container Container to store reordered elements.
     * @param Branch     $branch    Branch to reorder.
     */
    private static function reorderCollection($container, $branch)
    {
        // Push own branch to container.
        $container->put($branch->object->id, $branch);

        // Push each branch children.
        if ($branch->children) {
            /** @var Branch[] $branchesChildren */
            $branchesChildren = $branch->children;
            foreach ($branchesChildren as $branchChildren) {
                self::reorderCollection($container, $branchChildren);
            }
        }
    }

    /**
     * Returns root-linked and unlinked branches together.
     * By default it'll returns first linked then unlinked branches.
     *
     * @param string $type         Type of returned collection.
     * @param string $linkPriority Priority of link type.
     *
     * @return Collection
     */
    public function getBothBranches($type = self::TYPE_TREE, $linkPriority = self::FIRST_LINKED)
    {
        $firstCollectionMethod = 'getLinkedBranch';
        $lastCollectionMethod  = 'getUnlinkedBranch';

        if ($linkPriority === self::FIRST_UNLINKED) {
            $tempCollectionMethodSwift = $firstCollectionMethod;
            $firstCollectionMethod     = $lastCollectionMethod;
            $lastCollectionMethod      = $tempCollectionMethodSwift;
        }

        /** @var Collection $collection */
        /** @var Collection $branches */
        $collection = call_user_func([ $this, $firstCollectionMethod ], $type);
        $branches   = call_user_func([ $this, $lastCollectionMethod ], $type);

        foreach ($branches as $branch) {
            $collection->put($branch->object->id, $branch);
        }

        return $collection;
    }

    /**
     * Returns only root-linked branches.
     *
     * @param string|null $type Type of returned collection.
     *
     * @return Collection
     */
    public function getLinkedBranch($type = self::TYPE_TREE)
    {
        // Return a root-linked tree collection.
        // It'll return only branches from root, basically.
        if ($type === null || $type === self::TYPE_TREE) {
            $self = $this;

            return $this->getProcessedCollection()->filter(function ($branch) use ($self) {
                return $branch->root === $branch;
            });
        }

        // Return a root-linked linear collection.
        // Basically will return branches with root defined.
        return $this->getProcessedCollection()->filter(function ($branch) {
            return $branch->root !== null;
        });
    }

    /**
     * Returns a collection that is not linked to any root branch.
     *
     * @param string $type Type of returned collection.
     *
     * @return Collection
     */
    public function getUnlinkedBranch($type = self::TYPE_TREE)
    {
        // Return a unlinked tree collection.
        // It'll return only branches without root branch defined, but that still are base branches, basically.
        if ($type === null || $type === self::TYPE_TREE) {
            $self = $this;

            return $this->getProcessedCollection()->filter(function ($branch) use ($self) {
                return $branch->root === null &&
                       $branch->base === $branch;
            });
        }

        // Return a unlinked linear collection.
        // Basically will return branches without root.
        return $this->getProcessedCollection()->filter(function ($branch) {
            return $branch->root === null;
        });
    }

    /**
     * Process collection and return it.
     * @return Collection
     */
    private function getProcessedCollection()
    {
        /** @var Branch $branch */
        if (!$this->processed) {
            $branches   = new Collection();
            $collection = new Collection();

            // Prepare branches collection reference.
            foreach ($this->collection as $node) {
                $node->branch = new Branch($node);
                $branches->put($node->id, $node->branch);
            }

            // Get all branches and mark it as processing.
            /** @var Branch[] $branchesProcessing */
            $branchesProcessing = $branches->pluck('object.id')->toArray();

            // Work until process all branches.
            // Define branches parent, root, base and children references.
            while ($branchesProcessing) {
                foreach ($branchesProcessing as $processingKey => $branchKey) {
                    $branch = $branches->get($branchKey);

                    // If element points to self-parent, then will set parent to null.
                    if ($branch->object->id_parent === $branch->object->id) {
                        $branch->object->id_parent = null;
                    }

                    // If it's not a root element, check if parent exists on branches,
                    // If it exists, check if it was processed. If not, will skip for now.
                    if ($branch->object->id_parent !== null &&
                        $branches->has($branch->object->id_parent) &&
                        in_array($branch->object->id_parent, $branchesProcessing, true)
                    ) {
                        continue;
                    }

                    // Process branch.
                    $branch->setParent($branches->get($branch->object->id_parent));
                    $branch->setChildren($branches->filter(function ($subBranch) use ($branch) {
                        return $subBranch->object->id_parent === $branch->object->id;
                    }));

                    // Remove from processing branch.
                    unset( $branchesProcessing[$processingKey] );
                }
            }

            // Reorder root-based elements by children order.
            foreach ($branches as $branch) {
                if ($branch->root === $branch) {
                    self::reorderCollection($collection, $branch);
                }
            }

            // Reorder unlinked elements by children order.
            foreach ($branches as $branch) {
                if ($branch->root === null && $branch->base === $branch) {
                    self::reorderCollection($collection, $branch);
                }
            }

            $this->collection = $collection;
            $this->processed  = true;
        }

        return $this->collection;
    }
}
