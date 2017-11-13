<?php

use PHPUnit\Framework\TestCase;
use PTree\Node;
use PTree\Tree;

class TreeTest extends TestCase
{
    protected $exceptionThrown = false;

    public function setUp()
    {
        parent::setUp();
        $this->exceptionThrown = false;
    }

    public function testAddNode()
    {
        // add the root node of the tree
        $root = new Node('root');
        $tree = new Tree($root);
        self::assertTrue($root->equals($tree->getRootNode()));

        // add the root node of the tree when it already has a root node
        $root2 = new Node('root2');
        try {
            $tree->addNode($root2);
        } catch (Exception $e) {
            self::assertEquals(1002, $e->getCode());
            $this->exceptionThrown = true;
        }
        self::assertTrue($this->exceptionThrown, 'Did not receive expected exception.');
        $this->exceptionThrown = false;

        // add a node to the tree which the parent node does not exist
        $node = new Node('node');
        try {
            $tree->addNode($node, $root2);
        } catch (Exception $e) {
            self::assertEquals(1003, $e->getCode());
            $this->exceptionThrown = true;
        }
        self::assertTrue($this->exceptionThrown, 'Did not receive expected exception.');
        $this->exceptionThrown = false;

        // add a node to the tree
        $tree->addNode($node, $root);
        self::assertNotEmpty($tree->getNode($node->getId()));
        self::assertTrue($node->getParent()->equals($root));
        self::assertTrue(array_key_exists($node->getId(), $root->getChildren()));

        // add an already existing node to the tree
        try {
            $tree->addNode($node, $root);
        } catch (Exception $e) {
            self::assertEquals(1004, $e->getCode());
            $this->exceptionThrown = true;
        }
        self::assertTrue($this->exceptionThrown, 'Did not receive expected exception.');
    }

    public function testRemoveNode()
    {
        // remove a node that does not exist
        $tree = new Tree();
        $root = new Node('root');
        $node = new Node('node');
        $tree->addNode($root);
        try {
            $tree->removeNode($node);
        } catch (Exception $e) {
            self::assertEquals(1005, $e->getCode());
            $this->exceptionThrown = true;
        }
        self::assertTrue($this->exceptionThrown, 'Did not receive expected exception.');
        $this->exceptionThrown = false;

        // remove the root node
        try {
            $tree->removeNode($root);
        } catch (Exception $e) {
            self::assertEquals(1006, $e->getCode());
            $this->exceptionThrown = true;
        }
        self::assertTrue($this->exceptionThrown, 'Did not receive expected exception.');

        // remove a node from the tree
        $tree->addNode($node, $root);
        $tree->removeNode($node);
        self::assertNull($tree->getNode($node->getId()));
        self::assertNull($node->getParent());
        self::assertTrue(! array_key_exists($node->getId(), $root->getChildren()));
    }

    public function testRemoveChildrenNodes()
    {
        $tree = new Tree();
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $tree->addNode($root);
        $tree->addNode($node1, $root);
        $tree->addNode($node2, $root);
        self::assertEquals(2, $root->childrenCount());

        $tree->removeChildrenNodes($root);
        self::assertEquals(0, $root->childrenCount());
    }

    public function testGetRootNode()
    {
        $tree = new Tree();
        $root = new Node('root');
        $tree->addNode($root);
        self::assertTrue($root->equals($tree->getRootNode()));
    }

    public function testGetLeafNodes()
    {
        // create a tree with 3 leaf nodes and 2 non-leaf nodes
        $tree = new Tree();
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $node3 = new Node('node3');
        $node4 = new Node('node4');
        $tree->addNode($root);
        $tree->addNode($node1, $root);
        $tree->addNode($node2, $root);
        $tree->addNode($node3, $node1);
        $tree->addNode($node4, $node1);

        self::assertEquals(3, count($tree->getLeafNodes()));
        self::assertTrue(in_array($node2, $tree->getLeafNodes()));
        self::assertTrue(in_array($node3, $tree->getLeafNodes()));
        self::assertTrue(in_array($node4, $tree->getLeafNodes()));
        self::assertFalse(in_array($root, $tree->getLeafNodes()));
        self::assertFalse(in_array($node1, $tree->getLeafNodes()));
    }

    public function testGetNonLeafNodes()
    {
        // create a tree with 3 leaf nodes and 2 non-leaf nodes
        $tree = new Tree();
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $node3 = new Node('node3');
        $node4 = new Node('node4');
        $tree->addNode($root);
        $tree->addNode($node1, $root);
        $tree->addNode($node2, $root);
        $tree->addNode($node3, $node1);
        $tree->addNode($node4, $node1);

        self::assertEquals(2, count($tree->getNonLeafNodes()));
        self::assertFalse(array_key_exists($node2->getId(), $tree->getNonLeafNodes()));
        self::assertFalse(array_key_exists($node3->getId(), $tree->getNonLeafNodes()));
        self::assertFalse(array_key_exists($node4->getId(), $tree->getNonLeafNodes()));
        self::assertTrue(array_key_exists($root->getId(), $tree->getNonLeafNodes()));
        self::assertTrue(array_key_exists($node1->getId(), $tree->getNonLeafNodes()));
    }

