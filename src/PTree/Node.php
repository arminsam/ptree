<?php

namespace PTree;

use InvalidArgumentException;

class Node
{
    /**
     * A unique id to identify this node.
     *
     * @var string
     */
    protected $id;

    /**
     * A value of any type to be stored in this node.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Depth of the node in the tree.
     *
     * @var int
     */
    protected $depth = 0;

    /**
     * Height of the node in the tree.
     *
     * @var int
     */
    protected $height = 0;

    /**
     * A reference to the parent node.
     *
     * @var Node|null
     */
    protected $parent = null;

    /**
     * A list of all children of this node.
     *
     * @var Node[]
     */
    protected $children = [];

    /**
     * Create a new node.
     *
     * @param mixed $value
     * @param string $id
     */
    public function __construct($value, $id = '')
    {
        $this->setValue($value);
        $this->setId($value, $id);
    }

    /**
     * Set the value to be stored in the node.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get the if of the node.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value stored in the node.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Determine if the node has parent.
     *
     * @return bool
     */
    public function hasParent()
    {
        return ! is_null($this->parent);
    }

    /**
     * Get the parent of this node.
     *
     * @return Node|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Determine if the node has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return ! empty($this->children);
    }

    /**
     * Get a list of children nodes.
     *
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get the child node matching the given id.
     *
     * @param $id
     * @return Node
     * @throws InvalidArgumentException
     */
    public function getChild($id)
    {
        if (! array_key_exists($id, $this->children)) {
            throw new InvalidArgumentException("Node {$this->getId()} does not have a child with id {$id}.", 1000);
        }

        return $this->children[$id];
    }

    /**
     * Get the count of children of this node.
     *
     * @return int
     */
    public function childrenCount()
    {
        return count($this->children);
    }

    /**
     * Determine if the node as siblings.
     *
     * @return bool
     */
    public function hasSiblings()
    {
        if (is_null($this->parent)) {
            return false;
        }

        return $this->parent->childrenCount() > 1;
    }

    /**
     * Get a list of sibling nodes.
     *
     * @return Node[]
     */
    public function getSiblings()
    {
        $siblings = [];

        if (! $this->hasSiblings()) {
            return $siblings;
        }

        foreach ($this->parent->getChildren() as $id => $node) {
            if ($this->getId() != $id) {
                $siblings[$id] = $node;
            }
        }

        return $siblings;
    }

    /**
     * Get the sibling node matching the given id.
     *
     * @param string $id
     * @return Node
     * @throws InvalidArgumentException
     */
    public function getSibling($id)
    {
        $siblings = $this->getSiblings();

        if (! array_key_exists($id, $siblings)) {
            throw new InvalidArgumentException("Node {$this->getId()} does not have a sibling with id {$id}.", 1001);
        }

        return $siblings[$id];
    }

    /**
     * Get the count of siblings of this node.
     *
     * @return int
     */
    public function siblingsCount()
    {
        return count($this->getSiblings());
    }

    /**
     * Determine whether this node is equal to another node.
     *
     * @param Node $node
     * @return bool
     */
    public function equals(Node $node)
    {
        return $this->getId() === $node->getId();
    }

    /**
     * Set the id of this node.
     *
     * @param mixed $value
     * @param string $id
     */
    protected function setId($value, $id)
    {
        if (! empty(trim($id))) {
            $this->id = $id;
        } elseif (is_object($value) && is_callable([$value, 'getHash'])) {
            $this->id = $value->getHash();
        } else {
            $this->id = uniqid();
        }
    }

    /**
     * Remove this node.
     */
    protected function remove()
    {
        if (! is_null($this->getParent())) {
            $this->getParent()->removeChild($this);
        }

        $this->parent = null;
    }

    /**
     * Add a child node to this node.
     *
     * @param Node $node
     */
    protected function addChild(Node $node)
    {
        $this->children[$node->getId()] = $node;
        $node->parent = $this;
        $this->setHeight(max($this->getHeight(), $node->getHeight() + 1));
        $node->setDepth($this->getDepth() + 1);
    }

    /**
     * Remove a child node from this node.
     *
     * @param Node $node
     */
    protected function removeChild(Node $node)
    {
        unset($this->children[$node->getId()]);

        $maxHeight = -1;

        foreach ($this->getChildren() as $child) {
            $maxHeight = $child->getHeight() > $maxHeight ? $child->getHeight() : $maxHeight;
        }

        $this->setHeight($maxHeight + 1);
    }

    /**
     * Set the depth of the node in the tree.
     *
     * @param int $depth
     */
    protected function setDepth($depth) {
        $this->depth = $depth;
    }

    /**
     * Get the depth of the node in the tree.
     *
     * @return int
     */
    protected function getDepth()
    {
        return $this->depth;
    }

    /**
     * Set the height of the node in the tree.
     *
     * @param int $height
     */
    protected function setHeight($height) {
        if ($this->hasParent()) {
            $this->getParent()->setHeight($height + 1);
        }

        $this->height = $height;
    }

    /**
     * Get the height of the node in the tree.
     *
     * @return int
     */
    protected function getHeight()
    {
        return $this->height;
    }
}