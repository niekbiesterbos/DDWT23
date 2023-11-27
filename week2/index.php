<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

session_start();
include 'model.php';


/* Connect to DB */
$db = connect_db('localhost', 'ddwt23_week2', 'ddwt23', 'ddwt23');

/* Common Section */
$nbr_series = count_series($db);
$right_column = use_template('cards');
$user_count = count_users($db);

$navigation_template = [
    1 => ['name' => 'Home', 'url' => '/DDWT23/week2/'],
    2 => ['name' => 'Overview', 'url' => '/DDWT23/week2/overview/'],
    3 => ['name' => 'Add Series', 'url' => '/DDWT23/week2/add/'],
    4 => ['name' => 'My Account', 'url' => '/DDWT23/week2/myaccount/'],
    5 => ['name' => 'Registration', 'url' => '/DDWT23/week2/register/']
];


/* Landing page */
if (new_route('/DDWT23/week2/', 'get')) {
    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Home' => na('/DDWT23/week2/', True)
    ]);
    $navigation = get_navigation($navigation_template, 1);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */elseif (new_route('/DDWT23/week2/overview/', 'get')) {
    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Overview' => na('/DDWT23/week2/overview', True)
    ]);
    $navigation = get_navigation($navigation_template, 2);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_series_table(get_series($db), $db);

    /* Display error message */
    if (isset($_GET['error_msg'])) {
        $feedback = json_decode(urldecode($_GET['error_msg']), true);
        $error_msg = get_error($feedback);
    }

    /* Choose Template */
    include use_template('main');
}

/* Single series */elseif (new_route('/DDWT23/week2/series/', 'get')) {
    /* Get series from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);
    $user = get_user_by_id($db, $series_info['user']);


    /* Page info */
    $page_title = $series_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Overview' => na('/DDWT23/week2/overview/', False),
        $series_info['name'] => na('/DDWT23/week2/series/?series_id=' . $series_id, True)
    ]);
    $navigation = get_navigation($navigation_template);

    /* Page content */
    $display_buttons = ($series_info['user'] == $_SESSION['user_id']);
    $page_subtitle = sprintf("Information about %s", $series_info['name']);
    $page_content = $series_info['abstract'];
    $nbr_seasons = $series_info['seasons'];
    $creators = $series_info['creator'];
    $firstname = isset($user['firstname']) ? $user['firstname'] : 'John';
    $lastname = isset($user['lastname']) ? $user['lastname'] : 'Doe';
    $added_by = $firstname . ' ' . $lastname;


    /* Display error message */
    if (isset($_GET['error_msg'])) {
        $feedback = json_decode(urldecode($_GET['error_msg']), true);
        $error_msg = get_error($feedback);
    }

    /* Choose Template */
    include use_template('series');
}

/* Add series GET */elseif (new_route('/DDWT23/week2/add/', 'get')) {
    if (!check_login()) {
        redirect('/DDWT23/week2/login/');
    }
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Add Series' => na('/DDWT23/week2/add/', True)
    ]);
    $navigation = get_navigation($navigation_template, 5);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of your favorite series.';
    $submit_btn = 'Add Series';
    $form_action = '/DDWT23/week2/add/';

    /* Display error message */
    if (isset($_GET['error_msg'])) {
        $feedback = json_decode(urldecode($_GET['error_msg']), true);
        $error_msg = get_error($feedback);
    }

    include use_template('new');
}

/* Add series POST */elseif (new_route('/DDWT23/week2/add/', 'post')) {
    if (!check_login()) {
        redirect('/DDWT23/week2/login/');
    }
    /* Add series to database */
    $feedback = add_series($db, $_POST);
    $encoded_feedback = urlencode(json_encode($feedback));
    redirect(sprintf('/DDWT23/week2/add/?error_msg=%s', $encoded_feedback));
}

/* Edit series GET */elseif (new_route('/DDWT23/week2/edit/', 'get')) {
    /* Get series info from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);
    $series_name = $series_info['name'];
    $series_abstract = $series_info['abstract'];
    $seasons = $series_info['seasons'];
    $creator = $series_info['creator'];

    $form_action = '/DDWT23/week2/edit/';
    $submit_button_text = 'Update Series';

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        sprintf('Edit Series %s', $series_name) => na('/DDWT23/week2/new/', True)
    ]);
    $navigation = get_navigation($navigation_template);

    /* Page content */
    $right_column = use_template('cards');
    $page_subtitle = sprintf('Edit %s', $series_name);
    $page_content = 'Edit the series below.';

    /* Choose Template */
    include use_template('new');
}

