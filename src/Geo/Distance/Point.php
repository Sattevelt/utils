<?php

namespace Geo\Distance;

class Point
{
    public function __construct(
        public float $lat,
        public float $long
    ) {}

    /**
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getLong(): float
    {
        return $this->long;
    }
}