    public function testGetAllNodes()
    {
        // get all nodes in depth 0, 1, 2
        $tree = new Tree();
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $node3 = new Node('node3');
        $node4 = new Node('node4');
        $tree->addNode($root);
        $tree->addNode($node1, $root);
        $tree->addNode($node2, $root);
        $tree->addNode($node3, $node1);
        $tree->addNode($node4, $node1);
        self::assertEquals(1, count($tree->getAllNodes(0)));
        self::assertEquals(2, count($tree->getAllNodes(1)));
        self::assertEquals(2, count($tree->getAllNodes(2)));
        self::assertTrue(array_key_exists($root->getId(), $tree->getAllNodes(0)));
        self::assertTrue(array_key_exists($node1->getId(), $tree->getAllNodes(1)));
        self::assertTrue(array_key_exists($node2->getId(), $tree->getAllNodes(1)));
        self::assertTrue(array_key_exists($node3->getId(), $tree->getAllNodes(2)));
        self::assertTrue(array_key_exists($node4->getId(), $tree->getAllNodes(2)));

        // get all nodes in the entire tree
        self::assertEquals(5, count($tree->getAllNodes()));
        self::assertTrue(array_key_exists($root->getId(), $tree->getAllNodes()));
        self::assertTrue(array_key_exists($node1->getId(), $tree->getAllNodes()));
        self::assertTrue(array_key_exists($node2->getId(), $tree->getAllNodes()));
        self::assertTrue(array_key_exists($node3->getId(), $tree->getAllNodes()));
        self::assertTrue(array_key_exists($node4->getId(), $tree->getAllNodes()));
    }

    public function testGetNode()
    {
        $tree = new Tree();
        $root = new Node('root');
        $node = new Node('node');
        $tree->addNode($root);
        $tree->addNode($node, $root);

        // if node does not exist, we should receive null
        self::assertNull($tree->getNode('100'));
        self::assertTrue($node->equals($tree->getNode($node->getId())));
    }

    public function testHasNode()
    {
        $tree = new Tree();
        $root = new Node('root');
        $node = new Node('node');
        $tree->addNode($root);
        $tree->addNode($node, $root);

        self::assertFalse($tree->hasNode('100'));
        self::assertTrue($tree->hasNode($node->getId()));
    }

    public function testGetSize()
    {
        $tree = new Tree();
        $root = new Node('root');
        $node = new Node('node');
        self::assertEquals(0, $tree->getSize());
        $tree->addNode($root);
        self::assertEquals(1, $tree->getSize());
        $tree->addNode($node, $root);
        self::assertEquals(2, $tree->getSize());
    }

    public function testGetDepth()
    {
        $tree = new Tree();
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $node3 = new Node('node3');
        $node4 = new Node('node4');
        $tree->addNode($root);
        $tree->addNode($node1, $root);
        $tree->addNode($node2, $root);
        $tree->addNode($node3, $node1);
        $tree->addNode($node4, $node3);

        self::assertEquals(0, $tree->getDepth($root));
        self::assertEquals(1, $tree->getDepth($node1));
        self::assertEquals(1, $tree->getDepth($node2));
        self::assertEquals(2, $tree->getDepth($node3));
        self::assertEquals(3, $tree->getDepth($node4));
    }

    public function testGetHeight()
    {
        $tree = new Tree();
        $root = new Node('root');
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        $node3 = new Node('node3');
        $node4 = new Node('node4');
        $tree->addNode($root);
        $tree->addNode($node1, $root);
        $tree->addNode($node2, $root);
        $tree->addNode($node3, $node1);
        $tree->addNode($node4, $node3);

        self::assertEquals(3, $tree->getHeight($root));
        self::assertEquals(2, $tree->getHeight($node1));
        self::assertEquals(0, $tree->getHeight($node2));
        self::assertEquals(1, $tree->getHeight($node3));
        self::assertEquals(0, $tree->getHeight($node4));

        $tree->removeNode($node4);
        self::assertEquals(0, $tree->getHeight($node3));
        self::assertEquals(1, $tree->getHeight($node1));
        self::assertEquals(2, $tree->getHeight($root));

        $tree->removeNode($node1);
        self::assertEquals(1, $tree->getHeight($root));
    }

    public function testIsRoot()
    {
        $tree = new Tree();
        $root = new Node('root');
        $node = new Node('node');
        $tree->addNode($root);
        $tree->addNode($node, $root);

        self::assertTrue($tree->isRoot($root));
        self::assertFalse($tree->isRoot($node));
    }
}