/* Edit series POST */elseif (new_route('/DDWT23/week2/edit/', 'post')) {
    if (!check_login()) {
        redirect('/DDWT23/week2/login/');
    }
    /* Update series in database */
    $feedback = update_series($db, $_POST);
    $encoded_feedback = urlencode(json_encode($feedback));
    $series_id = $_POST['series_id'];
    redirect(sprintf('/DDWT23/week2/series/?series_id=%s&error_msg=%s', $series_id, $encoded_feedback));
}



/* Remove series */elseif (new_route('/DDWT23/week2/remove/', 'post')) {
    /* Remove series from database */
    $feedback = remove_series($db, $_POST['series_id']);
    $encoded_feedback = urlencode(json_encode($feedback));
    redirect(sprintf('/DDWT23/week2/overview/?error_msg=%s', $encoded_feedback));
}

/* My Account page */elseif (new_route('/DDWT23/week2/myaccount/', 'get')) {
    if (!check_login()) {
        redirect('/DDWT23/week2/login/');
    }
    /* Page info */
    $page_title = 'My Account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'My Account' => na('/DDWT23/week2/myaccount/', True)
    ]);
    $navigation = get_navigation($navigation_template, 4);

    /* Page content */
    $page_subtitle = 'Your account details';
    $page_content = 'Here you can manage your account details.';
    $complete_user = get_user_by_id($db, $_SESSION['user_id']);
    $user = $complete_user['firstname'];

    /* Display error message */
    if (isset($_GET['error_msg'])) {
        $feedback = json_decode(urldecode($_GET['error_msg']), true);
        $error_msg = get_error($feedback);
    }

    /* Choose Template */
    include use_template('account');
}

/* Registration page */elseif (new_route('/DDWT23/week2/register/', 'get')) {
    /* Page info */
    $page_title = 'Registration';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Registration' => na('/DDWT23/week2/register/', True)
    ]);
    $navigation = get_navigation($navigation_template, 5);

    /* Page content */
    $page_subtitle = 'Register for an account';
    $page_content = 'Fill in the form to register for an account.';

    /* Display error message */
    if (isset($_GET['error_msg'])) {
        $feedback = json_decode(urldecode($_GET['error_msg']), true);
        $error_msg = get_error($feedback);
    }

    /* Choose Template */
    include use_template('register');
}

/* Registration POST */elseif (new_route('/DDWT23/week2/register/', 'post')) {
    $feedback = register_user($db, $_POST);

    /* Redirect to the appropriate page based on the feedback */
    if ($feedback['type'] == 'success') {
        redirect(sprintf('/DDWT23/week2/myaccount/?error_msg=%s', json_encode($feedback)));
    } else {
        redirect(sprintf('/DDWT23/week2/register/?error_msg=%s', json_encode($feedback)));
    }
}

/* Login page */elseif (new_route('/DDWT23/week2/login/', 'get')) {
    if (check_login()) {
        redirect('/DDWT23/week2/myaccount/');
    }
    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Login' => na('/DDWT23/week2/login/', True)
    ]);
    $navigation = get_navigation($navigation_template, 6);

    /* Page content */
    $page_subtitle = 'Login to your account';
    $page_content = 'Enter your credentials to login.';

    /* Display error message */
    if (isset($_GET['error_msg'])) {
        $feedback = json_decode(urldecode($_GET['error_msg']), true);
        $error_msg = get_error($feedback);
    }

    /* Choose Template */
    include use_template('login');
} /* Login POST */elseif (new_route('/DDWT23/week2/login/', 'post')) {
    /* Call login_user() function */
    $feedback = login_user($db, $_POST);

    /* Redirect to the appropriate page based on the feedback */
    if ($feedback['type'] == 'success') {
        redirect(sprintf('/DDWT23/week2/myaccount/?error_msg=%s', json_encode($feedback)));
    } else {
        redirect(sprintf('/DDWT23/week2/login/?error_msg=%s', json_encode($feedback)));
    }
}

/* Logout */elseif (new_route('/DDWT23/week2/logout/', 'get')) {
    /* Call logout_user() function */
    $feedback = logout_user();

    /* Redirect to the landing page with the feedback message */
    redirect(sprintf('/DDWT23/week2/?error_msg=%s', json_encode($feedback)));
} else {
    http_response_code(404);
    echo '404 Not Found';
}