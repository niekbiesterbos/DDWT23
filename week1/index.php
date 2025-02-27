<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */
include 'models/model.php';

$host = 'localhost';
$database = 'ddwt23_week1';
$username = 'ddwt23';
$password = 'ddwt23';

$db = connect_db($host, $database, $username, $password);


$series_count = count_series($db);
$series = get_series($db);
$series_table = get_series_table($series);


/* Landing page */
if (new_route('/DDWT23/week1/', 'get')) {
    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        'Home' => na('/DDWT23/week1/', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', True),
        'Overview' => na('/DDWT23/week1/overview/', False),
        'Add Series' => na('/DDWT23/week1/add/', False)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */elseif (new_route('/DDWT23/week1/overview/', 'get')) {
    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', True),
        'Add Series' => na('/DDWT23/week1/add/', False)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = '
    <table class="table table-hover">
    <thead>
    <tr>
        <th scope="col">Series</th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>
    ' . $series_table . '
    </tbody>
</table>';
    ;

    /* Choose Template */
    include use_template('main');
}

/* Single series */elseif (new_route('/DDWT23/week1/series/', 'get')) {
    /* Get series from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);

    $series_name = $series_info['name'];
    $series_abstract = $series_info['abstract'];
    $seasons = $series_info['seasons'];
    $creator = $series_info['creator'];

    /* Page info */
    $page_title = $series_name;
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview/', False),
        $series_name => na('/DDWT23/week1/series/', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', True),
        'Add Series' => na('/DDWT23/week1/add/', False)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = sprintf('Information about %s', $series_name);
    $page_content = $series_abstract;

    /* Choose Template */
    include use_template('series');
}

/* Add series GET */elseif (new_route('/DDWT23/week1/add/', 'get')) {
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        'Add Series' => na('/DDWT23/week1/new/', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', False),
        'Add Series' => na('/DDWT23/week1/add/', True)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $form_action = '/DDWT23/week1/add/';
    $submit_button_text = 'Add Series';

    /* Choose Template */
    include use_template('new');
}

/* Add series POST */elseif (new_route('/DDWT23/week1/add/', 'post')) {
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        'Add Series' => na('/DDWT23/week1/add/', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', False),
        'Add Series' => na('/DDWT23/week1/add/', True)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_button_text = 'Add Series';
    $form_action = '/DDWT23/week1/add/';

    $result = add_series($db, $_POST);

    include use_template('new');
}

/* Edit series GET */elseif (new_route('/DDWT23/week1/edit/', 'get')) {
    /* Get series info from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);
    $series_name = $series_info['name'];
    $series_abstract = $series_info['abstract'];
    $seasons = $series_info['seasons'];
    $creator = $series_info['creator'];

    $form_action = '/DDWT23/week1/edit/';
    $submit_button_text = 'Update Series';

    $form_action = '/DDWT23/week1/edit/';
    $submit_button_text = 'Update Series';

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        sprintf('Edit Series %s', $series_name) => na('/DDWT23/week1/new/', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', False),
        'Add Series' => na('/DDWT23/week1/add/', False)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = sprintf('Edit %s', $series_name);
    $page_content = 'Edit the series below.';

    /* Choose Template */
    include use_template('new');
}

/* Edit series POST */elseif (new_route('/DDWT23/week1/edit/', 'post')) {
    /* Get series info from db */
    $feedback = update_series($db, $_POST);
    $error_message = get_error($feedback);

    $series_id = $_POST['series_id'];
    $series_info = get_series_info($db, $series_id);
    $series_name = $series_info['name'];
    $series_abstract = $series_info['abstract'];
    $seasons = $series_info['seasons'];
    $creator = $series_info['creator'];

    /* Page info */
    $page_title = $series_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview/', False),
        $series_name => na('/DDWT23/week1/series/', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', False),
        'Add Series' => na('/DDWT23/week1/add/', False)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = sprintf('Information about %s', $series_name);
    $page_content = $series_info['abstract'];

    /* Choose Template */
    include use_template('series');
}

/* Remove series */elseif (new_route('/DDWT23/week1/remove/', 'post')) {
    /* Remove series in database */
    $series_id = $_POST['series_id'];
    $feedback = remove_series($db, $series_id);
    $error_msg = get_error($feedback);

    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 1' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', True)
    ]);
    $navigation = get_navigation([
        'Home' => na('/DDWT23/week1/', False),
        'Overview' => na('/DDWT23/week1/overview', True),
        'Add Series' => na('/DDWT23/week1/add/', False)
    ]);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';


    /* Choose Template */
    include use_template('main');
} else {
    http_response_code(404);
    echo '404 Not Found';
}
