<?php

global $CFG;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_collabcows', 'Collabco Endpoint Plugin');
    
    $ADMIN->add('localplugins', $settings);
    
    //$settings->add(new admin_setting_configtext('setting_name', 'display name','description', 'default value', PARAM_EMAIL));
    
    $settings->add(new admin_setting_configtext('local_collabcows_baseURL', get_string('baseURL', 'local_collabcows'), get_string('baseURL_desc', 'local_collabcows'), $CFG->wwwroot));
    
    $settings->add(new admin_setting_configtext('local_collabcows_salt', get_string('salt', 'local_collabcows'), get_string('salt_desc', 'local_collabcows'), null));
    $settings->add(new admin_setting_configtext('local_collabcows_secret', get_string('secret', 'local_collabcows'), get_string('secret_desc', 'local_collabcows'), null));
    $settings->add(new admin_setting_configtext('local_collabcows_token', get_string('token', 'local_collabcows'), get_string('token_desc', 'local_collabcows'), null));
    
    $settings->add(new admin_setting_configcheckbox('local_collabcows_metrics', get_string('metrics', 'local_collabcows'), get_string('metrics_desc', 'local_collabcows'), 0));
    $settings->add(new admin_setting_configcheckbox('local_collabcows_plaintext', get_string('plain', 'local_collabcows'), get_string('plain_desc', 'local_collabcows'), 0));
    $settings->add(new admin_setting_configcheckbox('local_collabcows_ciphertext', get_string('ciphertext', 'local_collabcows'), get_string('ciphertext_desc', 'local_collabcows'), 1));
    $settings->add(new admin_setting_configcheckbox('local_collabcows_debugdata', get_string('debugdata', 'local_collabcows'), get_string('debugdata_desc', 'local_collabcows'), 0));
    
    $settings->add(new admin_setting_configtext('local_collabcows_timedrift', get_string('timedrift', 'local_collabcows'), get_string('timedrift_desc', 'local_collabcows'), 90, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_collabcows_version', get_string('version', 'local_collabcows'), get_string('version_desc', 'local_collabcows'), null));

    //$settings->add(new admin_setting_configtext('local_collabcows_prefix', get_string('prefix', 'local_collabcows'), get_string('prefix_desc', 'local_collabcows'), null));
    

}


?>