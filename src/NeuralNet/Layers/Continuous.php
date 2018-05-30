<?php

namespace Rubix\Engine\NeuralNet\Layers;

use MathPHP\LinearAlgebra\Matrix;
use MathPHP\LinearAlgebra\MatrixFactory;
use Rubix\Engine\NeuralNet\ActivationFunctions\Identity;
use InvalidArgumentException;

class Continuous implements Output
{
    /**
     * The function that outputs the activation or implulse of each neuron.
     *
     * @var \Rubix\Engine\NeuralNet\ActivationFunctions\ActivationFunction
     */
    protected $activationFunction;

    /**
     * The L2 regularization parameter.
     *
     * @var float
     */
    protected $alpha;

    /**
     * The previous layer in the network.
     *
     * @var \Rubix\Engine\NeuralNet\Layers\Layer
     */
    protected $previous;

    /**
     * The weight matrix.
     *
     * @var \MathPHP\LinearAlgebra\Matrix
     */
    protected $weights;

    /**
     * The memoized z matrix.
     *
     * @var \MathPHP\LinearAlgebra\Matrix
     */
    protected $z;

    /**
     * The memoized output activations matrix.
     *
     * @var \MathPHP\LinearAlgebra\Matrix
     */
    protected $computed;

    /**
     * The memoized error matrix.
     *
     * @var \MathPHP\LinearAlgebra\Matrix
     */
    protected $errors;

    /**
     * The memoized gradient matrix.
     *
     * @var \MathPHP\LinearAlgebra\Matrix
     */
    protected $gradients;

    /**
     * @param  float  $alpha
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __construct(float $alpha = 1e-4)
    {
        $this->activationFunction = new Identity();
        $this->alpha = $alpha;
    }

    /**
     * @return int
     */
    public function width() : int
    {
        return 1;
    }

    /**
     * @return \MathPHP\LinearAlgebra\Matrix
     */
    public function weights() : Matrix
    {
        return $this->weights;
    }

    /**
     * @return \MathPHP\LinearAlgebra\Matrix
     */
    public function computed() : Matrix
    {
        return $this->computed;
    }

    /**
     * @return \MathPHP\LinearAlgebra\Matrix
     */
    public function errors() : Matrix
    {
        return $this->errors;
    }

    /**
     * @return \MathPHP\LinearAlgebra\Matrix
     */
    public function gradients() : Matrix
    {
        return $this->gradients;
    }

    /**
     * Initialize the layer by fully connecting each neuron to every input and
     * generating a random weight for each parameter/synapse in the layer.
     *
     * @param  \Rubix\Engine\NeuralNet\Layers\Layer  $previous
     * @return void
     */
    public function initialize(Layer $previous) : void
    {
        $this->weights = MatrixFactory::zero($this->width(),
            $previous->width())->map(function ($weight) use ($previous) {
                return $this->activationFunction
                    ->initialize($previous->width());
            });

        $this->previous = $previous;
    }

    /**
     * Compute the input sum and activation of each neuron in the layer and return
     * an activation matrix.
     *
     * @param  \MathPHP\LinearAlgebra\Matrix  $input
     * @return \MathPHP\LinearAlgebra\Matrix
     */
    public function forward(Matrix $input) : Matrix
    {
        $activations = $this->previous->forward($input);

        $this->z = $this->weights->multiply($activations);

        $this->computed = $this->activationFunction->compute($this->z);

        return $this->computed;
    }

    /**
     * Calculate the errors and gradients for each output neuron.
     *
     * @param  array  $labels
     * @return void
     */
    public function back(array $labels) : void
    {
        $errors = [[]];

        foreach ($labels as $i => $label) {
            $errors[0][$i] = ($label - $this->computed[0][$i]);
        }

        $this->errors = $this->activationFunction
            ->differentiate($this->z, $this->computed)
            ->hadamardProduct(new Matrix($errors));

        $this->gradients = $this->errors->multiply($this->previous->computed()
            ->transpose());

        $this->previous->back($this);
    }

    /**
     * Return an array with the output activations for each class.
     *
     * @return array
     */
    public function activations() : array
    {
        $activations = [];

        foreach ($this->computed->getMatrix() as $i => $neuron) {
            foreach ($neuron as $j => $activation) {
                $activations[$j][0] = $activation;
            }
        }

        return $activations;
    }

    /**
     * Update the parameters in the layer.
     *
     * @param  \MathPHP\LinearAlgebra\Matrix  $steps
     * @return void
     */
    public function update(Matrix $steps) : void
    {
        $this->weights = $this->weights->add($steps);
    }

    /**
     * Restore the parameters in the layer.
     *
     * @param  \MathPHP\LinearAlgebra\Matrix  $weights
     * @return void
     */
    public function restore(Matrix $weights) : void
    {
        $this->weights = $weights;
    }
}
