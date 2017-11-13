<?php

namespace PTree;

use Exception;
use ReflectionClass;
use ReflectionMethod;

class Tree
{
    /**
     * List of all nodes in the tree
     *
     * @var Node[]
     */
    protected $nodes;

    /**
     * List of all leaf nodes
     *
     * @var Node[]
     */
    protected $leaves;

    /**
     * The root node of the tree
     *
     * @var Node
     */
    protected $root;

    /**
     * Create a new tree.
     *
     * @param Node|null $root
     */
    public function __construct(Node $root = null)
    {
        if (! is_null($root)) {
            $this->setRoot($root);
        }
    }

    /**
     * Add a child node to the given parent in the tree.
     *
     * @param Node $node
     * @param Node|null $parent
     * @throws Exception
     */
    public function addNode(Node $node, Node $parent = null)
    {
        if (is_null($parent)) {
            $this->setRoot($node);
            return;
        }

        if (! isset($this->nodes[$parent->getId()])) {
            throw new Exception("The given parent node {$parent->getId()} does not exist in the tree.", 1003);
        }

        if (isset($this->nodes[$node->getId()])) {
            throw new Exception("The given node {$node->getId()} already exists in the tree.", 1004);
        }

        self::getMethod('addChild')->invokeArgs($parent, [$node]);
        $this->nodes[$node->getId()] = $node;
    }

    /**
     * Remove a node and all its children from the tree.
     *
     * @param Node $node
     * @throws Exception
     */
    public function removeNode(Node $node)
    {
        if (! isset($this->nodes[$node->getId()])) {
            throw new Exception("The given node {$node->getId()} does not exist in the tree.", 1005);
        }

        if ($this->isRoot($node)) {
            throw new Exception("The root node cannot be removed.", 1006);
        }

        $this->removeChildrenNodes($node);

        self::getMethod('remove')->invokeArgs($node, []);
        unset($this->nodes[$node->getId()]);
    }

    /**
     * Remove all children of the given parent node in the tree.
     *
     * @param Node $parent
     */
    public function removeChildrenNodes(Node $parent)
    {
        array_map([$this, 'removeNode'], $parent->getChildren());
    }

    /**
     * Get the root node of the tree.
     *
     * @return Node
     */
    public function getRootNode()
    {
        return $this->root;
    }

    /**
     * Get a list of all leaf nodes of the tree.
     *
     * @return Node[]
     */
    public function getLeafNodes()
    {
        return array_filter($this->nodes, function(Node $node) {
            return ! $node->hasChildren() && ! $node->equals($this->getRootNode());
        });
    }

    /**
     * Get a list of all non-leaf nodes of the tree.
     *
     * @return Node[]
     */
    public function getNonLeafNodes()
    {
        return array_filter($this->nodes, function(Node $node) {
            return $node->hasChildren();
        });
    }

    /**
     * Get all nodes of the tree on the given depth level (if null, returns all).
     *
     * @param int $depth
     * @return Node[]
     */
    public function getAllNodes($depth = null)
    {
        if (is_null($depth)) {
            return $this->nodes;
        }

        return array_filter($this->nodes, function($node) use ($depth) {
            return $this->getDepth($node) === $depth;
        });
    }

    /**
     * Get a node from the tree based on the given id.
     *
     * @param string $id
     * @return Node|null
     * @throws Exception
     */
    public function getNode($id)
    {
        if (! $this->hasNode($id)) {
            return null;
        }

        return $this->nodes[$id];
    }

    /**
     * Determine if the tree has a node with the given id.
     *
     * @param string $id
     * @return bool
     */
    public function hasNode($id)
    {
        return isset($this->nodes[$id]);
    }

    /**
     * Get the total number of nodes in the tree.
     *
     * @return int
     */
    public function getSize()
    {
        return count($this->nodes);
    }

    /**
     * Get the length of the path from the given node to the root.
     *
     * @param Node $node
     * @return int
     */
    public function getDepth(Node $node)
    {
        return self::getMethod('getDepth')->invokeArgs($node, []);
    }

    /**
     * Get the length of the longest path from the given node to a leaf.
     *
     * @param Node $node
     * @return int
     */
    public function getHeight(Node $node)
    {
        return self::getMethod('getHeight')->invokeArgs($node, []);
    }

    /**
     * Determine if the given node is the root of the tree.
     *
     * @param Node $node
     * @return bool
     */
    public function isRoot(Node $node)
    {
        if (is_null($this->root) || $this->root->getId() !== $node->getId()) {
            return false;
        }

        return true;
    }

    /**
     * Set the root node of the tree.
     *
     * @param Node $root
     * @throws Exception
     */
    protected function setRoot(Node $root)
    {
        if (! is_null($this->root)) {
            throw new Exception("The root node has already been set.", 1002);
        }

        $this->root = $root;
        $this->nodes[$root->getId()] = $root;
    }

    /**
     * This method is used to call protected methods of Node class, like addChild() or remove().
     *
     * @param string $name
     * @return ReflectionMethod
     */
    protected static function getMethod($name) {
        $class = new ReflectionClass(Node::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}