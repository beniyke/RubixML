<?php

namespace Rubix\ML\Tests\NeuralNet\ActivationFunctions;

use Tensor\Matrix;
use Rubix\ML\NeuralNet\ActivationFunctions\LeakyReLU;
use Rubix\ML\NeuralNet\ActivationFunctions\ActivationFunction;
use PHPUnit\Framework\TestCase;
use Generator;

class LeakyReLUTest extends TestCase
{
    /**
     * @var \Rubix\ML\NeuralNet\ActivationFunctions\LeakyReLU
     */
    protected $activationFn;

    public function setUp() : void
    {
        $this->activationFn = new LeakyReLU(0.01);
    }

    public function test_build_activation_function() : void
    {
        $this->assertInstanceOf(LeakyReLU::class, $this->activationFn);
        $this->assertInstanceOf(ActivationFunction::class, $this->activationFn);
    }

    /**
     * @param \Tensor\Matrix $input
     * @param array[] $expected
     *
     * @dataProvider compute_provider
     */
    public function test_compute(Matrix $input, array $expected) : void
    {
        $activations = $this->activationFn->compute($input)->asArray();

        $this->assertEquals($expected, $activations);
    }

    /**
     * @return \Generator<array>
     */
    public function compute_provider() : Generator
    {
        yield [
            Matrix::quick([
                [1.0, -0.5, 0.0, 20.0, -10.0],
            ]),
            [
                [1.0, -0.005, 0.0, 20.0, -0.1],
            ],
        ];

        yield [
            Matrix::quick([
                [-0.12, 0.31, -0.49],
                [0.99, 0.08, -0.03],
                [0.05, -0.52, 0.54],
            ]),
            [
                [-0.0012, 0.31, -0.0049],
                [0.99, 0.08, -0.0003],
                [0.05, -0.005200000000000001, 0.54],
            ],
        ];
    }

    /**
     * @param \Tensor\Matrix $input
     * @param \Tensor\Matrix $activations
     * @param array[] $expected
     *
     * @dataProvider differentiate_provider
     */
    public function test_differentiate(Matrix $input, Matrix $activations, array $expected) : void
    {
        $derivatives = $this->activationFn->differentiate($input, $activations)->asArray();

        $this->assertEquals($expected, $derivatives);
    }

    /**
     * @return \Generator<array>
     */
    public function differentiate_provider() : Generator
    {
        yield [
            Matrix::quick([
                [1.0, -0.5, 0.0, 20.0, -10.0],
            ]),
            Matrix::quick([
                [1.0, -0.005, 0.0, 20.0, -0.1],
            ]),
            [
                [1.0, 0.01, 0.01, 1.0, 0.01],
            ],
        ];

        yield [
            Matrix::quick([
                [-0.12, 0.31, -0.49],
                [0.99, 0.08, -0.03],
                [0.05, -0.52, 0.54],
            ]),
            Matrix::quick([
                [-0.0012, 0.31, -0.0049],
                [0.99, 0.08, -0.0003],
                [0.05, -0.005200000000000001, 0.54],
            ]),
            [
                [0.01, 1.0, 0.01],
                [1.0, 1.0, 0.01],
                [1.0, 0.01, 1.0],
            ],
        ];
    }
}
