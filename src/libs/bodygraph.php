<?php

  require_once __DIR__ . '/constants.php';
  require_once __DIR__ . '/utils.php';

  date_default_timezone_set('UTC');
  swe_set_ephe_path(dirname(__DIR__, 1) . '/data/ephemeris');

  class Bodygraph {
    private $date;
    private $time;
    private $timezone;
    private $bdt_julian_utc;
    private $ddt_julian_utc;

    private $bodygraph = [
      'birth_datetime' => null,
      'birth_datetime_julian_utc' => null,
      'design_datetime' => null,
      'design_datetime_julian_utc' => null,
      'profile' => [null, null],
      'type' => null,
      'authority' => null,
      'incarnation_cross' => null,
      'channels' => [],
      'defined_centers' => [],
      'activations' => [
        'personality' => [],
        'design' => []
      ]
    ];

    function __construct($date, $time, $timezone = ['offset' => 0]) {
      $this->date = $date;
      $this->time = $time;
      $this->timezone = $timezone;
    }

    // Astrology
    private function _getEphemerisForCelestialBody($date, $celestial_body) {
      // TODO: Check for errors!
      $cb_position = swe_calc_ut($date, $celestial_body[0], SEFLG_SPEED);
      $longitude = $cb_position[0];
      $longitude += $celestial_body[1];

      if ($longitude > 360) {
        $longitude -= 360;
      }

      $ephemeris = [
        'longitude' => $longitude,
        // 'latitude' => $cb_position[1],
        // '???' => $cb_position[2],
        // 'speed' => $cb_position[3]
      ];
    
      return $ephemeris;
    }

    // I Ching
    private function _getActivationsForLongitude($longitude) {      
      // MARK: Human Design gates start at Gate 41 at 02º00'00" Aquarius, so we have to adjust from 00º00'00" Aries. The distance is 58º00'00" exactly.
      $degreesFloat = $longitude + 58;

      if ($degreesFloat >= 360) {
        $degreesFloat -= 360;
      }
    
      $percentageThrough = $degreesFloat / 360;
      $exactLine = 64 * 6 * $percentageThrough;
      $exactColor = 2304 * $percentageThrough;
      $exactTone = 13824 * $percentageThrough;
      $exactBase = 69120 * $percentageThrough;
    
      $activations = [
        // MARK: Raw values
        'gate' => GATES_IN_ORDER[floor($percentageThrough * 64)],
        'line'=> floor((intval($exactLine) % 6) + 1),
        'color'=> floor((intval($exactColor) % 6) + 1),
        'tone'=> floor((intval($exactTone) % 6) + 1),
        'base'=> floor((intval($exactBase) % 5) + 1)
        // MARK: Metadata
        // 'gate-index'=> floor($percentageThrough * 64),
      ];

      return $activations;
    }

    // Human Design
    private function _generateBirthDateTime($date, $time, $timezone) {
      $birth_datetime = new DateTime(
        $date['year'] . '-' . $date['month'] . '-' . $date['day'] . ' ' . $time['hour'] . ':' . $time['min'] . ':0', 
        new DateTimeZone('UTC')
      );

      $utc_offset = -1 * floatval($timezone['offset']);
      $birth_datetime->modify(($utc_offset < 0 ? '' : '+')  . $utc_offset . ' hour');

      $birth_datetime_julian_utc = swe_julday(
        $birth_datetime->format('Y'),
        $birth_datetime->format('n'),
        $birth_datetime->format('j'),
        ($birth_datetime->format('H') + ($birth_datetime->format('i') / 60)),
        1
      );

      return [$birth_datetime, $birth_datetime_julian_utc];
    }
    
    private function _generateDesignDateTime($date, $time, $timezone) {
      // TODO: Do this differently?
      $cb_position = swe_calc_ut(self::_generateBirthDateTime($date, $time, $timezone)[1], SE_SUN, SEFLG_SPEED);
      $personality_sun_longitude = $cb_position[0];
      $search_sun_longitude = 0;

      $search_datetime = new DateTime(
        $date['year'] . '-' . $date['month'] . '-' . $date['day'] . ' ' . $time['hour'] . ':' . $time['min'] . ':0', 
        new DateTimeZone('UTC')
      );

      $utc_offset = -1 * floatval($timezone['offset']);
      $search_datetime->modify(($utc_offset < 0 ? '' : '+')  . $utc_offset . ' hour');

      $search_datetime->modify('-92 days');

      if ($personality_sun_longitude > 87) {
        $offset = 89;

        while ($offset > 88) {
          $search_datetime_julian_utc = swe_julday(
            $search_datetime->format('Y'),
            $search_datetime->format('n'),
            $search_datetime->format('j'),
            ($search_datetime->format('H') + ($search_datetime->format('i') / 60)),
            1
          );
    
          $cb_position = swe_calc_ut($search_datetime_julian_utc, SE_SUN, SEFLG_SPEED);
          $search_sun_longitude = $cb_position[0];

          $offset = abs($personality_sun_longitude - $search_sun_longitude);

          if ($offset > 88) {
            $search_datetime->modify('+1 hour');
          }
        }
      } else {
        $offset = 271;

        while ($offset < 272) {
          $search_datetime_julian_utc = swe_julday(
            $search_datetime->format('Y'),
            $search_datetime->format('n'),
            $search_datetime->format('j'),
            ($search_datetime->format('H') + ($search_datetime->format('i') / 60)),
            1
          );
    
          $cb_position = swe_calc_ut($search_datetime_julian_utc, SE_SUN, SEFLG_SPEED);
          $search_sun_longitude = $cb_position[0];

          $offset = abs($personality_sun_longitude - $search_sun_longitude);

          if ($offset < 272) {
            $search_datetime->modify('+1 hour');
          }
        }
      }

      $search_datetime_julian_utc = swe_julday (
        $search_datetime->format('Y'),
        $search_datetime->format('n'),
        $search_datetime->format('j'),
        ($search_datetime->format('H') + ($search_datetime->format('i') / 60)),
        1
      );

      return [$search_datetime, $search_datetime_julian_utc];
    }

    // TODO: Rename this!
    private function _generateActivations($birth_datetime_julian_utc, $design_datetime) {
      // TODO: Rename this!
      $stuff = ['personality' => $birth_datetime_julian_utc, 'design' => $design_datetime];
      $activations = [];

      foreach (CELESTIAL_BODIES as $cb_key => $cb_value) {     
        foreach ($stuff as $stuff_key => $stuff_date) {

          // Personality
          $cb_position = self::_getEphemerisForCelestialBody($stuff_date, $cb_value);
          // TODO: Rename this!
          $activations2 = self::_getActivationsForLongitude($cb_position['longitude']);
          
          $activations[$stuff_key][$cb_key] = [
            // MARK: Prettified
            // 'key' => $cb_key,
            // 'label' => $activations2['gate'] . '.' . $activations2['line'],
            // MARK: Raw values
            'gate' => $activations2['gate'],
            'line' => $activations2['line'],
            // 'color' => $activations2['color'],
            // 'tone' => $activations2['tone'],
            // 'base' => $activations2['base'],
            // MARK: Metadata
            // 'longitude' => $cb_position['longitude'],
            // 'gate-index' => $activations2['gate-index'],
          ];
        }
      }

      return $activations;
    }

    private function _generateProfile($personality_sun_line, $design_sun_line) {
      $profile = [$personality_sun_line, $design_sun_line];
      return $profile;
    }

    private function _generateChannels($personality_activations, $design_activations) {
      $activated_personality_gates = [];
      $activated_design_gates = [];
      $activated_gates = [];
      
      // Personality gates
      foreach ($personality_activations as $cb_key => $cb_value) {
        $activated_personality_gates[] = $cb_value['gate'];
      }
      
      $activated_personality_gates = array_unique($activated_personality_gates);
      sort($activated_personality_gates);
            
      // Design gates
      foreach ($design_activations as $cb_key => $cb_value) {
        $activated_design_gates[] = $cb_value['gate'];
      }
      
      $activated_design_gates = array_unique($activated_design_gates);
      sort($activated_design_gates);
      
      // Combined gates
      $activated_gates = array_merge($activated_personality_gates, $activated_design_gates);
      $activated_gates = array_unique($activated_gates);
      sort($activated_gates);
      
      // Channels
      $channels = [];
      
      foreach ($activated_gates as $gate) {
        foreach(HARMONIC_GATES[$gate] as $harmonic_gate) {
          if (in_array($harmonic_gate, $activated_gates)) {
            $channels[] = [
              min($gate, $harmonic_gate),
              max($gate, $harmonic_gate)
            ];
          }
        }
      }

      $unique_channels = [];
      foreach ($channels as $channel) {
        $key = implode(',', $channel);
        $unique_channels[$key] = $channel;
      }

      $unique_channels = array_values($unique_channels);

      return $unique_channels;
    }

    private function _generateDefinedCenters($channels) {
      $defined_centers = [];

      foreach ($channels as $channel) {
        $key = $channel[0] . '-' . $channel[1];

        foreach(DEFINED_CENTERS_FOR_CHANNEL[$key] as $defined_center) {
          $defined_centers[] = BODYGRAPH_CENTERS[$defined_center];
        }
      }

      $defined_centers = array_unique($defined_centers);
      sort($defined_centers);

      return $defined_centers;
    }

    private function _getMotorToThroat($channels, $defined_centers) {
      $flat_channels = [];
      foreach ($channels as $channel) {
        $key = implode('-', $channel);
        $flat_channels[] = $key;
      }

      function some($haystack_array, $needle_array) {
        foreach ($needle_array as $element) {
            if (in_array($element, $haystack_array)) {
                return true;
            }
        }

        return false;
      }

      // TODO: CHECK IF THOSE IDENTIFIERS MATCH!

      if (!some($defined_centers, ['throat']) || !some($defined_centers, ['emotion', 'sacral', 'root', 'ego'])) {
        // Throat is undefined and/or motor is undefined
        return false;
      }

      // Solar Plexus
      if (some($defined_centers, ['emotion'])) {
        if (some($flat_channels, ['12-22', '35-36'])) {
          return true;
        }
      }

      // Sacral
      if (some($defined_centers, ['sacral'])) {
        if (some($flat_channels, ['20-34'])) {
          return true;
        }

        if (some($flat_channels, ['2-14', '5-15', '29-46'])) {
          // G is defined
          if (some($flat_channels, ['1-8', '7-31', '10-20', '13-33'])) {
            return true;
          }
        }

        if (some($flat_channels, ['27-50'])) {
          // Spleen is defined
          if (some($flat_channels, ['16-48', '20-57'])) {
            return true;
          }
          
          if (some($flat_channels, ['10-57'])) {
            // G is defined
            if (some($flat_channels, ['1-8', '7-31', '10-20', '13-33'])) {
              return true;
            }
          }
        }
      }

      // Ego
      if (some($defined_centers, ['ego'])) {
        if (some($flat_channels, ['21-45'])) {
          return true;
        }

        if (some($flat_channels, ['25-51'])) {
          // G is defined
          if (some($flat_channels, ['1-8', '7-31', '10-20', '13-33'])) {
            return true;
          }

          if (some($flat_channels, ['10-57'])) {
            // Spleen is defined
            if (some($flat_channels, ['16-48', '20-57'])) {
              return true;
            }
          }
        }

        if (some($flat_channels, ['26-44'])) {
          // Spleen is defined
          if (some($flat_channels, ['16-48', '20-57'])) {
            return true;
          }
        }
      }

      // Root
      if (some($defined_centers, ['root'])) {
        if (some($flat_channels, ['18-58', '28-38', '32-54'])) {
          // Spleen is defined
          if (some($flat_channels, ['16-48', '20-57'])) {
            return true;
          }
        }

        if (some($flat_channels, ['10-57'])) {
          // G is defined
          if (some($flat_channels, ['1-8', '7-31', '10-20', '13-33'])) {
            return true;
          }
        }
      }
      
      return false;
    }

    private function _generateType($channels, $defined_centers) {
      $type = null;

      if (count($defined_centers) === 0) {
        $type = BODYGRAPH_TYPES['REFLECTOR'];
        return $type;
      }

      if (in_array('sacral', $defined_centers)) {
        $type = self::_getMotorToThroat($channels, $defined_centers) ? BODYGRAPH_TYPES['MANIFESTING_GENERATOR'] : BODYGRAPH_TYPES['GENERATOR'];
        return $type;
      }

      if (self::_getMotorToThroat($channels, $defined_centers)) {
        $type = BODYGRAPH_TYPES['MANIFESTOR'];
        return $type;
      } 

      $type = BODYGRAPH_TYPES['PROJECTOR'];
      return $type;
    }

    private function _generateAuthority($defined_centers) {
      $authority = null;

      if (count($defined_centers) === 0) {
        $authority = BODYGRAPH_AUTHORITIES['LUNAR'];
        return $authority;
      } 

      foreach (AUTHORITY_FOR_CENTER as $center_key => $center_label) {
        if (in_array(BODYGRAPH_CENTERS[$center_key], $defined_centers)) {
          $authority = BODYGRAPH_AUTHORITIES[AUTHORITY_FOR_CENTER[$center_key]];
          return $authority;
        }
      }

      if ($authority === null) {
        $authority = BODYGRAPH_AUTHORITIES['ENVIRONMENTAL'];
      }      

      return $authority;
    }

    private function _generateIncarnationCross($personality_sun_gate, $personality_earth_gate, $design_sun_gate, $design_earth_gate) {
      $incarnation_cross = [
        [$personality_sun_gate, $personality_earth_gate],
        [$design_sun_gate, $design_earth_gate]     
      ];
      return $incarnation_cross;
    }

    private function generateBodygraph() {
      $birth_datetimes = self::_generateBirthDateTime($this->date, $this->time, $this->timezone);
      $this->bdt_julian_utc = $birth_datetimes[1];
      $this->bodygraph['birth_datetime'] = $birth_datetimes[0]->format('Y-m-d H:i:s T');
      $this->bodygraph['birth_datetime_julian_utc'] = $birth_datetimes[1];

      $design_datetimes = self::_generateDesignDateTime($this->date, $this->time, $this->timezone);
      $this->ddt_julian_utc = $design_datetimes[1];
      $this->bodygraph['design_datetime'] = $design_datetimes[0]->format('Y-m-d H:i:s T');
      $this->bodygraph['design_datetime_julian_utc'] = $design_datetimes[1];

      $this->bodygraph['activations'] = self::_generateActivations($this->bdt_julian_utc, $this->ddt_julian_utc);
      $this->bodygraph['profile'] = self::_generateProfile($this->bodygraph['activations']['personality']['SUN']['line'], $this->bodygraph['activations']['design']['SUN']['line']);
      $this->bodygraph['channels'] = self::_generateChannels($this->bodygraph['activations']['personality'], $this->bodygraph['activations']['design']);
      $this->bodygraph['defined_centers'] = self::_generateDefinedCenters($this->bodygraph['channels']);
      $this->bodygraph['type'] = self::_generateType($this->bodygraph['channels'], $this->bodygraph['defined_centers']);
      $this->bodygraph['authority'] = self::_generateAuthority($this->bodygraph['defined_centers']);

      // TODO: Implement?!
      // $this->bodygraph['undefined_centers'] = self::_generateUndefinedCenters();
      // $this->bodygraph['definition'] = self::_generateDefinition();
      // $this->bodygraph['circuitry'] = self::_generateCircuitry();
      $this->bodygraph['incarnation_cross'] = self::_generateIncarnationCross(
        $this->bodygraph['activations']['personality']['SUN']['gate'], 
        $this->bodygraph['activations']['personality']['EARTH']['gate'], 
        $this->bodygraph['activations']['design']['SUN']['gate'], 
        $this->bodygraph['activations']['design']['EARTH']['gate']
      );
    }

    public function getBodygraph() {
      $this->generateBodygraph();
      return $this->bodygraph;
    }

    public function DEBUG() {
      return 'Hello world!';
    }
  }