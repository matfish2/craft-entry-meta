<?php

namespace matfish\EntryMeta\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EntryMetaExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('keyForHumans', [$this, 'getKeyForHumans']),
            new TwigFilter('valueForHumans', [$this, 'getValueForHumans'])
        ];
    }

    public function getKeyForHumans($key)
    {
        $key = ucfirst($key);

        if (str_contains($key, '_')) { // Snake case
            $res = implode(' ', explode('_', $key));

            return mb_convert_case($res, MB_CASE_TITLE, "UTF-8");
        }

        // Camel case
        $key = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $key);

        return $key;

    }

    public function getValueForHumans($value)
    {
        if (is_array($value)) {
            return '<code>' . json_encode($value) . '</code>';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

}