<?php

  error_reporting(E_ALL);

  require_once __DIR__ . '/libs/utils.php';

  $birth_datetime = null;
  
  if (isset($_GET['year']))
  $birth_datetime = [
    'date' => [
        'year' => $_GET['year'],
        'month' => $_GET['month'],
        'day' => $_GET['day']
      ],
      'time' => [
        'hour' => $_GET['hour'],
        'min' => $_GET['min']
      ],
      'timezone' => [
        'offset' => $_GET['offset']
      ]
  ];

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bodygraph API | Karmic Works</title>
  </head>
  <body>
    <div style='display: flex; flex-direction: col;'>
    <div style='flex: 1;'>
      <h1>Input</h1>
      <form method="GET">
        <label for="year">Year</label><br />
        <input name="year" id="year" value="1988" type="number" min="1970" max="2070" step="1"/><br />
        <br />
        <label for="month">Month</label><br />
        <input name="month" id="month" value="12" type="number" min="1" max="12" step="1"/><br />
        <br />
        <label for="day">Day</label><br />
        <input name="day" id="day" value="12" type="number" min="1" max="31" step="1"/><br />
        <br />
        <label for="hour">Hour (24)</label><br />
        <input name="hour" id="hour" value="23" type="number" min="0" max="23" step="1"/><br />
        <br />
        <label for="min">Minutes</label><br />
        <input name="min" id="min" value="42" type="number" min="0" max="59" step="1"/><br />
        <br />
        <label for="offset">Timezone Offset (From UTC)</label><br />
        <input name="offset" id="offset" value="2" type="number" min="-24" max="24" step="0.25"/><br />
        <br />
        <button type="submit">Submit</button>
      </form>
    </div>
    <div style='flex: 1;'>
      <h1>Output</h1>
        <?php

          if (!$birth_datetime) {
            return;
          }

        ?>
        <h2>SVG</h2>
        <?php

          if (!$birth_datetime) {
            return;
          }

          $ravemandala = call_bodygraph_api('/modes/ravemandala.php', $birth_datetime);
          
          ppr($ravemandala);

        ?>
        <h2>JSON</h2>
        <?php

          if (!$birth_datetime) {
            return;
          }

          $bodygraph = call_bodygraph_api('/modes/generate.php', $birth_datetime);

          try {
            $bodygraph = json_decode($bodygraph, true, 512, JSON_THROW_ON_ERROR);  
          } finally {
            ppr($bodygraph);
          }
          
        ?>
      </div>
    </div>
  </body>
</html>