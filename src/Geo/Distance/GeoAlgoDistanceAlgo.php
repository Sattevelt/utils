<?php

namespace Geo\Distance;

enum GeoDistanceAlgo
{
    // Cheap but fractionally less accurate (max deviation is 0,5%)
    case Havesine;
    // More expensive but also more accurate. Only noticable from distances < 1m. Deviation @ 1m: 3mm.
    case Vincenty;
}
