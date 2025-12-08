<?php

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page

    $optionalsubsystems->add(new admin_setting_configcheckbox('usecomments', new lang_string('enablecomments', 'admin'), new lang_string('configenablecomments', 'admin'), 1));

    $optionalsubsystems->add(new admin_setting_configcheckbox('enablewebservices', new lang_string('enablewebservices', 'admin'), new lang_string('configenablewebservices', 'admin'), 0));

    $optionalsubsystems->add(new admin_setting_configcheckbox('enablerssfeeds', new lang_string('enablerssfeeds', 'admin'), new lang_string('configenablerssfeeds', 'admin'), 0));

    $optionalsubsystems->add(new admin_setting_configcheckbox('enableglobalsearch', new lang_string('enableglobalsearch', 'admin'),
        new lang_string('enableglobalsearch_desc', 'admin'), 0, 1, 0));

    $optionalsubsystems->add(new admin_setting_configcheckbox('messaging',
        new lang_string('messaging', 'admin'),
        new lang_string('configmessaging', 'admin'),
        1)
    );

    $optionalsubsystems->add(new admin_setting_configcheckbox('enablecustomreports',
        new lang_string('enablecustomreports', 'core_reportbuilder'),
        new lang_string('enablecustomreports_desc', 'core_reportbuilder'),
        1
    ));

    $fullunicodesupport = true;
    if ($DB->get_dbfamily() == 'mysql') {
        $collation = $DB->get_dbcollation();
        $collationinfo = explode('_', $collation);
        $charset = reset($collationinfo);
        $fullunicodesupport = $charset === 'utf8mb4';
    }

    if ($fullunicodesupport) {
        $optionalsubsystems->add(new admin_setting_configcheckbox(
            'allowemojipicker',
            new lang_string('allowemojipicker', 'admin'),
            new lang_string('configallowemojipicker', 'admin'),
            1
        ));
    } else {
        $optionalsubsystems->add(new admin_setting_description(
            'allowemojipicker',
            new lang_string('allowemojipicker', 'admin'),
            new lang_string('configallowemojipickerincompatible', 'admin')
        ));
    }
}
