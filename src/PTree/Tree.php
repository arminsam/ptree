<?php

namespace PTree;

use Exception;

class Tree
{
    /**
     * @var Node[]
     */
    protected $nodes = [];

    /**
     * @var Node|null
     */
    protected $root = null;

    /**
     * Tree constructor.
     * @param Node $root
     */
    public function __construct(Node $root = null)
    {
        if (! is_null($root)) {
            $this->setRoot($root);
        }
    }

    /**
     * @param Node $root
     * @return $this
     * @throws Exception
     */
    public function setRoot(Node $root)
    {
        if (isset($this->root)) {
            throw new Exception('The root node is already set.', 1);
        }

        $this->nodes[$root->getId()] = $root;
        $this->root = $root;

        return $this;
    }

    /**
     * @return Node
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param Node $parent
     * @param Node $node
     * @return $this
     * @throws Exception
     */
    public function addNode(Node $parent, Node $node)
    {
        if (! array_key_exists($parent->getId(), $this->nodes)) {
            throw new Exception('The given parent node does not exist in the tree.', 1);
        }

        if (array_key_exists($node->getId(), $this->nodes)) {
            throw new Exception('The given node already exists in the tree.', 2);
        }

        $parent->addChild($node);
        $this->nodes[$node->getId()] = $node;

        return $this;
    }

    /**
     * @param Node $node
     * @return $this
     * @throws Exception
     */
    public function removeNode(Node $node)
    {
        if (! array_key_exists($node->getId(), $this->nodes)) {
            throw new Exception('The given node does not exist in the tree.', 1);
        }

        if ($this->isRoot($node)) {
            throw new Exception('The root node cannot be removed.', 2);
        }

        if ($node->hasChildren()) {
            array_map([$this, 'removeNode'], $node->getChildren());
        }

        $node->unlink();
        unset($this->nodes[$node->getId()]);

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return count($this->nodes);
    }

    /**
     * @param $id
     * @return null|Node
     */
    public function getNode($id)
    {
        if ($this->hasNode($id)) {
            return $this->nodes[$id];
        }

        return null;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasNode($id)
    {
        return isset($this->nodes[$id]);
    }

    /**
     * @return array
     */
    public function getNodeList()
    {
        return $this->nodes;
    }

    /**
     * @param Node $node
     * @return mixed
     */
    public function isRoot(Node $node)
    {
        return $this->root->equals($node);
    }
}