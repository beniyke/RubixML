<?php

namespace Rubix\Engine;

use Rubix\Engine\Datasets\Dataset;

interface Probabilistic extends Estimator
{
    /**
     * Output a vector of probabilities estimates.
     *
     * @param  \Rubix\Engine\Datasets\Dataset  $samples
     * @return array
     */
    public function proba(Dataset $samples) : array;
}
