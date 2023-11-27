<?php
/**
 * Model
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/**
 * Docs created with Mintlify Doc Writer(https://mintlify.com)
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Connects to the database using PDO
 * @param string $host Database host
 * @param string $db Database name
 * @param string $user Database user
 * @param string $pass Database password
 * @return PDO Database object
 */
function connect_db($host, $db, $user, $pass)
{
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        echo sprintf("Failed to connect. %s", $e->getMessage());
    }
    return $pdo;
}

/**
 * Check if the route exists
 * @param string $route_uri URI to be matched
 * @param string $request_type Request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type)
{
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    } else {
        return False;
    }
}

/**
 * Creates a new navigation array item using URL and active status
 * @param string $url The URL of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active)
{
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template Filename of the template without extension
 * @return string
 */
function use_template($template)
{
    return sprintf("views/%s.php", $template);
}

/**
 * Creates breadcrumbs HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs)
{
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]) {
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">' . $name . '</li>';
        } else {
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="' . $info[0] . '">' . $name . '</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation bar HTML code using given array
 * @param array $navigation Array with navigation items and their URLs
 * @param int $active_id The ID of the active navigation item
 * @return string HTML code that represents the navigation bar
 */
function get_navigation($navigation, $active_id = null)
{
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';

    foreach ($navigation as $id => $info) {
        $active_class = ($id === $active_id) ? ' active' : '';
        $navigation_exp .= '<li class="nav-item' . $active_class . '">';
        $navigation_exp .= '<a class="nav-link" href="' . $info['url'] . '">' . $info['name'] . '</a>';
        $navigation_exp .= '</li>';
    }

    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';

    return $navigation_exp;
}

/**
 * Creates a Bootstrap table with a list of series
 * @param array $series Associative array of series
 * @return string
 */
function get_series_table($series, $pdo)
{
    $table_exp = '
    <table class="table table-hover">
    <thead
    <tr>
        <th scope="col">Series</th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>';
    foreach ($series as $key => $value) {
        $user = get_user_by_id($pdo, $value['user']);
        $firstname = isset($user['firstname']) ? $user['firstname'] : 'John';
        $lastname = isset($user['lastname']) ? $user['lastname'] : 'Doe';
        $table_exp .= '
        <tr>
            <th scope="row">' . $value['name'] . '</th>
            <td>' . $firstname . ' ' . $lastname . '</td>
            <td><a href="/DDWT23/week2/series/?series_id=' . $value['id'] . '" role="button" class="btn btn-primary">More info</a></td>
        </tr>
        ';
    }
    $table_exp .= '
    </tbody>
    </table>
    ';
    return $table_exp;
}

/**
 * Pretty Print Array
 * @param $input
 */
