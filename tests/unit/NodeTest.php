<?php

use PHPUnit\Framework\TestCase;
use PTree\Node;

class NodeTest extends TestCase
{
    protected $exceptionThrown = false;

    public function setUp()
    {
        parent::setUp();
        $this->exceptionThrown = false;
    }

    public function testGetValue()
    {
        // set a primary type value
        $node = new Node(2);
        $node->setValue(5);
        self::assertEquals(5, $node->getValue());

        // set an object value
        $object = new stdClass();
        $object->id = 100;
        $node->setValue($object);
        self::assertEquals('stdClass', get_class($node->getValue()));
        $object = $node->getValue();
        self::assertEquals(100, $object->id);
    }

    public function testGetId()
    {
        // check auto-generated ids for primary type values
        $node1 = new Node('node1');
        $node2 = new Node('node2');
        self::assertEquals(13, strlen($node1->getId()));
        self::assertEquals(13, strlen($node2->getId()));
        self::assertNotEquals($node1->getId(), $node2->getId());

        // check manually set ids
        $node = new Node('node', 100);
        self::assertEquals(100, $node->getId());

        // check ids set by ::getHash method of the given object as value
        $mock = self::getMockBuilder('AnyClass')
            ->setMethods(['getHash'])
            ->getMock();
        $mock->expects(self::once())
            ->method('getHash')
            ->will(self::returnValue(200));
        $node = new Node($mock);
        self::assertEquals(200, $node->getId());
    }

    public function testHasParent()
    {
        // a single node should return false
        $child = new Node('child');
        self::assertFalse($child->hasParent());

        // after adding one child, it should return true
        $addChild = self::getMethod('addChild');
        $parent = new Node('parent');
        $addChild->invokeArgs($parent, [$child]);
        self::assertTrue($child->hasParent());
    }

    public function testGetParent()
    {
        // a node that belongs to a parent node, should return the parent node
        $parent = new Node('parent');
        $child = new Node('child');

        $addChild = self::getMethod('addChild');
        $addChild->invokeArgs($parent, [$child]);

        self::assertEquals($parent->getId(), $child->getParent()->getId());
    }

    public function testHasChildren()
    {
        // a single node should return false
        $parent = new Node('parent');
        self::assertFalse($parent->hasChildren());

        // after adding one child, it should return true
        $addChild = self::getMethod('addChild');
        $child = new Node('child');
        $addChild->invokeArgs($parent, [$child]);
        self::assertTrue($parent->hasChildren());
    }

    public function testGetChildren()
    {
        // a parent node with two children should return an array of two nodes with their ids as keys
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $addChild = self::getMethod('addChild');
        $addChild->invokeArgs($parent, [$child1]);
        $addChild->invokeArgs($parent, [$child2]);

        self::assertArrayHasKey($child1->getId(), $parent->getChildren());
        self::assertArrayHasKey($child2->getId(), $parent->getChildren());
    }

    public function testGetChild()
    {
        // we should be able to get a child node by its id
        $parent = new Node('parent');
        $child1 = new Node('child1', 100);
        $child2 = new Node('child2', 200);

        $addChild = self::getMethod('addChild');
        $addChild->invokeArgs($parent, [$child1]);
        $addChild->invokeArgs($parent, [$child2]);

        try {
            self::assertEquals('child1', ($parent->getChild(100)->getValue()));
            self::assertEquals('child2', ($parent->getChild(200)->getValue()));

            // and if node doesn't have a child with that id, it should throw an exception
            $parent->getChild(300);
        } catch (Exception $e) {
            self::assertEquals(1000, $e->getCode());
            $this->exceptionThrown = true;
        }

        self::assertTrue($this->exceptionThrown, 'Did not receive expected exception.');
    }

    public function testChildrenCount()
    {
        // we should be able to get the count of number of children a node has
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $addChild = self::getMethod('addChild');
        self::assertEquals(0, $parent->childrenCount());
        $addChild->invokeArgs($parent, [$child1]);
        self::assertEquals(1, $parent->childrenCount());
        $addChild->invokeArgs($parent, [$child2]);
        self::assertEquals(2, $parent->childrenCount());
    }

    public function testHasSiblings()
    {
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $addChild = self::getMethod('addChild');
        $addChild->invokeArgs($parent, [$child1]);

        // if a node has only one child, that child does not have siblings
        self::assertFalse($child1->hasSiblings());
        $addChild->invokeArgs($parent, [$child2]);

        // but if a node has multiple children, each child has siblings
        self::assertTrue($child1->hasSiblings());
        self::assertTrue($child2->hasSiblings());

        // if node does not have a parent, it does not have have siblings as well
        self::assertFalse($parent->hasSiblings());
    }

    public function testGetSiblings()
    {
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $addChild = self::getMethod('addChild');
        $addChild->invokeArgs($parent, [$child1]);

        // if a node has only one child, siblings of that child is an empty list
        self::assertEmpty($child1->getSiblings());
        $addChild->invokeArgs($parent, [$child2]);

        // but if a node has multiple children, siblings of each child is a list of other children
        self::assertArrayHasKey($child2->getId(), $child1->getSiblings());
        self::assertArrayHasKey($child1->getId(), $child2->getSiblings());
    }

    public function testGetSibling()
    {
        // we should be able to get a sibling node by its id
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $addChild = self::getMethod('addChild');
        $addChild->invokeArgs($parent, [$child1]);
        $addChild->invokeArgs($parent, [$child2]);

        $siblingId = $child2->getId();
        $invalidSiblingId = 100;

        try {
            self::assertEquals($siblingId, $child1->getSibling($siblingId)->getId());

            // and if node doesn't have a sibling with that id, it should throw an exception
            $child1->getSibling($invalidSiblingId);
        } catch (Exception $e) {
            self::assertEquals(1001, $e->getCode());
            $this->exceptionThrown = true;
        }

        self::assertTrue($this->exceptionThrown, 'Did not receive expected exception.');
    }

    public function testSiblingsCount()
    {
        // we should be able to get the count of number of siblings a node has
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');
        $child3 = new Node('child3');

        $addChild = self::getMethod('addChild');
        self::assertEquals(0, $child1->siblingsCount());
        $addChild->invokeArgs($parent, [$child1]);
        self::assertEquals(0, $child1->siblingsCount());
        $addChild->invokeArgs($parent, [$child2]);
        self::assertEquals(1, $child1->siblingsCount());
        $addChild->invokeArgs($parent, [$child3]);
        self::assertEquals(2, $child1->siblingsCount());
    }

    public function testEquals()
    {
        // if two nodes have the same id, they are considered equals
        $node1 = new Node('node1', 100);
        $node2 = new Node('node2', 100);
        $node3 = new Node('node3', 200);

        self::assertTrue($node1->equals($node2));
        self::assertFalse($node1->equals($node3));
    }

    protected static function getMethod($name) {
        $class = new ReflectionClass(Node::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}