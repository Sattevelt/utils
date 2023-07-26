<?php

namespace Geo\Distance;

/**
 * Utility to calculate the distance between two points on a sphere or spherical ellipsoid.
 * Can use two different algorithms to calculate. In day to day use there is barely any difference. Only when
 * calculating distances of two points < 1 meters apart does the difference in algorithms become noticeable.
 * The default algorithm should be fine for most use cases.
 *
 * A number of convenience methods are provided to accept a varienty of different presentations of two lat/long pairs.
 * Use whichever is most convenient for you.
 *
 * All distance methods return the distance in rotation degrees. A method is provided to convert to Km.
 *
 * Usage example:
 * $distInDegrees = GeoDistance::fromLatLng(53.556, 6.492, 50.750, 5.9149, GeoDistanceAlgo::Havesine);
 * $distInKm = GeoDistance::deg2km($distInDegrees);
 *
 * @see https://en.wikipedia.org/wiki/Great-circle_distance
 */
class GeoDistance
{
    /** @var float Conversion factor between degrees of rotation and kilometers */
    private const DEG2KM = 111.045;

    public static function deg2km(float $degrees)
    {
        return $degrees * static::DEG2KM;
    }

    /**
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @param GeoDistanceAlgo|null $algo
     * @return float Distance in rotation degrees.
     * @see static::deg2km() to convert to Km.
     */
    public static function fromLatLng(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
        ?GeoDistanceAlgo $algo = GeoDistanceAlgo::Havesine
    ): float {
        return static::fromPoints(
            new Point($lat1, $lng1),
            new Point($lat2, $lng2),
            $algo
        );
    }

    /**
     * Assumes both array arguments hold latitude at key 0 and longitude at key 1.
     * @param array $point1
     * @param array $point2
     * @param GeoDistanceAlgo|null $algo
     * @return float Distance in rotation degrees.
     * @see static::deg2km() to convert to Km.
     */
    public static function fromArray(
        array $point1,
        array $point2,
        ?GeoDistanceAlgo $algo = GeoDistanceAlgo::Havesine
    ): float {
        return static::fromPoints(
            new Point($point1[0], $point1[1]),
            new Point($point2[0], $point2[1]),
            $algo
        );
    }

    /**
     * @param Point $point1
     * @param Point $point2
     * @param GeoDistanceAlgo|null $algo
     * @return float Distance in rotation degrees.
     * @see static::deg2km() to convert to Km.
     */
    public static function fromPoints(
        Point $point1,
        Point $point2,
        ?GeoDistanceAlgo $algo = GeoDistanceAlgo::Havesine
    ): float {
        return match ($algo) {
            GeoDistanceAlgo::Havesine => static::getHaversineDist($point1, $point2),
            GeoDistanceAlgo::Vincenty => static::getVincentyDist($point1, $point2),
            default => throw new RuntimeException('Algorithm not implemented.'),
        };
    }

    /**
     * @param Point $point1
     * @param Point $point2
     * @return float
     * @see https://en.wikipedia.org/wiki/Haversine_formula
     */
    private static function getHaversineDist(Point $point1, Point $point2)
    {
        $radPoint1 = new Point(deg2rad($point1->getLat()), deg2rad($point1->getLong()));
        $radPoint2 = new Point(deg2rad($point2->getLat()), deg2rad($point2->getLong()));

        return rad2deg(
            acos(
                cos($radPoint1->getLat()) *
                cos($radPoint2->getLat()) *
                cos($radPoint2->getLong() - $radPoint1->getLong()) +
                sin($radPoint1->getLat()) * sin($radPoint2->getLat())
            )
        );
    }

    /**
     * @param Point $point1
     * @param Point $point2
     * @return float
     * @see https://en.wikipedia.org/wiki/Vincenty%27s_formulae
     */
    private static function getVincentyDist(Point $point1, Point $point2)
    {
        $radPoint1 = new Point(deg2rad($point1->getLat()), deg2rad($point1->getLong()));
        $radPoint2 = new Point(deg2rad($point2->getLat()), deg2rad($point2->getLong()));

        return rad2deg(
            atan2(
                sqrt(
                    pow(cos($radPoint2->getLat()) * sin(deg2rad($point2->getLong() - $point1->getLong())), 2) +
                    pow(
                        cos($radPoint1->getLat()) * sin($radPoint2->getLat()) -
                        (
                            sin($radPoint1->getLat()) * cos($radPoint2->getLat()) *
                            cos(deg2rad($point2->getLong() - $point1->getLong()))
                        ),
                        2
                    )
                ),
                (
                    sin($radPoint1->getLat()) *
                    sin($radPoint2->getLat()) +
                    cos($radPoint1->getLat()) *
                    cos($radPoint2->getLat()) *
                    cos(deg2rad($point2->getLong() - $point1->getLong()))
                )
            )
        );
    }
}
