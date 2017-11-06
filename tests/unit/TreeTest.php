<?php

use PHPUnit\Framework\TestCase;
use PTree\Node;
use PTree\Tree;

class TreeTest extends TestCase
{
    public function testGetRoot()
    {
        $tree = new Tree();
        $this->assertNull($tree->getRoot());

        $root = new Node('root');
        $tree = new Tree($root);
        $this->assertTrue($root->equals($tree->getRoot()));
    }

    public function testSetRoot()
    {
        $tree = new Tree();
        $root = new Node('root');
        $tree->setRoot($root);
        $this->assertTrue($tree->isRoot($root));

        try {
            $tree->setRoot($root);
        } catch (Exception $e) {
            $this->assertEquals(1, $e->getCode());
            return;
        }

        $this->fail('Did not received expected exception');
    }

    public function testAddNode()
    {
        $parent = new Node('parent');
        $child = new Node('child');
        $tree = new Tree($parent);

        $tree->addNode($parent, $child);

        $this->assertEquals(2, $tree->getSize());
        $this->assertArrayHasKey($parent->getId(), $tree->getNodeList());
        $this->assertArrayHasKey($child->getId(), $tree->getNodeList());

    }

    public function testAddNodeToNonExistingParent()
    {
        $tree = new Tree();
        $parent = new Node('parent');
        $child = new Node('child');

        try {
            $tree->addNode($parent, $child);
        } catch (Exception $e) {
            $this->assertEquals(1, $e->getCode());
            return;
        }

        $this->fail('Did not received expected exception');
    }

    public function testAddNodeForExistingNode()
    {
        $parent = new Node('parent');
        $child = new Node('child');
        $tree = new Tree($parent);

        $tree->addNode($parent, $child);

        try {
            $tree->addNode($parent, $child);
        } catch (Exception $e) {
            $this->assertEquals(2, $e->getCode());
            return;
        }

        $this->fail('Did not received expected exception');
    }

    public function testRemoveNode()
    {
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $node3 = new Node('node3');

        $tree = new Tree($root);
        $tree->addNode($root, $node1)
            ->addNode($root, $node2)
            ->addNode($node1, $node3);

        $this->assertEquals(4, $tree->getSize());
        $tree->removeNode($node1);

        $this->assertEquals(2, $tree->getSize());
        $this->assertArrayNotHasKey($node1->getId(), $tree->getNodeList());
        $this->assertArrayNotHasKey($node3->getId(), $tree->getNodeList());
    }

    public function testRemoveNodeForNonExistenceNode()
    {
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $tree = new Tree($root);

        $tree->addNode($root, $node1);

        try {
            $tree->removeNode($node2);
        } catch (Exception $e) {
            $this->assertEquals(1, $e->getCode());
            return;
        }

        $this->fail('Did not received expected exception');
    }

    public function testRemoveNodeForRootNode()
    {
        $root = new Node('root');
        $tree = new Tree($root);

        try {
            $tree->removeNode($root);
        } catch (Exception $e) {
            $this->assertEquals(2, $e->getCode());
            return;
        }

        $this->fail('Did not received expected exception');
    }
}