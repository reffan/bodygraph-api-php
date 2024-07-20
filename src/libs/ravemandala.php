<?php

  // require_once __DIR__ . '/constants.php';
  require_once __DIR__ . '/utils.php';

  // TODO: SPLIT OUT!
  function move_elements_to_top(SimpleXMLElement $xml, $ids) {
    $dom = dom_import_simplexml($xml);
    $xpath = new DOMXPath($dom->ownerDocument);

    $nodes = [];

    foreach ($ids as $id) {
        $node = $xpath->query("//*[@id='$id']")->item(0);
        if ($node) {
            $nodes[] = $node;
        }
    }

    foreach ($nodes as $node) {
      if ($node->parentNode) {
        $parent = $node->parentNode;

        $parent->removeChild($node);
        $parent->appendChild($node);
      }
    }
  }
  
  class RaveMandala {
    private $base_mandala;
    private $mandala;
    private $bodygraph;

    // TODO: SPLIT OUT!
    private $colors = [
      'pink_a' => '#FFFAFA',
      'pink_b' => '#F8D5D3',
      'pink_c' => '#FDB3AF',
      'pink_d' => '#683431',
      'pink_text' => '#1D1924',
      'vanilla_a' => '#FFFCFA',
      'vanilla_b' => '#F4D6B9',
      'vanilla_c' => '#FDC287',
      'vanilla_d' => '#684D31',
      'vanilla_text' => '#221924',
      'forest_a' => '#FAFFFA',
      'forest_b' => '#CCE0CC',
      'forest_c' => '#BBF1BB',
      'forest_d' => '#3F5A3F',
      'forest_text' => '#241C19',
      'ocean_a' => '#FAFEFF',
      'ocean_b' => '#C3E2EA',
      'ocean_c' => '#8EB8C2',
      'ocean_d' => '#315D68',
      'ocean_text' => '#1F2419'
    ];

    private $theme = [];
    
    function __construct($bodygraph) {
      $this->base_mandala = file_get_contents(dirname(__DIR__, 1) . '/data/ravemandala/ravemandala.svg');
      $this->bodygraph = $bodygraph;

      // TODO: SPLIT OUT!
      $this->theme = [
        'body' => [
          $this->colors['pink_d'], 0.06,
          $this->colors['pink_d'], 0,
        ],
        'centers' => [
          'ajna' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'crown' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'ego' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'g' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'root' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'sacral' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'emotion' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'spleen' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
          'throat' => [ 
            'active' => [ $this->colors['vanilla_b'], $this->colors['vanilla_b'] ],
            'inactive' => [ $this->colors['vanilla_a'], $this->colors['vanilla_b'] ] 
          ],
        ],
        'gates' => [
          'active' => [ 
            // $this->colors['vanilla_text'], $this->colors['vanilla_text'], $this->colors['vanilla_a'] 
            $this->colors['vanilla_text'], $this->colors['vanilla_text'], 'white' 
          ],
          'inactive' => [ 
            // $this->colors['vanilla_a'], $this->colors['vanilla_a'], $this->colors['vanilla_text'] 
            'white', 'white', $this->colors['vanilla_text'] 
          ],
        ],
        'channels' => [
          'active' => [ $this->colors['ocean_c'] ],
          'inactive' => [ 
            // $this->colors['ocean_text']
            'white'
          ],
        ],
      ];
    }

    // TODO: SPLIT UP!
    private function _processMandala($mandala, $bodygraph) {
      $new_mandala = new SimpleXMLElement($mandala);
      $new_mandala->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');

      // BODY
      $body = $new_mandala->xpath('//svg:g[@id="body_v1"]');
      if (isset($body[0])) {

        $count = 0;

        foreach ($body[0]->children() as $subelement) {

          switch ($count) {
            case 0:
              break;
              case 1:
              $attribute = 'fill';
              $subelement->attributes()->$attribute = $this->theme['body'][0];

              $attribute = 'fill-opacity';
              $subelement->attributes()->$attribute = $this->theme['body'][1];
              break;
            case 2:
              $attribute = 'fill';
              $subelement->attributes()->$attribute = $this->theme['body'][2];
      
              $attribute = 'fill-opacity';
              $subelement->attributes()->$attribute = $this->theme['body'][3];
              break;
          }

          $count++;
        }
      }

      // CHANNELS
      $channels = $new_mandala->xpath('//svg:g[@id="channels"]');
      if (isset($channels[0])) {
        $personality_gates = array_values(array_map(function ($celestial_body) {
          return $celestial_body['gate'];
        }, $bodygraph['activations']['personality']));
        sort($personality_gates);

        $activated_channels = [];

        foreach ($channels[0]->children() as $element) {
          $element_id = (string) $element->attributes()['id'];
          $raw_id = str_replace('channel_', '', $element_id);
          $raw_id = explode('_', $raw_id);
          $raw_id = array_map(function ($gate) { return (int) $gate; }, $raw_id );

          $count = 0;

          foreach ($element[0]->children() as $subelement) {

            switch ($count) {
              case 0:       
                $attribute = 'stroke';
                $subelement->attributes()->$attribute = $this->theme['channels']['inactive'][0];
                break;
            }

            // Active?
            if (in_array($raw_id, $bodygraph['channels'])) {
              switch ($count) {
                case 0:       
                  $attribute = 'stroke';
                  $subelement->attributes()->$attribute = $this->theme['channels']['active'][0];
                  break;
              }
            }

            $count++;
          }

          // Active?
          if (in_array($raw_id, $bodygraph['channels'])) {
            array_push($activated_channels, $element_id);
          }
        }

        move_elements_to_top($new_mandala, $activated_channels);
      }

      // CENTERS
      $centers = $new_mandala->xpath('//svg:g[@id="centers"]');
      if (isset($centers[0])) {

        foreach ($centers[0]->children() as $element) {
          $element_id = (string) $element->attributes()['id'];
          $raw_id = str_replace('center_', '', $element_id);

          $attribute = 'fill';
          $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['inactive'][0];
          
          $attribute = 'stroke';
          $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['inactive'][1];
          
          // Active?
          if (in_array($raw_id, $bodygraph['defined_centers'])) {
            $attribute = 'fill';
            $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['active'][0];

            $attribute = 'stroke';
          $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['active'][1];
          }
        }
      }

      // GATES
      $gates = $new_mandala->xpath('//svg:g[@id="gates"]');
      if (isset($gates[0])) {

        $personality_gates = array_values(array_map(function ($celestial_body) {
          return $celestial_body['gate'];
        }, $bodygraph['activations']['personality']));
        sort($personality_gates);

        $design_gates = array_values(array_map(function ($celestial_body) {
          return $celestial_body['gate'];
        }, $bodygraph['activations']['design']));
        sort($design_gates);

        foreach ($gates[0]->children() as $element) {
          $element_id = (string) $element->attributes()['id'];
          $raw_id = str_replace('gate_', '', $element_id);
          
          $count = 0;

          foreach ($element[0]->children() as $subelement) {
            switch ($count) {
              case 0:
                $attribute = 'fill';
                $subelement->attributes()->$attribute = $this->theme['gates']['inactive'][0];
                break;
              case 1:
                $attribute = 'stroke';
                $subelement->attributes()->$attribute = $this->theme['gates']['inactive'][1];
                break;
              case 2:
                $attribute = 'fill';
                $subelement->attributes()->$attribute = $this->theme['gates']['inactive'][2];
                break;
            }

            // Active?
            if (in_array((int) $raw_id, $personality_gates) || in_array((int) $raw_id, $design_gates)) {
              switch ($count) {
                case 0:
                  $attribute = 'fill';
                  $subelement->attributes()->$attribute = $this->theme['gates']['active'][0];
                  break;
                case 1:
                  $attribute = 'stroke';
                  $subelement->attributes()->$attribute = $this->theme['gates']['active'][1];
                  break;
                case 2:
                  $attribute = 'fill';
                  $subelement->attributes()->$attribute = $this->theme['gates']['active'][2];
                  break;
              }
            }

            $count++;
          }
          
        }
      }

      // return $new_mandala->asXML();
      $domxml = dom_import_simplexml($new_mandala);
      return $domxml->ownerDocument->saveXML($domxml->ownerDocument->documentElement);
    }

    public function getRaveMandala() {
      $this->mandala = self::_processMandala($this->base_mandala, $this->bodygraph);
      return $this->mandala;
    }

    public function DEBUG() {
      return 'Hello world!';
    }

  }