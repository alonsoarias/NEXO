<?php

    require_once('../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/authlib.php');
    require_once($CFG->dirroot.'/user/lib.php');
    require_once($CFG->dirroot.'/'.$CFG->admin.'/user/user_bulk_forms.php');

    $delete       = optional_param('delete', 0, PARAM_INT);
    $confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
    $confirmuser  = optional_param('confirmuser', 0, PARAM_INT);
    $suspend      = optional_param('suspend', 0, PARAM_INT);
    $unsuspend    = optional_param('unsuspend', 0, PARAM_INT);
    $unlock       = optional_param('unlock', 0, PARAM_INT);
    $resendemail  = optional_param('resendemail', 0, PARAM_INT);

    admin_externalpage_setup('editusers');

    $sitecontext = context_system::instance();
    $site = get_site();

    $returnurl = new moodle_url('/admin/user.php');

    $PAGE->set_primary_active_tab('siteadminnode');
    $PAGE->navbar->add(get_string('userlist', 'admin'), $PAGE->url);

    // The $user variable is also used outside of these if statements.
    $user = null;
    if ($confirmuser and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);
        if (!$user = $DB->get_record('user', array('id'=>$confirmuser))) {
            throw new \moodle_exception('nousers');
        }

        $auth = get_auth_plugin($user->auth);

        $result = $auth->user_confirm($user->username, $user->secret);

        if ($result == AUTH_CONFIRM_OK or $result == AUTH_CONFIRM_ALREADY) {
            redirect($returnurl);
        } else {
            echo $OUTPUT->header();
            redirect($returnurl, get_string('usernotconfirmed', '', fullname($user, true)));
        }

    } else if ($resendemail && confirm_sesskey()) {
        if (!$user = $DB->get_record('user', ['id' => $resendemail, 'deleted' => 0])) {
            throw new \moodle_exception('nousers');
        }

        // Prevent spamming users who are already confirmed.
        if ($user->confirmed) {
            throw new \moodle_exception('alreadyconfirmed', 'moodle');
        }

        $returnmsg = get_string('emailconfirmsentsuccess');
        $messagetype = \core\output\notification::NOTIFY_SUCCESS;
        if (!send_confirmation_email($user)) {
            $returnmsg = get_string('emailconfirmsentfailure');
            $messagetype = \core\output\notification::NOTIFY_ERROR;
        }

        redirect($returnurl, $returnmsg, null, $messagetype);
    } else if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation
        require_capability('moodle/user:delete', $sitecontext);

        $user = $DB->get_record('user', array('id'=>$delete), '*', MUST_EXIST);

        if ($user->deleted) {
            throw new \moodle_exception('usernotdeleteddeleted', 'error');
        }
        if (is_siteadmin($user->id)) {
            throw new \moodle_exception('useradminodelete', 'error');
        }

        if ($confirm != md5($delete)) {
            echo $OUTPUT->header();
            $fullname = fullname($user, true);
            echo $OUTPUT->heading(get_string('deleteuser', 'admin'));

            $optionsyes = array('delete'=>$delete, 'confirm'=>md5($delete), 'sesskey'=>sesskey());
            $deleteurl = new moodle_url($returnurl, $optionsyes);
            $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

            echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), $deletebutton, $returnurl);
            echo $OUTPUT->footer();
            die;
        } else {
            if (delete_user($user)) {
                \core\session\manager::gc(); // Remove stale sessions.
                redirect($returnurl, get_string('deleteduserx', 'admin', fullname($user, true)));
            } else {
                \core\session\manager::gc(); // Remove stale sessions.
                echo $OUTPUT->header();
                echo $OUTPUT->notification($returnurl, get_string('deletednot', '', fullname($user, true)));
            }
        }
    } else if ($suspend and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id'=>$suspend, 'deleted'=>0))) {
            if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {
                $user->suspended = 1;
                // Force logout.
                \core\session\manager::destroy_user_sessions($user->id);
                user_update_user($user, false);
            }
        }
        redirect($returnurl);

    } else if ($unsuspend and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id'=>$unsuspend, 'deleted'=>0))) {
            if ($user->suspended != 0) {
                $user->suspended = 0;
                user_update_user($user, false);
            }
        }
        redirect($returnurl);

    } else if ($unlock and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id'=>$unlock, 'deleted'=>0))) {
            login_unlock_account($user);
        }
        redirect($returnurl);
    }

    echo $OUTPUT->header();

    echo html_writer::start_div('', ['data-region' => 'report-user-list-wrapper']);

    $bulkactions = new user_bulk_action_form(new moodle_url('/admin/user/user_bulk.php'),
        ['excludeactions' => ['displayonpage', 'download'], 'passuserids' => true, 'hidesubmit' => true],
        'post', '',
        ['id' => 'user-bulk-action-form']);
    $bulkactions->set_data(['returnurl' => $PAGE->url->out_as_local_url(false)]);

    $report = \core_reportbuilder\system_report_factory::create(\core_admin\reportbuilder\local\systemreports\users::class,
        context_system::instance(), parameters: ['withcheckboxes' => $bulkactions->has_bulk_actions()]);
    if (has_capability('moodle/user:create', $sitecontext)) {
        $url = new moodle_url('/user/editadvanced.php', ['id' => -1]);
        $report->set_report_action(new \core_reportbuilder\output\report_action(
            get_string('addnewuser', 'moodle'),
            ['class' => 'btn btn-primary ms-auto', 'data-action' => 'add-user', 'href' => (string) $url],
            'a',
        ));
    }

    echo $report->output();

    if ($bulkactions->has_bulk_actions()) {
        $PAGE->requires->js_call_amd('core_admin/bulk_user_actions', 'init');
        $bulkactions->display();
    }

    echo html_writer::end_div();

    echo $OUTPUT->footer();
