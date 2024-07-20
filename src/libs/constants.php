<?php

  // Astrology
  const CELESTIAL_BODIES = [
    'SUN' => [SE_SUN, 0],
    'EARTH' => [SE_SUN, 180],
    'MOON' => [SE_MOON, 0],
    'NORTH_NODE' => [SE_TRUE_NODE, 0],
    'SOUTH_NODE' => [SE_TRUE_NODE, 180],
    'MERCURY' => [SE_MERCURY, 0],
    'VENUS' => [SE_VENUS, 0],
    'MARS' => [SE_MARS, 0],
    'JUPITER' => [SE_JUPITER, 0],
    'SATURN' => [SE_SATURN, 0],
    'URANUS' => [SE_URANUS, 0],
    'NEPTUNE' => [SE_NEPTUNE, 0],
    'PLUTO' => [SE_PLUTO, 0],
  ];


  // Human Design
  const GATES_IN_ORDER = [
    41, 19, 13, 49, 30, 55, 37, 63, 22, 36, 25, 17, 21, 51, 42, 3, 27, 24, 2, 23, 8,
    20, 16, 35, 45, 12, 15, 52, 39, 53, 62, 56, 31, 33, 7, 4, 29, 59, 40, 64, 47, 6,
    46, 18, 48, 57, 32, 50, 28, 44, 1, 43, 14, 34, 9, 5, 26, 11, 10, 58, 38, 54, 61, 60
  ];

  const HARMONIC_GATES = [
    '1' => [8],
    '2' => [14],
    '3' => [60],
    '4' => [63],
    '5' => [15],
    '6' => [59],
    '7' => [31],
    '8' => [1],
    '9' => [52],
    '10' => [20, 34, 57],
    '11' => [56],
    '12' => [22],
    '13' => [33],
    '14' => [2],
    '15' => [5],
    '16' => [48],
    '17' => [62],
    '18' => [58],
    '19' => [49],
    '20' => [10, 34, 57],
    '21' => [45],
    '22' => [12],
    '23' => [43],
    '24' => [61],
    '25' => [51],
    '26' => [44],
    '27' => [50],
    '28' => [38],
    '29' => [46],
    '30' => [41],
    '31' => [7],
    '32' => [54],
    '33' => [13],
    '34' => [10, 20, 57],
    '35' => [36],
    '36' => [35],
    '37' => [40],
    '38' => [28],
    '39' => [55],
    '40' => [37],
    '41' => [30],
    '42' => [53],
    '43' => [23],
    '44' => [26],
    '45' => [21],
    '46' => [29],
    '47' => [64],
    '48' => [16],
    '49' => [19],
    '50' => [27],
    '51' => [25],
    '52' => [9],
    '53' => [42],
    '54' => [32],
    '55' => [39],
    '56' => [11],
    '57' => [10, 20, 34],
    '58' => [18],
    '59' => [6],
    '60' => [3],
    '61' => [24],
    '62' => [17],
    '63' => [4],
    '64' => [47]
  ];


  // Bodygraph
  // MARK: Maps to external consumers key.
  // const BG_TYPE_PROJECTOR = 'projector';
  // const BG_TYPE_REFLECTOR = 'reflector';
  // const BG_TYPE_MANIFESTOR = 'manifestor';
  // const BG_TYPE_GENERATOR = 'generator';
  // const BG_TYPE_MANIFESTING_GENERATOR = 'manifesting-generator';

  // const BODYGRAPH_TYPES = [
  //   BG_TYPE_PROJECTOR,
  //   BG_TYPE_REFLECTOR,
  //   BG_TYPE_MANIFESTOR,
  //   BG_TYPE_GENERATOR,
  //   BG_TYPE_MANIFESTING_GENERATOR
  // ];

  // HMMM, THESE BELONG AT THE REPORTS?
  
  const BODYGRAPH_TYPES = [
    'GENERATOR' => 'generator',
    'MANIFESTING_GENERATOR' => 'manifesting-generator',
    'MANIFESTOR' => 'manifestor',
    'PROJECTOR' => 'projector',
    'REFLECTOR' => 'reflector'
  ];

  const BODYGRAPH_CENTERS = [
    'AJNA' => 'ajna',
    'CROWN' => 'crown',
    'EGO' => 'ego',
    'G' => 'g',
    'ROOT' => 'root',
    'SACRAL' => 'sacral',
    'SOLAR_PLEXUS' => 'emotion',
    'SPLEEN' => 'spleen',
    'THROAT' => 'throat'
  ];

  const BODYGRAPH_AUTHORITIES = [
    'EGO_PROJECTED' => 'ego-projected',
    'EMOTIONAL' => 'emotions',
    'LUNA' => 'luna',
    'SACRAL' => 'sacral',
    'SELF_PROJECTED' => 'self-projected',
    'SPLENIC' => 'splenic',
    'ENVIRONMENTAL' => 'environmental',
    // 'SOUNDING_BOARD' => 'sounding-board',
  ];

  // Internal mappings
  const DEFINED_CENTERS_FOR_CHANNEL = [
    '1-8' => ['G', 'THROAT'],
    '2-14' => ['SACRAL', 'G'],
    '3-60' => ['ROOT', 'SACRAL'],
    '4-63' => ['CROWN', 'AJNA'],
    '5-15' => ['SACRAL', 'G'],
    '6-59' => ['SACRAL', 'SOLAR_PLEXUS'],
    '7-31' => ['G', 'THROAT'],
    '9-52' => ['ROOT', 'SACRAL'],
    '10-20' => ['G', 'THROAT'], // !! ??
    '10-34' => ['G', 'SACRAL'], // !!
    '10-57' => ['G', 'SPLEEN'], // !!
    '11-56' => ['AJNA', 'THROAT'],
    '12-22' => ['THROAT', 'SOLAR_PLEXUS'],
    '13-33' => ['G', 'THROAT'],
    '16-48' => ['SPLEEN', 'THROAT'],
    '17-62' => ['AJNA', 'THROAT'],
    '18-58' => ['ROOT', 'SPLEEN'],
    '19-49' => ['ROOT', 'SOLAR_PLEXUS'],
    '20-34' => ['THROAT', 'SACRAL'], // !!
    '20-57' => ['THROAT', 'SPLEEN'], // !!
    '21-45' => ['EGO', 'THROAT'],
    '23-43' => ['AJNA', 'THROAT'],
    '24-61' => ['CROWN', 'AJNA'],
    '25-51' => ['EGO', 'THROAT'],
    '26-44' => ['SPLEEN', 'EGO'],
    '27-50' => ['SPLEEN', 'SACRAL'],
    '28-38' => ['SPLEEN', 'ROOT'],
    '29-46' => ['SACRAL', 'G'],
    '30-41' => ['ROOT', 'SOLAR_PLEXUS'],
    '32-54' => ['ROOT', 'SPLEEN'],
    '34-57' => ['SACRAL', 'SPLEEN'], // !! ??
    '35-36' => ['SOLAR_PLEXUS', 'THROAT'],
    '37-40' => ['EGO', 'SOLAR_PLEXUS'],
    '39-55' => ['ROOT', 'SOLAR_PLEXUS'],
    '42-53' => ['ROOT', 'SACRAL'],
    '47-64' => ['CROWN', 'AJNA']
  ];

  const AUTHORITY_FOR_CENTER = [
    'SOLAR_PLEXUS' => 'EMOTIONAL',
    'SACRAL' => 'SACRAL',
    'SPLEEN' => 'SPLENIC',
    'EGO' => 'EGO_PROJECTED',
    'G' => 'SELF_PROJECTED',
  ];