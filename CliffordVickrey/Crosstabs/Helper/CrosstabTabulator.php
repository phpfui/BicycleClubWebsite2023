<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Helper;

use CliffordVickrey\Crosstabs\Exception\CrosstabLogicException;
use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use CliffordVickrey\Crosstabs\SourceData\CrosstabSourceDataCollection;
use CliffordVickrey\Crosstabs\Utilities\CrosstabCastingUtilities;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMath;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathInterface;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathUtilities;

use function array_flip;
use function array_intersect_key;

/**
 * @internal
 */
final readonly class CrosstabTabulator implements CrosstabTabulatorInterface
{
    private CrosstabMath $math;
    private CrosstabParamsSerializerInterface $serializer;

    /**
     * @param CrosstabParamsSerializerInterface|null $serializer
     */
    public function __construct(?CrosstabParamsSerializerInterface $serializer = null)
    {
        $this->math = new CrosstabMath();
        $this->serializer = $serializer ?? new CrosstabParamsSerializer();
    }

    /**
     * @inheritDoc
     */
    public function tabulate(
        CrosstabVariableCollection $variables,
        CrosstabSourceDataCollection $sourceData,
        int $scale = CrosstabMathInterface::DEFAULT_SCALE
    ): array {
        if (0 === count($variables)) {
            throw new CrosstabLogicException('Variables cannot be empty');
        }

        $totals = ['n' => [], 'weightedN' => []];

        foreach ($sourceData as $sourceRow) {
            $fullQuery = [];
            $elements = [];

            foreach ($variables as $variable) {
                $value = CrosstabCastingUtilities::toString($sourceRow->getValue($variable->name));

                if ('' === $value) {
                    continue 2;
                }

                $fullQuery[$variable->name] = $value;
                $elements[] = $variable->name;
            }

            $powerSet = CrosstabMathUtilities::getPowerSet($elements);

            foreach ($powerSet as $elementsInPowerSet) {
                $query = array_intersect_key($fullQuery, array_flip($elementsInPowerSet));

                $key = $this->serializer->serializeParams($query);

                if (!isset($totals['n'][$key])) {
                    $totals['n'][$key] = (float)$sourceRow->n;
                } else {
                    $totals['n'][$key] = $this->math->add(
                        $totals['n'][$key],
                        (float)$sourceRow->n,
                        $scale
                    );
                }

                if (!isset($totals['weightedN'][$key])) {
                    $totals['weightedN'][$key] = (float)$sourceRow->weightedN;
                } else {
                    $totals['weightedN'][$key] = $this->math->add(
                        $totals['weightedN'][$key],
                        (float)$sourceRow->weightedN,
                        $scale
                    );
                }
            }
        }

        return $totals;
    }
}
