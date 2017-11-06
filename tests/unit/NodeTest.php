<?php

use PHPUnit\Framework\TestCase;
use PTree\Node;

class NodeTest extends TestCase
{
    public function testGetId()
    {
        $node = new Node('node', 100);
        $this->assertEquals(100, $node->getId());

        $mock = $this->getMockBuilder('AnyClass')
            ->setMethods(['getHash'])
            ->getMock();
        $mock->expects($this->once())
            ->method('getHash')
            ->will($this->returnValue(100));

        $node = new Node($mock);
        $this->assertEquals(100, $node->getId());
    }

    public function testGetValue()
    {
        $node = new Node('node');
        $this->assertEquals('node', $node->getValue());
    }

    public function testHasChildren()
    {
        $parent = new Node('parent');
        $child = new Node('child');
        $parent->addChild($child);

        $this->assertTrue($parent->hasChildren());
        $this->assertFalse($child->hasChildren());
    }

    public function testGetChildren()
    {
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);

        $this->assertArrayHasKey($child1->getId(), $parent->getChildren());
        $this->assertArrayHasKey($child2->getId(), $parent->getChildren());
    }

    public function getParent()
    {
        $parent = new Node('parent');
        $child = new Node('child');
        $parent->addChild($child);

        $this->assertEquals($child->getParent()->getId(), $parent->getId());
        $this->assertNull($parent->getParent());
    }

    public function testChildrenCount()
    {
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);

        $this->assertEquals(2, $parent->childrenCount());
        $this->assertEquals(0, $child1->childrenCount());
    }

    public function testUnlink()
    {
        $parent = new Node('parent');
        $child1 = new Node('child1');
        $child2 = new Node('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);

        $child2->unlink();
        $this->assertEquals(1, $parent->childrenCount());
        $this->assertArrayHasKey($child1->getId(), $parent->getChildren());
    }

    public function testEquals()
    {
        $node1 = new Node('node1');
        $node2 = new Node('node2');

        $this->assertNotTrue($node1->equals($node2));

        $node1 = new Node('node1', 100);
        $node2 = new Node('node2', 100);

        $this->assertTrue($node1->equals($node2));
    }
}