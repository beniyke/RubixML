<?php

namespace Rubix\ML\Tests\Graph\Nodes;

use Rubix\ML\Graph\Nodes\Node;
use Rubix\ML\Graph\Nodes\BinaryNode;
use Rubix\ML\Graph\Nodes\Traits\HasBinaryChildren;
use PHPUnit\Framework\TestCase;

class BinaryNodeTest extends TestCase
{
    /**
     * @var \Rubix\ML\Graph\Nodes\BinaryNode
     */
    protected $node;

    public function setUp() : void
    {
        $this->node = new class implements BinaryNode {
            use HasBinaryChildren;
        };
    }

    public function test_build_node() : void
    {
        $this->assertInstanceOf(BinaryNode::class, $this->node);
        $this->assertInstanceOf(Node::class, $this->node);

        $this->assertEquals(1, $this->node->height());
        $this->assertEquals(0, $this->node->balance());
        $this->assertNull($this->node->left());
        $this->assertNull($this->node->right());
        $this->assertTrue($this->node->leaf());
    }

    public function test_attach_left_child() : void
    {
        $this->node->attachLeft(new class implements BinaryNode {
            use HasBinaryChildren;
        });

        $node = $this->node->left();

        $this->assertNotNull($node);
        $this->assertInstanceOf(BinaryNode::class, $node);
        $this->assertEquals(2, $this->node->height());
        $this->assertEquals(-1, $this->node->balance());
        $this->assertEquals(1, $node->height());
        $this->assertFalse($this->node->leaf());
        $this->assertTrue($node->leaf());
    }

    public function test_attach_right_child() : void
    {
        $this->node->attachRight(new class implements BinaryNode {
            use HasBinaryChildren;
        });

        $node = $this->node->right();

        $this->assertNotNull($node);
        $this->assertInstanceOf(BinaryNode::class, $node);
        $this->assertEquals(2, $this->node->height());
        $this->assertEquals(1, $this->node->balance());
        $this->assertEquals(1, $node->height());
        $this->assertFalse($this->node->leaf());
        $this->assertTrue($node->leaf());
    }

    public function test_detach_left_child() : void
    {
        $this->node->attachLeft(new class implements BinaryNode {
            use HasBinaryChildren;
        });

        $this->assertNotNull($this->node->left());
        $this->assertInstanceOf(BinaryNode::class, $this->node->left());

        $this->node->detachLeft();

        $this->assertNull($this->node->left());
    }

    public function test_detach_right_child() : void
    {
        $this->node->attachRight(new class implements BinaryNode {
            use HasBinaryChildren;
        });

        $this->assertNotNull($this->node->right());
        $this->assertInstanceOf(BinaryNode::class, $this->node->right());

        $this->node->detachRight();

        $this->assertNull($this->node->right());
    }
}
