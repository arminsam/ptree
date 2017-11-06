<?php

namespace PTree;

class Node
{
    /**
     * @var
     */
    private $id;

    /**
     * @var
     */
    private $value;

    /**
     * @var Node|null
     */
    private $parent = null;

    /**
     * @var Node[]
     */
    private $children = [];

    /**
     * Node constructor.
     * @param $value
     * @param null $id
     */
    public function __construct($value, $id = null)
    {
        $this->value = $value;
        $this->setId($value, $id);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Node $node
     */
    public function addChild(Node $node)
    {
        $this->children[$node->getId()] = $node;
        $node->parent = $this;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return ! empty($this->children);
    }

    /**
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return null|Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function childrenCount()
    {
        return count($this->children);
    }

    /**
     * @return $this
     */
    public function unlink()
    {
        if (! is_null($this->getParent())) {
            $this->getParent()->removeChild($this);
        }

        $this->parent = null;

        return $this;
    }

    /**
     * @param Node $node
     */
    public function removeChild(Node $node)
    {
        unset($this->children[$node->getId()]);
    }

    /**
     * @param Node $node
     * @return bool
     */
    public function equals(Node $node)
    {
        return $this->getId() === $node->getId();
    }

    /**
     * @param $value
     * @param $id
     */
    private function setId($value, $id)
    {
        if (! empty($id)) {
            $this->id = $id;
        } elseif (is_object($value) && method_exists($value, 'getHash')) {
            $this->id = $value->getHash();
        } else {
            $this->id = uniqid();
        }
    }
}