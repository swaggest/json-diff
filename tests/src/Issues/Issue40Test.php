<?php

namespace Swaggest\JsonDiff\Tests\Issues;

use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;

class Issue40Test extends \PHPUnit_Framework_TestCase
{
    public function testIssue() {
        $json1 = <<<JSON
    {
      "type": "FeatureCollection",
      "features": [
        {
          "type": "Feature",
          "properties": {},
          "geometry": {
            "type": "Polygon",
            "coordinates": [
              [
                [
                  0.582275390625,
                  43.04881979669318
                ],
                [
                  0.97503662109375,
                  43.04881979669318
                ],
                [
                  0.97503662109375,
                  43.29120116988416
                ],
                [
                  0.582275390625,
                  43.29120116988416
                ],
                [
                  0.582275390625,
                  43.04881979669318
                ]
              ]
            ]
          }
        },
        {
          "type": "Feature",
          "properties": {},
          "geometry": {
            "type": "Polygon",
            "coordinates": [
              [
                [
                  0.9722900390624999,
                  42.97049193148623
                ],
                [
                  1.329345703125,
                  42.97049193148623
                ],
                [
                  1.329345703125,
                  43.29120116988416
                ],
                [
                  0.9722900390624999,
                  43.29120116988416
                ],
                [
                  0.9722900390624999,
                  42.97049193148623
                ]
              ]
            ]
          }
        },
        {
          "type": "Feature",
          "properties": {
            "stroke": "#555555",
            "stroke-width": 2,
            "stroke-opacity": 1,
            "fill": "#555555",
            "fill-opacity": 0.5
          },
          "geometry": {
            "type": "Polygon",
            "coordinates": [
              [
                [
                  0.8778762817382812,
                  42.97275278822627
                ],
                [
                  0.8205413818359375,
                  42.945365709261324
                ],
                [
                  0.8758163452148438,
                  42.9224919308288
                ],
                [
                  0.91461181640625,
                  42.94234987312984
                ],
                [
                  0.8778762817382812,
                  42.97275278822627
                ]
              ]
            ]
          }
        },
        {
          "type": "Feature",
          "properties": {
            "stroke": "#555555",
            "stroke-width": 2,
            "stroke-opacity": 1,
            "fill": "#555555",
            "fill-opacity": 0.7,
            "dinasty": "qi"
          },
          "geometry": {
            "type": "Polygon",
            "coordinates": [
              [
                [
                  0.8778762817382812,
                  42.97325518954874
                ],
                [
                  0.9156417846679686,
                  42.94234987312984
                ],
                [
                  0.9578704833984375,
                  42.934055561994754
                ],
                [
                  0.9836196899414061,
                  42.96697487803267
                ],
                [
                  0.9348678588867186,
                  42.98857645832184
                ],
                [
                  0.8778762817382812,
                  42.97325518954874
                ]
              ]
            ]
          }
        }
      ]
    }
JSON;

        $json2 = <<<JSON
    {
      "type": "FeatureCollection",
      "features": [
        {
          "type": "Feature",
          "properties": {},
          "geometry": {
            "type": "Polygon",
            "coordinates": [
              [
                [
                  0.582275390625,
                  43.04881979669318
                ],
                [
                  0.97503662109375,
                  43.04881979669318
                ],
                [
                  0.97503662109375,
                  43.29120116988416
                ],
                [
                  0.582275390625,
                  43.29120116988416
                ],
                [
                  0.582275390625,
                  43.04881979669318
                ]
              ]
            ]
          }
        },
        {
          "type": "Feature",
          "properties": {
            "stroke": "#555555",
            "stroke-width": 2,
            "stroke-opacity": 1,
            "fill": "#555555",
            "fill-opacity": 0.5
          },
          "geometry": {
            "type": "Polygon",
            "coordinates": [
              [
                [
                  0.8778762817382812,
                  42.97275278822627
                ],
                [
                  0.8205413818359375,
                  42.945365709261324
                ],
                [
                  0.8758163452148438,
                  42.9224919308288
                ],
                [
                  0.91461181640625,
                  42.94234987312984
                ],
                [
                  0.8778762817382812,
                  42.97275278822627
                ]
              ]
            ]
          }
        }
      ]
    }
JSON;

        $j1 = json_decode($json1);

        $diff = new JsonDiff($j1, json_decode($json2));
        $exportedPatch = $diff->getPatch()->export($diff->getPatch());

        $patch = JsonPatch::import($exportedPatch);

        var_dump($patch->apply($j1));
    }
}