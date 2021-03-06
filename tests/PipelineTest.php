<?php

namespace Rubix\ML\Tests;

use Rubix\ML\Online;
use Rubix\ML\Wrapper;
use Rubix\ML\Verbose;
use Rubix\ML\Pipeline;
use Rubix\ML\DataType;
use Rubix\ML\Estimator;
use Rubix\ML\Persistable;
use Rubix\ML\Probabilistic;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Other\Loggers\BlackHole;
use Rubix\ML\Datasets\Generators\Blob;
use Rubix\ML\Classifiers\SoftmaxClassifier;
use Rubix\ML\Transformers\PolynomialExpander;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Datasets\Generators\Agglomerate;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Transformers\VarianceThresholdFilter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PipelineTest extends TestCase
{
    protected const TRAIN_SIZE = 300;
    protected const TEST_SIZE = 10;
    protected const MIN_SCORE = 0.8;

    protected const RANDOM_SEED = 0;

    /**
     * @var \Rubix\ML\Datasets\Generators\Agglomerate
     */
    protected $generator;

    /**
     * @var \Rubix\ML\Pipeline
     */
    protected $estimator;

    /**
     * @var \Rubix\ML\CrossValidation\Metrics\Accuracy
     */
    protected $metric;

    public function setUp() : void
    {
        $this->generator = new Agglomerate([
            'red' => new Blob([255, 0, 128], 30.),
            'green' => new Blob([0, 128, 0], 10.),
            'blue' => new Blob([64, 32, 255], 20.),
        ]);

        $this->estimator = new Pipeline([
            new VarianceThresholdFilter(),
            new PolynomialExpander(2),
            new ZScaleStandardizer(),
        ], new SoftmaxClassifier(), true);

        $this->estimator->setLogger(new BlackHole());

        $this->metric = new Accuracy();

        srand(self::RANDOM_SEED);
    }

    public function test_build_meta_estimator() : void
    {
        $this->assertInstanceOf(Pipeline::class, $this->estimator);
        $this->assertInstanceOf(Online::class, $this->estimator);
        $this->assertInstanceOf(Wrapper::class, $this->estimator);
        $this->assertInstanceOf(Probabilistic::class, $this->estimator);
        $this->assertInstanceOf(Verbose::class, $this->estimator);
        $this->assertInstanceOf(Persistable::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);

        $this->assertSame(Estimator::CLASSIFIER, $this->estimator->type());

        $this->assertNotContains(DataType::CATEGORICAL, $this->estimator->compatibility());
        $this->assertContains(DataType::CONTINUOUS, $this->estimator->compatibility());
    }

    public function test_train_partial_predict() : void
    {
        $training = $this->generator->generate(self::TRAIN_SIZE);

        $testing = $this->generator->generate(self::TEST_SIZE);

        $folds = $training->stratifiedFold(3);

        $this->estimator->train($folds[0]);
        $this->estimator->partial($folds[1]);
        $this->estimator->partial($folds[2]);

        $this->assertTrue($this->estimator->trained());

        $predictions = $this->estimator->predict($testing);

        $score = $this->metric->score($predictions, $testing->labels());

        $this->assertGreaterThanOrEqual(self::MIN_SCORE, $score);
    }

    public function test_predict_untrained() : void
    {
        $this->expectException(RuntimeException::class);

        $this->estimator->predict(Unlabeled::quick());
    }
}