function p_print($input)
{
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Get array with all listed series from the database
 * @param PDO $pdo Database object
 * @return array Associative array with all series
 */
function get_series($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = array();

    /* Create array with htmlspecialchars */
    foreach ($series as $key => $value) {
        foreach ($value as $user_key => $user_input) {
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $series_exp;
}

/**
 * Generates an array with series information
 * @param PDO $pdo Database object
 * @param int $series_id ID from the series
 * @return mixed
 */
function get_series_info($pdo, $series_id)
{
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$series_id]);
    $series_info = $stmt->fetch();
    $series_info_exp = array();

    /* Create array with htmlspecialchars */
    foreach ($series_info as $key => $value) {
        $series_info_exp[$key] = htmlspecialchars($value);
    }
    return $series_info_exp;
}

/**
 * Creates HTML alert code with information about the success or failure
 * @param array $feedback Associative array with keys type and message
 * @return string
 */
function get_error($feedback)
{
    if (is_string($feedback)) {
        $feedback = json_decode($feedback, true);
    }
    return '
        <div class="alert alert-' . $feedback['type'] . '" role="alert">
            ' . $feedback['message'] . '
        </div>';
}

/**
 * Add series to the database
 * @param PDO $pdo Database object
 * @param array $series_info Associative array with series info
 * @return array Associative array with key type and message
 */
function add_series($pdo, $series_info)
{
    /* Check if all fields are set */
    if (
        empty($series_info['Name']) or
        empty($series_info['Creator']) or
        empty($series_info['Seasons']) or
        empty($series_info['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check data type */
    if (!is_numeric($series_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Check if series already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$series_info['Name']]);
    $series = $stmt->rowCount();
    if ($series) {
        return [
            'type' => 'danger',
            'message' => 'This series was already added.'
        ];
    }

    /* Add Series */
    $stmt = $pdo->prepare("INSERT INTO series (name, creator, seasons, abstract, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $series_info['Name'],
        $series_info['Creator'],
        $series_info['Seasons'],
        $series_info['Abstract'],
        $_SESSION['user_id']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' added to Series Overview.", $series_info['Name'])
        ];
    } else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not added. Try it again.'
        ];
    }
}

/**
 * Updates a series in the database
 * @param PDO $pdo Database object
 * @param array $series_info Associative array with series info
 * @return array
 */
function update_series($pdo, $series_info)
{
    /* Check if all fields are set */
    if (
        empty($series_info['Name']) or
        empty($series_info['Creator']) or
        empty($series_info['Seasons']) or
        empty($series_info['Abstract']) or
        empty($series_info['series_id'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check data type */
    if (!is_numeric($series_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Get current series name */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$series_info['series_id']]);
    $series = $stmt->fetch();
    $current_name = $series['name'];

    /* Check if series already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$series_info['Name']]);
    $series = $stmt->fetch();
    if ($series_info['Name'] == $series['name'] and $series['name'] != $current_name) {
        return [
            'type' => 'danger',
            'message' => sprintf("The name of the series cannot be changed. %s already exists.", $series_info['Name'])
        ];
    }

    /* Update Series */
    $stmt = $pdo->prepare("UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ?, user_id = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([
        $series_info['Name'],
        $series_info['Creator'],
        $series_info['Seasons'],
        $series_info['Abstract'],
        $_SESSION['user_id'],
        $series_info['series_id'],
        $_SESSION['user_id'],
    ]);
    $updated = $stmt->rowCount();
    if ($updated == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was edited!", $series_info['Name'])
        ];
    } else {
        return [
            'type' => 'warning',
            'message' => 'The series was not edited. No changes were detected.'
        ];
    }
}

/**
 * Removes a series with a specific series ID
 * @param PDO $pdo Database object
 * @param int $series_id ID of the series
 * @return array
 */
function remove_series($pdo, $series_id)
{
    /* Get series info */
    $series_info = get_series_info($pdo, $series_id);

    /* Delete Series */
    $stmt = $pdo->prepare("DELETE FROM series WHERE id = ? AND user_id = ?");
    $stmt->execute([$series_id, $_SESSION['user_id']]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was removed!", $series_info['name'])
        ];
    } else {
        return [
            'type' => 'warning',
            'message' => 'An error occurred. The series was not removed.'
        ];
    }
}

/**
 * Count the number of series listed on Series Overview
 * @param PDO $pdo Database object
 * @return int
 */
function count_series($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->rowCount();
    return $series;
}

/**
 * Get the username by user id
 * @param PDO $pdo Database object
 * @param int $id User id
 * @return string
 */
function get_user_by_id($pdo, $id)
{
    $stmt = $pdo->prepare('SELECT firstname, lastname FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Changes the HTTP Header to a given location
 * @param string $location Location to redirect to
 */
function redirect($location)
{
    header(sprintf('Location: %s', $location));
    die();
}

/**
 * Get current user ID
 * @return bool Current user ID or False if not logged in
 */
function get_user_id()
{
    session_start();
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    } else {
        return False;
    }
}

/**
 * The count_users function retrieves the count of distinct users from a series table using a PDO
 * object.
 * 
 * @param pdo The parameter `` is a PDO object, which is used to connect to a database and execute
 * SQL queries. It is typically created using the PDO class and requires the necessary database
 * credentials to establish a connection.
 * 
 * @return count of distinct users from the "series" table in the database.
 */
function count_users($pdo)
{
    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT user) as user_count FROM series');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['user_count'];
}

function register_user($pdo, $form_data)
{
    /* Check if all fields are filled in */
    if (empty($form_data['username']) || empty($form_data['password']) || empty($form_data['firstname']) || empty($form_data['lastname'])) {
        return [
            'type' => 'danger',
            'message' => 'You should enter a username, password, first- and last name.'
        ];
    }

    /* Check if username already exists */
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$form_data['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [
            'type' => 'danger',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }

    if ($user) {
        return [
            'type' => 'danger',
            'message' => 'This username is already taken.'
        ];
    }

    /* Hash the password */
    $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);

    /* Save the user to the database */
    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, password, firstname, lastname) VALUES (?, ?, ?, ?)');
        $stmt->execute([$form_data['username'], $hashed_password, $form_data['firstname'], $form_data['lastname']]);
    } catch (PDOException $e) {
        return [
            'type' => 'danger',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }

    /* Log the user in and add the user id to the current session */
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['username'] = $form_data['username'];

    return [
        'type' => 'success',
        'message' => 'Registration successful. You are now logged in.'
    ];
}

function login_user($pdo, $form_data)
{
    /* Check if all fields are filled in */
    if (empty($form_data['username']) || empty($form_data['password'])) {
        return [
            'type' => 'danger',
            'message' => 'You should enter a username and password.'
        ];
    }

    /* Check if username exists */
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$form_data['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [
            'type' => 'danger',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }

    if (!$user) {
        return [
            'type' => 'danger',
            'message' => 'This username does not exist.'
        ];
    }

    /* Check if the password is correct */
    if (!password_verify($form_data['password'], $user['password'])) {
        return [
            'type' => 'danger',
            'message' => 'The password is incorrect.'
        ];
    }

    /* Log the user in and add the user id to the current session */
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    return [
        'type' => 'success',
        'message' => 'Login successful.'
    ];
}

/**
 * The function checks if a user is logged in by checking if the 'user_id' key is set in the session.
 * 
 * @return boolean value indicating whether the 'user_id' key is set in the  array.
 */
function check_login()
{
    return isset($_SESSION['user_id']);
}

/**
 * The function "logout_user" destroys the session and returns a success message indicating that the
 * user has been logged out.
 * 
 * @return array is being returned with two key-value pairs. The 'type' key has a value of 'success'
 * and the 'message' key has a value of 'You have been logged out.'
 */
function logout_user()
{
    /* Destroy the session */
    session_destroy();

    return [
        'type' => 'success',
        'message' => 'You have been logged out.'
    ];
}