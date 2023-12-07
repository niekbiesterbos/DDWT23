<?php
/**
 * Model
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** 
 * Comments created using Mintlify Doc Writer
 * Visit https://writer.mintlify.com/
 */

/**
 * Establishes a connection with the database
 * @param string $host Hostname of the database server
 * @param string $database Name of the database
 * @param string $username Username for the database
 * @param string $password Password for the database
 * @return PDO
 */
function connect_db($host, $database, $username, $password) {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo "Connection failed: ".$e->getMessage();
        return null;
    }
}

/**
 * Returns the number of series listed in the database
 * @param PDO $db Database connection
 * @return int
 */
function count_series($db) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM series");
    $stmt->execute();
    return $stmt->fetchColumn();
}

/**
 * Returns an associative array with all the series listed in the database
 * @param PDO $db Database connection
 * @return array
 */
function get_series($db) {
    $stmt = $db->prepare("SELECT * FROM series");
    $stmt->execute();
    $series = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($series as $key => $value) {
        foreach($value as $subkey => $subvalue) {
            $series[$key][$subkey] = htmlspecialchars($subvalue);
        }
    }
    return $series;

}

/**
 * Returns a string with the HTML code representing the table with all the series
 * @param array $series Associative array with all the series
 * @return string
 */
function get_series_table($series) {
    $table = '';
    foreach($series as $serie) {
        $table .= '<tr>';
        $table .= '<td>'.$serie['name'].'</td>';
        $table .= '<td><a href="/DDWT23/week1/series/?series_id='.$serie['id'].'">More info</a></td>';
        $table .= '</tr>';
    }
    return $table;
}

/**
 * Returns the information of a series with a specific series id
 * @param PDO $db Database connection
 * @param int $series_id ID of the series
 * @return array
 */
function get_series_info($db, $series_id) {
    $stmt = $db->prepare("SELECT * FROM series WHERE id = :id");
    $stmt->bindParam(':id', $series_id);
    $stmt->execute();
    $series = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$series) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series does not exist.', 'status' => 400];
    }
    return $series;
}

/**
 * Adds a new series to the database
 * @param PDO $db Database connection
 * @param array $post_data POST data from the form
 * @return array
 */
function add_series($db, $post_data) {
    if(empty($post_data['name']) || empty($post_data['creator']) || empty($post_data['seasons']) || empty($post_data['abstract'])) {
        return ['type' => 'danger', 'message' => 'All fields are required.', 'status' => 400];
    }

    if(!is_numeric($post_data['seasons'])) {
        return ['type' => 'danger', 'message' => 'Seasons must be a number.', 'status' => 400];
    }

    $stmt = $db->prepare("SELECT * FROM series WHERE name = :name");
    $stmt->bindParam(':name', $post_data['name']);
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        return ['type' => 'danger', 'message' => 'The series already exists in the database.', 'status' => 400];
    }

    $stmt = $db->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (:name, :creator, :seasons, :abstract)");
    $stmt->bindParam(':name', $post_data['name']);
    $stmt->bindParam(':creator', $post_data['creator']);
    $stmt->bindParam(':seasons', $post_data['seasons']);
    $stmt->bindParam(':abstract', $post_data['abstract']);
    if($stmt->execute()) {
        return ['type' => 'success', 'message' => 'The series was added successfully.'];
    } else {
        return ['type' => 'danger', 'message' => 'There was an error adding the series to the database.', 'status' => 500];
    }
}

/**
 * Updates a series in the database
 * @param PDO $db Database connection
 * @param array $post_data POST data from the form
 * @return array
 */
function update_series($db, $post_data) {
    if(empty($post_data['name']) || empty($post_data['creator']) || empty($post_data['seasons']) || empty($post_data['abstract'])) {
        return ['type' => 'danger', 'message' => 'All fields are required.'];
    }

    if(!is_numeric($post_data['seasons'])) {
        return ['type' => 'danger', 'message' => 'Seasons must be a number.', 'status' => 400];
    }

    // Check if the series exists
    $stmt = $db->prepare("SELECT * FROM series WHERE id = :id");
    $stmt->bindParam(':id', $post_data['series_id']);
    $stmt->execute();
    $current_series = $stmt->fetch();

    if(!$current_series) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series does not exist.'
            , 'status' => 404
        ];
    }

    $stmt = $db->prepare("SELECT * FROM series WHERE name = :name AND id != :id");
    $stmt->bindParam(':name', $post_data['name']);
    $stmt->bindParam(':id', $post_data['series_id']);
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        return ['type' => 'danger', 'message' => 'Another series with the same name already exists in the database.', 'status' => 500];
    }

    $stmt = $db->prepare("UPDATE series SET name = :name, creator = :creator, seasons = :seasons, abstract = :abstract WHERE id = :id");
    $stmt->bindParam(':name', $post_data['name']);
    $stmt->bindParam(':creator', $post_data['creator']);
    $stmt->bindParam(':seasons', $post_data['seasons']);
    $stmt->bindParam(':abstract', $post_data['abstract']);
    $stmt->bindParam(':id', $post_data['series_id']);
    if($stmt->execute()) {
        return ['type' => 'success', 'message' => 'The series was updated successfully.'];
    } else {
        return ['type' => 'danger', 'message' => 'There was an error updating the series in the database.', 'status' => 500];
    }
}

/**
 * Removes a series from the database
 * @param PDO $db Database connection
 * @param int $series_id ID of the series to remove
 * @return array
 */
function remove_series($db, $series_id) {
    $stmt = $db->prepare("SELECT * FROM series WHERE id = :id");
    $stmt->bindParam(':id', $series_id);
    $stmt->execute();
    $series_info = $stmt->fetch();

    if(isset($series_info['message'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series does not exist.'
            , 'status' => 404
        ];
    }

    $stmt = $db->prepare("DELETE FROM series WHERE id = :id");
    $stmt->bindParam(':id', $series_id);
    if($stmt->execute()) {
        return ['type' => 'success', 'message' => 'The series was removed successfully.'];
    } else {
        return ['type' => 'danger', 'message' => 'There was an error removing the series from the database.', 'status' => 500];
    }
}


/**
 * Check if the route exists
 * @param string $route_uri URI to be matched
 * @param string $request_type Request method
 * @return bool
 *
 */

function new_route($route_uri, $request_type) {
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
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
function na($url, $active) {
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template Filename of the template without extension
 * @return string
 */
function use_template($template) {
    return sprintf("views/%s.php", $template);
}

/**
 * Creates breadcrumbs HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach($breadcrumbs as $name => $info) {
        if($info[1]) {
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        } else {
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation bar HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the navigation bar
 */
function get_navigation($navigation) {
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach($navigation as $name => $info) {
        if($info[1]) {
            $navigation_exp .= '<li class="nav-item active">';
        } else {
            $navigation_exp .= '<li class="nav-item">';
        }
        $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pretty Print Array
 * @param $input
 */
function p_print($input) {
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creates HTML alert code with information about the success or failure
 * @param array $feedback Associative array with keys type and message
 * @return string
 */
function get_error($feedback) {
    return '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
}
