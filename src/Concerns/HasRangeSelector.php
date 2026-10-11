<?php

namespace Coroowicaksono\ChartJsIntegration\Concerns;

use InvalidArgumentException;

trait HasRangeSelector
{
    /**
     * Range selector (dropdown) for any chart card. Call it after options() — options() replaces
     * the whole options meta.
     *
     * - custom data with $rangeKey: a change reloads the card through Nova's dashboard cards route
     *   (`/nova-api/dashboards/cards/{dashboard}?chartRanges[$rangeKey]=key`); the host renders the
     *   card for the requested range and the card with the same rangeKey replaces labels + series.
     * - model data: a change sends options.advanceFilterSelected to the endpoint; a numeric key
     *   means days, or whole months with $unit = 'month'. Any other key (e.g. 'default') keeps
     *   the latestData window.
     *
     * @param  array<int|string, string>  $options  ordered key => label
     */
    public function rangeSelector(array $options, string $selected, ?string $rangeKey = null, ?string $unit = null): self
    {
        if ($options === []) {
            throw new InvalidArgumentException('rangeSelector needs at least one option.');
        }
        if (! in_array($selected, array_map(strval(...), array_keys($options)), true)) {
            throw new InvalidArgumentException("rangeSelector has no option '$selected'.");
        }
        if ($unit !== null && ! in_array($unit, ['day', 'month'], true)) {
            throw new InvalidArgumentException("rangeSelector unit must be 'day' or 'month'.");
        }

        $meta = (array) ($this->meta['options'] ?? []);
        $meta['btnFilter'] = true;
        // Ordered pairs: a JS object would sort numeric keys first and lose the given order.
        $meta['btnFilterList'] = array_map(
            fn (int|string $key, string $label): array => [(string) $key, $label],
            array_keys($options),
            array_values($options),
        );
        $meta['btnFilterDefault'] = $selected;
        $meta['advanceFilterSelected'] = $selected;
        if ($rangeKey !== null) {
            $meta['rangeKey'] = $rangeKey;
        }
        if ($unit !== null) {
            $meta['rangeUnit'] = $unit;
        }

        return $this->withMeta(['options' => (object) $meta]);
    }
}
