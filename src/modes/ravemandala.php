<?php

  error_reporting(E_ALL);

  require_once dirname(__DIR__) . '/libs/bodygraph.php';
  require_once dirname(__DIR__) . '/libs/ravemandala.php';

  header('Content-Type: image/svg+xml');
  
  // TODO: Check if empty! Then switch?
  $input_data = json_decode(file_get_contents('php://input'), true);
  $post_data = $_POST;
  $get_data = $_GET;

  // MARK: Debug only!
  if ($get_data && count($get_data) > 0 && isset($_GET['year'])) {
    $formatted_get_data = [
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
  }

  if ($get_data && count($get_data) > 0) {
    $data = $formatted_get_data;
  }

  if ($input_data && count($input_data) > 0) {
    $data = $input_data;
  }

  if ($post_data && count($post_data) > 0) {
    $data = $post_data;
  }

  if (!isset($data) || !isset($data['date']) || !isset($data['time']) || !isset($data['timezone'])) {
    echo '{}';
    die();
  }
  
  $bodygraph = new Bodygraph($data['date'], $data['time'], $data['timezone']);
  $ravemandala = new RaveMandala($bodygraph->getBodygraph());
  
  echo $ravemandala->getRaveMandala();