<?php

namespace Rubix\ML\Transformers;

use Rubix\ML\Datasets\DataFrame;
use RuntimeException;

/**
 * Max Absolute Scaler
 * 
 * Scale the sample matrix by the maximum absolute value of each feature
 * column independently such that the feature will be between -1 and 1.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class MaxAbsoluteScaler implements Transformer, Online
{
    /**
     * The maximum absolute values for each fitted feature column.
     * 
     * @var array|null
     */
    protected $maxabs;

    /**
     * Return the maximum absolute values for each feature column.
     * 
     * @return array|null
     */
    public function maxabs() : ?array
    {
        return $this->maxabs;
    }

    /**
     * Fit the transformer to the incoming data frame.
     *
     * @param  \Rubix\ML\Datasets\DataFrame  $dataframe
     * @return void
     */
    public function fit(DataFrame $dataframe) : void
    {
        $this->maxabs = [];

        foreach ($dataframe->types() as $column => $type) {
            if ($type === DataFrame::CONTINUOUS) {
                $this->maxabs[$column] = -INF;
            }
        }

        $this->update($dataframe);
    }

    /**
     * Update the fitting of the transformer.
     *
     * @param  \Rubix\ML\Datasets\DataFrame  $dataframe
     * @return void
     */
    public function update(DataFrame $dataframe) : void
    {
        if (is_null($this->maxabs)) {
            $this->fit($dataframe);
            return;
        }

        foreach ($this->maxabs as $column => $oldMax) {
             $values = $dataframe->column($column);

             $max = max(array_map('abs', $values));

             $max = max($oldMax, $max);

             $this->maxabs[$column] = $max ?: self::EPSILON;
        }
    }

    /**
     * Apply the transformation to the sample matrix.
     *
     * @param  array  $samples
     * @throws \RuntimeException
     * @return void
     */
    public function transform(array &$samples) : void
    {
        if (is_null($this->maxabs)) {
            throw new RuntimeException('Transformer has not been fitted.');
        }

        foreach ($samples as &$sample) {
            foreach ($sample as $column => &$feature) {
                $feature /= $this->maxabs[$column];
            }
        }
    }
}