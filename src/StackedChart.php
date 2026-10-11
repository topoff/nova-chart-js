<?php

namespace Coroowicaksono\ChartJsIntegration;

use Coroowicaksono\ChartJsIntegration\Concerns\HasRangeSelector;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Nova\Card;
use Override;

class StackedChart extends Card
{
    use HasRangeSelector;

    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    #[Override]
    public function component()
    {
        return 'stacked-chart';
    }

    public function series(array $series): self
    {
        return $this->withMeta(['series' => $series]);
    }

    public function type(string $type): self
    {
        return $this->withMeta(['type' => $type]);
    }

    public function options(array $options): self
    {
        return $this->withMeta(['options' => (object) $options]);
    }

    /**
     * Custom data with several precomputed ranges: the card shows a selector (labels from $labels,
     * keyed like $rangeSeries) and switches labels + datasets client-side without a request.
     *
     * @param  array<string, array{labels: array<int, string>, series: array<int, array<string, mixed>>}>  $rangeSeries
     * @param  array<string, string>  $labels  range key => selector text
     */
    public function rangeSeries(array $rangeSeries, array $labels, string $default): self
    {
        if ($rangeSeries === []) {
            throw new InvalidArgumentException('rangeSeries must contain at least one range.');
        }
        if (! array_key_exists($default, $rangeSeries)) {
            throw new InvalidArgumentException("rangeSeries has no default range '$default'.");
        }
        // Keys as strings and order-independent: PHP turns numeric string keys into ints, and the
        // selector order may differ from the series order.
        $seriesKeys = array_map(strval(...), array_keys($rangeSeries));
        $labelKeys = array_map(strval(...), array_keys($labels));
        sort($seriesKeys);
        sort($labelKeys);
        if ($seriesKeys !== $labelKeys) {
            throw new InvalidArgumentException('rangeSeries labels must carry exactly the range keys.');
        }
        foreach ($rangeSeries as $key => $range) {
            if (! isset($range['labels'], $range['series']) || ! is_array($range['labels']) || ! is_array($range['series'])) {
                throw new InvalidArgumentException("rangeSeries['$key'] needs 'labels' and 'series' arrays.");
            }
        }
        $options = (array) ($this->meta['options'] ?? []);
        $options['rangeSeries'] = $rangeSeries;
        $options['btnFilterList'] = $labels;
        $options['btnFilterDefault'] = $default;
        $selected = $rangeSeries[$default];
        // xaxis + series mirror the default range, so clients without the selector still render.
        $options['xaxis'] = ['categories' => $selected['labels']];

        return $this->withMeta(['options' => (object) $options, 'series' => $selected['series']]);
    }

    public function animations(array $animations): self
    {
        return $this->withMeta(['animations' => $animations]);
    }

    public function title(string $title): self
    {
        return $this->withMeta(['title' => $title, 'uriKey' => Str::slug($title)]);
    }

    public function model(string $model): self
    {
        return $this->withMeta(['model' => $model]);
    }

    public function col_xaxis(string $col_xaxis): self
    {
        return $this->withMeta(['col_xaxis' => $col_xaxis]);
    }

    public function uriKey(string $uriKey)
    {
        return $this->withMeta(['uriKey' => $uriKey]);
    }

    public function join(string $joinTable, string $joinColumnFirst, string $joinEqual, string $joinColumnSecond): self
    {
        return $this->withMeta(['join' => ['joinTable' => $joinTable, 'joinColumnFirst' => $joinColumnFirst, 'joinEqual' => $joinEqual, 'joinColumnSecond' => $joinColumnSecond]]);
    }
}
