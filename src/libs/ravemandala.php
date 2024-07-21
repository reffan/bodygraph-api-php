<?php

// require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/utils.php';

require_once dirname(__DIR__, 1) . '/data/ravemandala/theme.php';

  class RaveMandala {
    private $base_mandala;
    private $mandala;
    private $bodygraph;
    private $theme = [];
    
    function __construct($bodygraph) {
      $this->base_mandala = file_get_contents(dirname(__DIR__, 1) . '/data/ravemandala/ravemandala.svg');
      $this->bodygraph = $bodygraph;

      $this->theme = $GLOBALS['theme'];
    }

    private function _styleMandalaBody($mandala, $bodygraph) {
      $body = $mandala->xpath('//svg:g[@id="body_v1"]');

      if (isset($body[0])) {
        $count = 0;

        foreach ($body[0]->children() as $subelement) {

          // Base styling
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
    }

    private function _styleMandalaChannels($mandala, $bodygraph) {
      $channels = $mandala->xpath('//svg:g[@id="channels"]');

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

            // Base styling
            switch ($count) {
              case 0:       
                $attribute = 'stroke';
                $subelement->attributes()->$attribute = $this->theme['channels']['base'][0];
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

        move_xml_elements_to_top($mandala, $activated_channels);
      }
    }

    private function _styleMandalaCenters($mandala, $bodygraph) {
      $centers = $mandala->xpath('//svg:g[@id="centers"]');

      if (isset($centers[0])) {
        foreach ($centers[0]->children() as $element) {
          $element_id = (string) $element->attributes()['id'];
          $raw_id = str_replace('center_', '', $element_id);

          // Base styling
          $attribute = 'fill';
          $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['base'][0];
          
          $attribute = 'stroke';
          $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['base'][1];
          
          // Active?
          if (in_array($raw_id, $bodygraph['defined_centers'])) {
            $attribute = 'fill';
            $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['active'][0];

            $attribute = 'stroke';
          $element->attributes()->$attribute = $this->theme['centers'][$raw_id]['active'][1];
          }
        }
      }
    }

    private function _styleMandalaGates($mandala, $bodygraph) {
      $gates = $mandala->xpath('//svg:g[@id="gates"]');
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
            // Base styling
            switch ($count) {
              case 0:
                $attribute = 'fill';
                $subelement->attributes()->$attribute = $this->theme['gates']['base'][0];
                break;
              case 1:
                $attribute = 'stroke';
                $subelement->attributes()->$attribute = $this->theme['gates']['base'][1];
                break;
              case 2:
                $attribute = 'fill';
                $subelement->attributes()->$attribute = $this->theme['gates']['base'][2];
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
    }

    private function _styleRaveMandala($mandala, $bodygraph) {
      $styled_mandala = new SimpleXMLElement($mandala);
      $styled_mandala->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');

      self::_styleMandalaBody($styled_mandala, $bodygraph);
      self::_styleMandalaChannels($styled_mandala, $bodygraph);
      self::_styleMandalaCenters($styled_mandala, $bodygraph);
      self::_styleMandalaGates($styled_mandala, $bodygraph);

      $mandala_xml = dom_import_simplexml($styled_mandala);
      $mandala_svg = $mandala_xml->ownerDocument->saveXML($mandala_xml->ownerDocument->documentElement);

      return $mandala_svg;
    }

    public function getRaveMandala() {
      $this->mandala = self::_styleRaveMandala($this->base_mandala, $this->bodygraph);
      return $this->mandala;
    }

    public function DEBUG() {
      return 'Hello world!';
    }
  }