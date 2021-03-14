<?php

require_once(__DIR__ . '/../config.php');

$context = context_system::instance();
$PAGE->set_context($context);

// Hardcoded URL to course with quiz.
$url = new moodle_url('/course/view.php', ['id' => 3]);

// The last pre-created user for anonymous quiz.
// In the unlikely case that we'll hit the limit, quiz takers will need to
// wait for the site to reset.
$maxid = 1000;

// Check if the user is already logged-in.
if (isloggedin() and !isguestuser()) {
    redirect($url);
}

// Get the next user ID we can use.
$record = $DB->get_record('config', ['name' => 'nextguestid']);
$userid = (int) $record->value;
if ($userid > $maxid) {
    throw new moodle_exception('Daily limit reached. Come back tomorrow.');
}

$record->value += 1;
$DB->update_record($userid);

$user = core_user::get_user($userid, '*', MUST_EXIST);
core_user::require_active_user($user, true, true);

// Do the user log-in.
if (!$user = get_complete_user_data('id', $user->id)) {
    throw new moodle_exception('cannotfinduser', '', '', $user->id);
}

complete_user_login($user);
redirect($url);