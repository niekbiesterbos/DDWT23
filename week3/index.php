<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Require composer autoloader */
require __DIR__.'/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt23_week3', 'ddwt23', 'ddwt23');

/* Create Router instance */
$router = new \Bramus\Router\Router();

function set_cred($username, $password) {
    return ['username' => $username, 'password' => $password];
}

function check_cred($cred) {
    if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        return false;
    }

    return $_SERVER['PHP_AUTH_USER'] === $cred['username'] && $_SERVER['PHP_AUTH_PW'] === $cred['password'];
}

// Add routes here
$cred = set_cred('ddwt23', 'ddwt23');

$router->before('GET|POST|PUT|DELETE', '/api/.*', function () use ($router, $cred) {
    header("Content-Type: application/json");

    if(!check_cred($cred)) {
        echo json_encode(['error' => 'Invalid credentials']);
        exit();
    }
});

$router->mount('/api', function () use ($router, $db) {
    $router->get('/series', function () use ($db) {
        $series = get_series($db);
        http_response_code($series['status'] ?? 200);
        echo json_encode($series);
    });

    $router->get('/series/(\d+)', function ($id) use ($db) {
        $series = get_series_info($db, $id);
        http_response_code($series['status'] ?? 200);
        echo json_encode($series);
    });

    $router->delete('/series/(\d+)', function ($id) use ($db) {
        $result = remove_series($db, $id);
        http_response_code($result['status'] ?? 200);
        echo json_encode(['success' => $result]);
    });
    $router->post('/series', function () use ($db) {
        $result = add_series($db, $_POST);
        http_response_code($result['status'] ?? 200);
        echo json_encode(['success' => $result]);
    });
    $router->put('/series/(\d+)', function ($id) use ($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);

        $series_info = $_PUT + ["series_id" => $id];
        $result = update_series($db, $series_info);
        http_response_code($result['status'] ?? 200);
        echo json_encode(['success' => $result]);
    });
});

$router->set404(function () {
    header('HTTP/1.1 404 Not Found');
    echo '404 Not Found';
});


/* Run the router */
$router->run();
