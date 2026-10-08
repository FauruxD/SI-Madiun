<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use JsonException;

class GeoJsonGeometry implements ValidationRule
{
    public const MAX_VERTICES = 500;

    public function __construct(private string $type = 'Point') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $this->normalize($value);
        } catch (InvalidArgumentException) {
            $fail($this->type === 'Point'
                ? 'Pilih satu lokasi yang valid pada peta.'
                : 'Gambar satu poligon tertutup tanpa persilangan, maksimal 500 simpul.');
        }
    }

    /** @return array{type: string, coordinates: array} */
    public function normalize(mixed $value): array
    {
        if (is_string($value)) {
            if (strlen($value) > 65536) {
                throw new InvalidArgumentException('Geometry exceeds size limit.');
            }
            try {
                $value = json_decode($value, true, 8, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new InvalidArgumentException('Invalid JSON.', previous: $exception);
            }
        }

        if (! is_array($value) || count($value) !== 2 || ($value['type'] ?? null) !== $this->type
            || ! array_key_exists('coordinates', $value) || ! is_array($value['coordinates'])) {
            throw new InvalidArgumentException('Unsupported geometry.');
        }

        if ($this->type === 'Point') {
            return ['type' => 'Point', 'coordinates' => $this->position($value['coordinates'])];
        }

        if ($this->type !== 'Polygon' || ! array_is_list($value['coordinates']) || count($value['coordinates']) !== 1
            || ! is_array($value['coordinates'][0]) || ! array_is_list($value['coordinates'][0])) {
            throw new InvalidArgumentException('Only one exterior ring is supported.');
        }

        $positions = $value['coordinates'][0];
        if (count($positions) < 4 || count($positions) > self::MAX_VERTICES + 1) {
            throw new InvalidArgumentException('Invalid vertex count.');
        }
        $ring = array_map(fn (mixed $point): array => $this->position($point), $positions);
        if ($ring[0] !== $ring[array_key_last($ring)]) {
            throw new InvalidArgumentException('Ring must be closed.');
        }

        $vertices = array_slice($ring, 0, -1);
        $seen = [];
        $area = 0.0;
        $size = count($vertices);
        for ($i = 0; $i < $size; $i++) {
            $key = json_encode($vertices[$i], JSON_THROW_ON_ERROR);
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Repeated vertex.');
            }
            $seen[$key] = true;
            $next = ($i + 1) % $size;
            $area += $vertices[$i][0] * $vertices[$next][1] - $vertices[$next][0] * $vertices[$i][1];
            for ($j = $i + 1; $j < $size; $j++) {
                if ($j === $next || ($i === 0 && $j === $size - 1)) {
                    continue;
                }
                if ($this->intersects($vertices[$i], $vertices[$next], $vertices[$j], $vertices[($j + 1) % $size])) {
                    throw new InvalidArgumentException('Self-intersecting polygon.');
                }
            }
        }
        if (abs($area) < 1e-12) {
            throw new InvalidArgumentException('Polygon has no area.');
        }

        return ['type' => 'Polygon', 'coordinates' => [$ring]];
    }

    /** @return array{0: float, 1: float} */
    private function position(mixed $position): array
    {
        if (! is_array($position) || ! array_is_list($position) || count($position) !== 2) {
            throw new InvalidArgumentException('Expected two coordinates.');
        }
        foreach ($position as $coordinate) {
            if ((! is_int($coordinate) && ! is_float($coordinate)) || ! is_finite((float) $coordinate)) {
                throw new InvalidArgumentException('Coordinates must be finite numbers.');
            }
        }
        if (abs($position[0]) > 180 || abs($position[1]) > 90) {
            throw new InvalidArgumentException('Coordinates out of range.');
        }

        return [round((float) $position[0], 6), round((float) $position[1], 6)];
    }

    /** @param array{0: float, 1: float} $a
     * @param  array{0: float, 1: float}  $b
     * @param  array{0: float, 1: float}  $c
     * @param  array{0: float, 1: float}  $d
     */
    private function intersects(array $a, array $b, array $c, array $d): bool
    {
        if (max($a[0], $b[0]) < min($c[0], $d[0]) || max($c[0], $d[0]) < min($a[0], $b[0])
            || max($a[1], $b[1]) < min($c[1], $d[1]) || max($c[1], $d[1]) < min($a[1], $b[1])) {
            return false;
        }

        $cross = static fn (array $p, array $q, array $r): float => ($q[0] - $p[0]) * ($r[1] - $p[1]) - ($q[1] - $p[1]) * ($r[0] - $p[0]);
        $on = static fn (array $p, array $q, array $r): bool => $r[0] >= min($p[0], $q[0]) && $r[0] <= max($p[0], $q[0])
            && $r[1] >= min($p[1], $q[1]) && $r[1] <= max($p[1], $q[1]);
        $abc = $cross($a, $b, $c);
        $abd = $cross($a, $b, $d);
        $cda = $cross($c, $d, $a);
        $cdb = $cross($c, $d, $b);

        return ($abc * $abd < 0 && $cda * $cdb < 0)
            || (abs($abc) < 1e-12 && $on($a, $b, $c))
            || (abs($abd) < 1e-12 && $on($a, $b, $d))
            || (abs($cda) < 1e-12 && $on($c, $d, $a))
            || (abs($cdb) < 1e-12 && $on($c, $d, $b));
    }
}
