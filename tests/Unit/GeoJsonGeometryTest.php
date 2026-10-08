<?php

namespace Tests\Unit;

use App\Rules\GeoJsonGeometry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GeoJsonGeometryTest extends TestCase
{
    public function test_point_and_closed_polygon_are_normalized_to_six_decimal_places(): void
    {
        $this->assertSame(['type' => 'Point', 'coordinates' => [111.520001, -7.63]], (new GeoJsonGeometry)->normalize('{"type":"Point","coordinates":[111.5200006,-7.63]}'));
        $polygon = ['type' => 'Polygon', 'coordinates' => [[[111, -7], [112, -7], [112, -8], [111, -7]]]];
        $this->assertSame([[111.0, -7.0], [112.0, -7.0], [112.0, -8.0], [111.0, -7.0]], (new GeoJsonGeometry('Polygon'))->normalize($polygon)['coordinates'][0]);
    }

    #[DataProvider('invalidGeometries')]
    public function test_invalid_geometry_is_rejected(string $type, mixed $geometry): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new GeoJsonGeometry($type))->normalize($geometry);
    }

    public static function invalidGeometries(): array
    {
        $polygon = static fn (array $ring): array => ['type' => 'Polygon', 'coordinates' => [$ring]];

        return [
            'malformed JSON' => ['Point', '{'],
            'oversize payload' => ['Point', str_repeat(' ', 65537)],
            'null' => ['Point', null],
            'wrong type' => ['Point', ['type' => 'MultiPoint', 'coordinates' => [[1, 2]]]],
            'extra authority' => ['Point', ['type' => 'Point', 'coordinates' => [1, 2], 'crs' => 'unknown']],
            'string number' => ['Point', ['type' => 'Point', 'coordinates' => ['111', -7]]],
            'boolean number' => ['Point', ['type' => 'Point', 'coordinates' => [true, -7]]],
            'infinite number' => ['Point', ['type' => 'Point', 'coordinates' => [INF, -7]]],
            'nan number' => ['Point', ['type' => 'Point', 'coordinates' => [NAN, -7]]],
            'extra dimension' => ['Point', ['type' => 'Point', 'coordinates' => [111, -7, 5]]],
            'longitude range' => ['Point', ['type' => 'Point', 'coordinates' => [181, -7]]],
            'latitude range' => ['Point', ['type' => 'Point', 'coordinates' => [111, -91]]],
            'not a list' => ['Point', ['type' => 'Point', 'coordinates' => [1 => 111, 2 => -7]]],
            'unclosed ring' => ['Polygon', $polygon([[0, 0], [1, 0], [1, 1], [0, 1]])],
            'too few vertices' => ['Polygon', $polygon([[0, 0], [1, 0], [0, 0]])],
            'collinear' => ['Polygon', $polygon([[0, 0], [1, 1], [2, 2], [0, 0]])],
            'bow tie' => ['Polygon', $polygon([[0, 0], [2, 2], [0, 2], [2, 0], [0, 0]])],
            'repeated vertex' => ['Polygon', $polygon([[0, 0], [1, 0], [1, 1], [1, 0], [0, 0]])],
            'rounding collapses vertices' => ['Polygon', $polygon([[0, 0], [0.0000001, 0], [1, 1], [0, 0]])],
            'touching segment' => ['Polygon', $polygon([[0, 0], [2, 0], [2, 2], [1, 0], [0, 2], [0, 0]])],
            'holes' => ['Polygon', ['type' => 'Polygon', 'coordinates' => [[[0, 0], [1, 0], [1, 1], [0, 0]], [[0, 0], [1, 0], [1, 1], [0, 0]]]]],
            'vertex limit' => ['Polygon', $polygon(array_fill(0, 502, [0, 0]))],
        ];
    }

    public function test_rule_reports_one_validation_error_for_invalid_geometry(): void
    {
        $errors = [];
        (new GeoJsonGeometry)->validate('geometri', 'invalid', function (string $message) use (&$errors): void {
            $errors[] = $message;
        });
        $this->assertCount(1, $errors);
    }

    public function test_polygon_accepts_exactly_the_vertex_limit(): void
    {
        $ring = [];
        for ($index = 0; $index < GeoJsonGeometry::MAX_VERTICES; $index++) {
            $angle = 2 * M_PI * $index / GeoJsonGeometry::MAX_VERTICES;
            $ring[] = [111.52 + 0.05 * cos($angle), -7.63 + 0.05 * sin($angle)];
        }
        $ring[] = $ring[0];
        $geometry = (new GeoJsonGeometry('Polygon'))->normalize(['type' => 'Polygon', 'coordinates' => [$ring]]);

        $this->assertCount(501, $geometry['coordinates'][0]);
        $this->assertSame($geometry['coordinates'][0][0], $geometry['coordinates'][0][500]);
    }
}
