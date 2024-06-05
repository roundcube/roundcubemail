<?php

namespace Roundcube\WIP {
    if (!isset($legacyAutoloadClassName)) {
        function rcube_autoload_legacy(string $classname)
        {
            if (strpos($classname, '\\') === false) {
                $fqcn = 'Roundcube\WIP\\' . $classname;

                if (class_exists($fqcn) || interface_exists($fqcn) || trait_exists($fqcn)) {
                    $legacyAutoloadClassName = $classname;
                    require __FILE__;
                }
            }
        }

        spl_autoload_register(__NAMESPACE__ . '\rcube_autoload_legacy');
    }
}

namespace {
    if (isset($legacyAutoloadClassName)) {
        switch ($legacyAutoloadClassName) {
            case 'acl':
                class acl extends Roundcube\WIP\acl {}

                break;
            case 'additional_message_headers':
                class additional_message_headers extends Roundcube\WIP\additional_message_headers {}

                break;
            case 'archive':
                class archive extends Roundcube\WIP\archive {}

                break;
            case 'attachment_reminder':
                class attachment_reminder extends Roundcube\WIP\attachment_reminder {}

                break;
            case 'autologon':
                class autologon extends Roundcube\WIP\autologon {}

                break;
            case 'autologout':
                class autologout extends Roundcube\WIP\autologout {}

                break;
            case 'database_attachments':
                class database_attachments extends Roundcube\WIP\database_attachments {}

                break;
            case 'debug_logger':
                class debug_logger extends Roundcube\WIP\debug_logger {}

                break;
            case 'emoticons':
                class emoticons extends Roundcube\WIP\emoticons {}

                break;
            case 'enigma':
                class enigma extends Roundcube\WIP\enigma {}

                break;
            case 'enigma_driver':
                abstract class enigma_driver extends Roundcube\WIP\enigma_driver {}

                break;
            case 'enigma_driver_gnupg':
                class enigma_driver_gnupg extends Roundcube\WIP\enigma_driver_gnupg {}

                break;
            case 'enigma_driver_phpssl':
                class enigma_driver_phpssl extends Roundcube\WIP\enigma_driver_phpssl {}

                break;
            case 'enigma_engine':
                class enigma_engine extends Roundcube\WIP\enigma_engine {}

                break;
            case 'enigma_key':
                class enigma_key extends Roundcube\WIP\enigma_key {}

                break;
            case 'enigma_mime_message':
                class enigma_mime_message extends Roundcube\WIP\enigma_mime_message {}

                break;
            case 'enigma_signature':
                class enigma_signature extends Roundcube\WIP\enigma_signature {}

                break;
            case 'enigma_subkey':
                class enigma_subkey extends Roundcube\WIP\enigma_subkey {}

                break;
            case 'enigma_ui':
                class enigma_ui extends Roundcube\WIP\enigma_ui {}

                break;
            case 'example_addressbook':
                class example_addressbook extends Roundcube\WIP\example_addressbook {}

                break;
            case 'example_addressbook_backend':
                class example_addressbook_backend extends Roundcube\WIP\example_addressbook_backend {}

                break;
            case 'filesystem_attachments':
                class filesystem_attachments extends Roundcube\WIP\filesystem_attachments {}

                break;
            case 'help':
                class help extends Roundcube\WIP\help {}

                break;
            case 'hide_blockquote':
                class hide_blockquote extends Roundcube\WIP\hide_blockquote {}

                break;
            case 'http_authentication':
                class http_authentication extends Roundcube\WIP\http_authentication {}

                break;
            case 'identicon':
                class identicon extends Roundcube\WIP\identicon {}

                break;
            case 'identicon_engine':
                class identicon_engine extends Roundcube\WIP\identicon_engine {}

                break;
            case 'identity_select':
                class identity_select extends Roundcube\WIP\identity_select {}

                break;
            case 'jqueryui':
                class jqueryui extends Roundcube\WIP\jqueryui {}

                break;
            case 'krb_authentication':
                class krb_authentication extends Roundcube\WIP\krb_authentication {}

                break;
            case 'rcube_sieve':
                class rcube_sieve extends Roundcube\WIP\rcube_sieve {}

                break;
            case 'rcube_sieve_engine':
                class rcube_sieve_engine extends Roundcube\WIP\rcube_sieve_engine {}

                break;
            case 'rcube_sieve_forward':
                class rcube_sieve_forward extends Roundcube\WIP\rcube_sieve_forward {}

                break;
            case 'rcube_sieve_vacation':
                class rcube_sieve_vacation extends Roundcube\WIP\rcube_sieve_vacation {}

                break;
            case 'managesieve':
                class managesieve extends Roundcube\WIP\managesieve {}

                break;
            case 'markasjunk_amavis_blacklist':
                class markasjunk_amavis_blacklist extends Roundcube\WIP\markasjunk_amavis_blacklist {}

                break;
            case 'markasjunk_cmd_learn':
                class markasjunk_cmd_learn extends Roundcube\WIP\markasjunk_cmd_learn {}

                break;
            case 'markasjunk_dir_learn':
                class markasjunk_dir_learn extends Roundcube\WIP\markasjunk_dir_learn {}

                break;
            case 'markasjunk_edit_headers':
                class markasjunk_edit_headers extends Roundcube\WIP\markasjunk_edit_headers {}

                break;
            case 'markasjunk_email_learn':
                class markasjunk_email_learn extends Roundcube\WIP\markasjunk_email_learn {}

                break;
            case 'markasjunk_jsevent':
                class markasjunk_jsevent extends Roundcube\WIP\markasjunk_jsevent {}

                break;
            case 'markasjunk_sa_blacklist':
                class markasjunk_sa_blacklist extends Roundcube\WIP\markasjunk_sa_blacklist {}

                break;
            case 'markasjunk_sa_detach':
                class markasjunk_sa_detach extends Roundcube\WIP\markasjunk_sa_detach {}

                break;
            case 'markasjunk':
                class markasjunk extends Roundcube\WIP\markasjunk {}

                break;
            case 'new_user_dialog':
                class new_user_dialog extends Roundcube\WIP\new_user_dialog {}

                break;
            case 'new_user_identity':
                class new_user_identity extends Roundcube\WIP\new_user_identity {}

                break;
            case 'new_user_identity_ldap_backend':
                class new_user_identity_ldap_backend extends Roundcube\WIP\new_user_identity_ldap_backend {}

                break;
            case 'newmail_notifier':
                class newmail_notifier extends Roundcube\WIP\newmail_notifier {}

                break;
            case 'rcube_chpasswd_password':
                class rcube_chpasswd_password extends Roundcube\WIP\rcube_chpasswd_password {}

                break;
            case 'rcube_cpanel_password':
                class rcube_cpanel_password extends Roundcube\WIP\rcube_cpanel_password {}

                break;
            case 'rcube_dbmail_password':
                class rcube_dbmail_password extends Roundcube\WIP\rcube_dbmail_password {}

                break;
            case 'rcube_directadmin_password':
                class rcube_directadmin_password extends Roundcube\WIP\rcube_directadmin_password {}

                break;
            case 'rcube_domainfactory_password':
                class rcube_domainfactory_password extends Roundcube\WIP\rcube_domainfactory_password {}

                break;
            case 'rcube_dovecot_passwdfile_password':
                class rcube_dovecot_passwdfile_password extends Roundcube\WIP\rcube_dovecot_passwdfile_password {}

                break;
            case 'rcube_expect_password':
                class rcube_expect_password extends Roundcube\WIP\rcube_expect_password {}

                break;
            case 'rcube_gearman_password':
                class rcube_gearman_password extends Roundcube\WIP\rcube_gearman_password {}

                break;
            case 'rcube_hmail_password':
                class rcube_hmail_password extends Roundcube\WIP\rcube_hmail_password {}

                break;
            case 'rcube_httpapi_password':
                class rcube_httpapi_password extends Roundcube\WIP\rcube_httpapi_password {}

                break;
            case 'rcube_kpasswd_password':
                class rcube_kpasswd_password extends Roundcube\WIP\rcube_kpasswd_password {}

                break;
            case 'rcube_ldap_password':
                class rcube_ldap_password extends Roundcube\WIP\rcube_ldap_password {}

                break;
            case 'rcube_ldap_exop_password':
                class rcube_ldap_exop_password extends Roundcube\WIP\rcube_ldap_exop_password {}

                break;
            case 'rcube_ldap_ppolicy_password':
                class rcube_ldap_ppolicy_password extends Roundcube\WIP\rcube_ldap_ppolicy_password {}

                break;
            case 'rcube_ldap_samba_ad_password':
                class rcube_ldap_samba_ad_password extends Roundcube\WIP\rcube_ldap_samba_ad_password {}

                break;
            case 'rcube_ldap_simple_password':
                class rcube_ldap_simple_password extends Roundcube\WIP\rcube_ldap_simple_password {}

                break;
            case 'rcube_mailcow_password':
                class rcube_mailcow_password extends Roundcube\WIP\rcube_mailcow_password {}

                break;
            case 'rcube_miab_password':
                class rcube_miab_password extends Roundcube\WIP\rcube_miab_password {}

                break;
            case 'rcube_modoboa_password':
                class rcube_modoboa_password extends Roundcube\WIP\rcube_modoboa_password {}

                break;
            case 'rcube_pam_password':
                class rcube_pam_password extends Roundcube\WIP\rcube_pam_password {}

                break;
            case 'rcube_plesk_password':
                class rcube_plesk_password extends Roundcube\WIP\rcube_plesk_password {}

                break;
            case 'rcube_poppassd_password':
                class rcube_poppassd_password extends Roundcube\WIP\rcube_poppassd_password {}

                break;
            case 'rcube_pw_usermod_password':
                class rcube_pw_usermod_password extends Roundcube\WIP\rcube_pw_usermod_password {}

                break;
            case 'rcube_pwned_password':
                class rcube_pwned_password extends Roundcube\WIP\rcube_pwned_password {}

                break;
            case 'rcube_sasl_password':
                class rcube_sasl_password extends Roundcube\WIP\rcube_sasl_password {}

                break;
            case 'rcube_smb_password':
                class rcube_smb_password extends Roundcube\WIP\rcube_smb_password {}

                break;
            case 'rcube_sql_password':
                class rcube_sql_password extends Roundcube\WIP\rcube_sql_password {}

                break;
            case 'rcube_tinycp_password':
                class rcube_tinycp_password extends Roundcube\WIP\rcube_tinycp_password {}

                break;
            case 'rcube_virtualmin_password':
                class rcube_virtualmin_password extends Roundcube\WIP\rcube_virtualmin_password {}

                break;
            case 'rcube_vpopmaild_password':
                class rcube_vpopmaild_password extends Roundcube\WIP\rcube_vpopmaild_password {}

                break;
            case 'rcube_ximss_password':
                class rcube_ximss_password extends Roundcube\WIP\rcube_ximss_password {}

                break;
            case 'rcube_xmail_password':
                class rcube_xmail_password extends Roundcube\WIP\rcube_xmail_password {}

                break;
            case 'XMail':
                class XMail extends Roundcube\WIP\XMail {}

                break;
            case 'rcube_zxcvbn_password':
                class rcube_zxcvbn_password extends Roundcube\WIP\rcube_zxcvbn_password {}

                break;
            case 'password':
                class password extends Roundcube\WIP\password {}

                break;
            case 'reconnect':
                class reconnect extends Roundcube\WIP\reconnect {}

                break;
            case 'redundant_attachments':
                class redundant_attachments extends Roundcube\WIP\redundant_attachments {}

                break;
            case 'show_additional_headers':
                class show_additional_headers extends Roundcube\WIP\show_additional_headers {}

                break;
            case 'squirrelmail_usercopy':
                class squirrelmail_usercopy extends Roundcube\WIP\squirrelmail_usercopy {}

                break;
            case 'subscriptions_option':
                class subscriptions_option extends Roundcube\WIP\subscriptions_option {}

                break;
            case 'userinfo':
                class userinfo extends Roundcube\WIP\userinfo {}

                break;
            case 'vcard_attachments':
                class vcard_attachments extends Roundcube\WIP\vcard_attachments {}

                break;
            case 'virtuser_file':
                class virtuser_file extends Roundcube\WIP\virtuser_file {}

                break;
            case 'virtuser_query':
                class virtuser_query extends Roundcube\WIP\virtuser_query {}

                break;
            case 'zipdownload':
                class zipdownload extends Roundcube\WIP\zipdownload {}

                break;
            case 'zipdownload_mbox_filter':
                class zipdownload_mbox_filter extends Roundcube\WIP\zipdownload_mbox_filter {}

                break;
            case 'rcmail_action_contacts_copy':
                class rcmail_action_contacts_copy extends Roundcube\WIP\rcmail_action_contacts_copy {}

                break;
            case 'rcmail_action_contacts_delete':
                class rcmail_action_contacts_delete extends Roundcube\WIP\rcmail_action_contacts_delete {}

                break;
            case 'rcmail_action_contacts_edit':
                class rcmail_action_contacts_edit extends Roundcube\WIP\rcmail_action_contacts_edit {}

                break;
            case 'rcmail_action_contacts_export':
                class rcmail_action_contacts_export extends Roundcube\WIP\rcmail_action_contacts_export {}

                break;
            case 'rcmail_action_contacts_group_addmembers':
                class rcmail_action_contacts_group_addmembers extends Roundcube\WIP\rcmail_action_contacts_group_addmembers {}

                break;
            case 'rcmail_action_contacts_group_create':
                class rcmail_action_contacts_group_create extends Roundcube\WIP\rcmail_action_contacts_group_create {}

                break;
            case 'rcmail_action_contacts_group_delete':
                class rcmail_action_contacts_group_delete extends Roundcube\WIP\rcmail_action_contacts_group_delete {}

                break;
            case 'rcmail_action_contacts_group_delmembers':
                class rcmail_action_contacts_group_delmembers extends Roundcube\WIP\rcmail_action_contacts_group_delmembers {}

                break;
            case 'rcmail_action_contacts_group_rename':
                class rcmail_action_contacts_group_rename extends Roundcube\WIP\rcmail_action_contacts_group_rename {}

                break;
            case 'rcmail_action_contacts_import':
                class rcmail_action_contacts_import extends Roundcube\WIP\rcmail_action_contacts_import {}

                break;
            case 'rcmail_action_contacts_index':
                class rcmail_action_contacts_index extends Roundcube\WIP\rcmail_action_contacts_index {}

                break;
            case 'rcmail_action_contacts_list':
                class rcmail_action_contacts_list extends Roundcube\WIP\rcmail_action_contacts_list {}

                break;
            case 'rcmail_action_contacts_mailto':
                class rcmail_action_contacts_mailto extends Roundcube\WIP\rcmail_action_contacts_mailto {}

                break;
            case 'rcmail_action_contacts_move':
                class rcmail_action_contacts_move extends Roundcube\WIP\rcmail_action_contacts_move {}

                break;
            case 'rcmail_action_contacts_photo':
                class rcmail_action_contacts_photo extends Roundcube\WIP\rcmail_action_contacts_photo {}

                break;
            case 'rcmail_action_contacts_print':
                class rcmail_action_contacts_print extends Roundcube\WIP\rcmail_action_contacts_print {}

                break;
            case 'rcmail_action_contacts_qrcode':
                class rcmail_action_contacts_qrcode extends Roundcube\WIP\rcmail_action_contacts_qrcode {}

                break;
            case 'rcmail_action_contacts_save':
                class rcmail_action_contacts_save extends Roundcube\WIP\rcmail_action_contacts_save {}

                break;
            case 'rcmail_action_contacts_search':
                class rcmail_action_contacts_search extends Roundcube\WIP\rcmail_action_contacts_search {}

                break;
            case 'rcmail_action_contacts_search_create':
                class rcmail_action_contacts_search_create extends Roundcube\WIP\rcmail_action_contacts_search_create {}

                break;
            case 'rcmail_action_contacts_search_delete':
                class rcmail_action_contacts_search_delete extends Roundcube\WIP\rcmail_action_contacts_search_delete {}

                break;
            case 'rcmail_action_contacts_show':
                class rcmail_action_contacts_show extends Roundcube\WIP\rcmail_action_contacts_show {}

                break;
            case 'rcmail_action_contacts_undo':
                class rcmail_action_contacts_undo extends Roundcube\WIP\rcmail_action_contacts_undo {}

                break;
            case 'rcmail_action_contacts_upload_photo':
                class rcmail_action_contacts_upload_photo extends Roundcube\WIP\rcmail_action_contacts_upload_photo {}

                break;
            case 'rcmail_action_login_oauth':
                class rcmail_action_login_oauth extends Roundcube\WIP\rcmail_action_login_oauth {}

                break;
            case 'rcmail_action_login_oauth_backchannel':
                class rcmail_action_login_oauth_backchannel extends Roundcube\WIP\rcmail_action_login_oauth_backchannel {}

                break;
            case 'rcmail_action_mail_addcontact':
                class rcmail_action_mail_addcontact extends Roundcube\WIP\rcmail_action_mail_addcontact {}

                break;
            case 'rcmail_action_mail_attachment_delete':
                class rcmail_action_mail_attachment_delete extends Roundcube\WIP\rcmail_action_mail_attachment_delete {}

                break;
            case 'rcmail_action_mail_attachment_display':
                class rcmail_action_mail_attachment_display extends Roundcube\WIP\rcmail_action_mail_attachment_display {}

                break;
            case 'rcmail_action_mail_attachment_rename':
                class rcmail_action_mail_attachment_rename extends Roundcube\WIP\rcmail_action_mail_attachment_rename {}

                break;
            case 'rcmail_action_mail_attachment_upload':
                class rcmail_action_mail_attachment_upload extends Roundcube\WIP\rcmail_action_mail_attachment_upload {}

                break;
            case 'rcmail_action_mail_autocomplete':
                class rcmail_action_mail_autocomplete extends Roundcube\WIP\rcmail_action_mail_autocomplete {}

                break;
            case 'rcmail_action_mail_bounce':
                class rcmail_action_mail_bounce extends Roundcube\WIP\rcmail_action_mail_bounce {}

                break;
            case 'rcmail_action_mail_check_recent':
                class rcmail_action_mail_check_recent extends Roundcube\WIP\rcmail_action_mail_check_recent {}

                break;
            case 'rcmail_action_mail_compose':
                class rcmail_action_mail_compose extends Roundcube\WIP\rcmail_action_mail_compose {}

                break;
            case 'rcmail_action_mail_copy':
                class rcmail_action_mail_copy extends Roundcube\WIP\rcmail_action_mail_copy {}

                break;
            case 'rcmail_action_mail_delete':
                class rcmail_action_mail_delete extends Roundcube\WIP\rcmail_action_mail_delete {}

                break;
            case 'rcmail_action_mail_folder_expunge':
                class rcmail_action_mail_folder_expunge extends Roundcube\WIP\rcmail_action_mail_folder_expunge {}

                break;
            case 'rcmail_action_mail_folder_purge':
                class rcmail_action_mail_folder_purge extends Roundcube\WIP\rcmail_action_mail_folder_purge {}

                break;
            case 'rcmail_action_mail_get':
                class rcmail_action_mail_get extends Roundcube\WIP\rcmail_action_mail_get {}

                break;
            case 'rcmail_action_mail_getunread':
                class rcmail_action_mail_getunread extends Roundcube\WIP\rcmail_action_mail_getunread {}

                break;
            case 'rcmail_action_mail_group_expand':
                class rcmail_action_mail_group_expand extends Roundcube\WIP\rcmail_action_mail_group_expand {}

                break;
            case 'rcmail_action_mail_headers':
                class rcmail_action_mail_headers extends Roundcube\WIP\rcmail_action_mail_headers {}

                break;
            case 'rcmail_action_mail_import':
                class rcmail_action_mail_import extends Roundcube\WIP\rcmail_action_mail_import {}

                break;
            case 'rcmail_action_mail_index':
                class rcmail_action_mail_index extends Roundcube\WIP\rcmail_action_mail_index {}

                break;
            case 'rcmail_action_mail_list':
                class rcmail_action_mail_list extends Roundcube\WIP\rcmail_action_mail_list {}

                break;
            case 'rcmail_action_mail_list_contacts':
                class rcmail_action_mail_list_contacts extends Roundcube\WIP\rcmail_action_mail_list_contacts {}

                break;
            case 'rcmail_action_mail_mark':
                class rcmail_action_mail_mark extends Roundcube\WIP\rcmail_action_mail_mark {}

                break;
            case 'rcmail_action_mail_move':
                class rcmail_action_mail_move extends Roundcube\WIP\rcmail_action_mail_move {}

                break;
            case 'rcmail_action_mail_pagenav':
                class rcmail_action_mail_pagenav extends Roundcube\WIP\rcmail_action_mail_pagenav {}

                break;
            case 'rcmail_action_mail_search':
                class rcmail_action_mail_search extends Roundcube\WIP\rcmail_action_mail_search {}

                break;
            case 'rcmail_action_mail_search_contacts':
                class rcmail_action_mail_search_contacts extends Roundcube\WIP\rcmail_action_mail_search_contacts {}

                break;
            case 'rcmail_action_mail_send':
                class rcmail_action_mail_send extends Roundcube\WIP\rcmail_action_mail_send {}

                break;
            case 'rcmail_action_mail_sendmdn':
                class rcmail_action_mail_sendmdn extends Roundcube\WIP\rcmail_action_mail_sendmdn {}

                break;
            case 'rcmail_action_mail_show':
                class rcmail_action_mail_show extends Roundcube\WIP\rcmail_action_mail_show {}

                break;
            case 'rcmail_action_mail_viewsource':
                class rcmail_action_mail_viewsource extends Roundcube\WIP\rcmail_action_mail_viewsource {}

                break;
            case 'rcmail_action_settings_about':
                class rcmail_action_settings_about extends Roundcube\WIP\rcmail_action_settings_about {}

                break;
            case 'rcmail_action_settings_folder_create':
                class rcmail_action_settings_folder_create extends Roundcube\WIP\rcmail_action_settings_folder_create {}

                break;
            case 'rcmail_action_settings_folder_delete':
                class rcmail_action_settings_folder_delete extends Roundcube\WIP\rcmail_action_settings_folder_delete {}

                break;
            case 'rcmail_action_settings_folder_edit':
                class rcmail_action_settings_folder_edit extends Roundcube\WIP\rcmail_action_settings_folder_edit {}

                break;
            case 'rcmail_action_settings_folder_purge':
                class rcmail_action_settings_folder_purge extends Roundcube\WIP\rcmail_action_settings_folder_purge {}

                break;
            case 'rcmail_action_settings_folder_rename':
                class rcmail_action_settings_folder_rename extends Roundcube\WIP\rcmail_action_settings_folder_rename {}

                break;
            case 'rcmail_action_settings_folder_save':
                class rcmail_action_settings_folder_save extends Roundcube\WIP\rcmail_action_settings_folder_save {}

                break;
            case 'rcmail_action_settings_folder_size':
                class rcmail_action_settings_folder_size extends Roundcube\WIP\rcmail_action_settings_folder_size {}

                break;
            case 'rcmail_action_settings_folder_subscribe':
                class rcmail_action_settings_folder_subscribe extends Roundcube\WIP\rcmail_action_settings_folder_subscribe {}

                break;
            case 'rcmail_action_settings_folder_unsubscribe':
                class rcmail_action_settings_folder_unsubscribe extends Roundcube\WIP\rcmail_action_settings_folder_unsubscribe {}

                break;
            case 'rcmail_action_settings_folders':
                class rcmail_action_settings_folders extends Roundcube\WIP\rcmail_action_settings_folders {}

                break;
            case 'rcmail_action_settings_identities':
                class rcmail_action_settings_identities extends Roundcube\WIP\rcmail_action_settings_identities {}

                break;
            case 'rcmail_action_settings_identity_create':
                class rcmail_action_settings_identity_create extends Roundcube\WIP\rcmail_action_settings_identity_create {}

                break;
            case 'rcmail_action_settings_identity_delete':
                class rcmail_action_settings_identity_delete extends Roundcube\WIP\rcmail_action_settings_identity_delete {}

                break;
            case 'rcmail_action_settings_identity_edit':
                class rcmail_action_settings_identity_edit extends Roundcube\WIP\rcmail_action_settings_identity_edit {}

                break;
            case 'rcmail_action_settings_identity_save':
                class rcmail_action_settings_identity_save extends Roundcube\WIP\rcmail_action_settings_identity_save {}

                break;
            case 'rcmail_action_settings_index':
                class rcmail_action_settings_index extends Roundcube\WIP\rcmail_action_settings_index {}

                break;
            case 'rcmail_action_settings_prefs_edit':
                class rcmail_action_settings_prefs_edit extends Roundcube\WIP\rcmail_action_settings_prefs_edit {}

                break;
            case 'rcmail_action_settings_prefs_save':
                class rcmail_action_settings_prefs_save extends Roundcube\WIP\rcmail_action_settings_prefs_save {}

                break;
            case 'rcmail_action_settings_response_create':
                class rcmail_action_settings_response_create extends Roundcube\WIP\rcmail_action_settings_response_create {}

                break;
            case 'rcmail_action_settings_response_delete':
                class rcmail_action_settings_response_delete extends Roundcube\WIP\rcmail_action_settings_response_delete {}

                break;
            case 'rcmail_action_settings_response_edit':
                class rcmail_action_settings_response_edit extends Roundcube\WIP\rcmail_action_settings_response_edit {}

                break;
            case 'rcmail_action_settings_response_get':
                class rcmail_action_settings_response_get extends Roundcube\WIP\rcmail_action_settings_response_get {}

                break;
            case 'rcmail_action_settings_response_save':
                class rcmail_action_settings_response_save extends Roundcube\WIP\rcmail_action_settings_response_save {}

                break;
            case 'rcmail_action_settings_responses':
                class rcmail_action_settings_responses extends Roundcube\WIP\rcmail_action_settings_responses {}

                break;
            case 'rcmail_action_settings_upload':
                class rcmail_action_settings_upload extends Roundcube\WIP\rcmail_action_settings_upload {}

                break;
            case 'rcmail_action_settings_upload_display':
                class rcmail_action_settings_upload_display extends Roundcube\WIP\rcmail_action_settings_upload_display {}

                break;
            case 'rcmail_action_utils_error':
                class rcmail_action_utils_error extends Roundcube\WIP\rcmail_action_utils_error {}

                break;
            case 'rcmail_action_utils_html2text':
                class rcmail_action_utils_html2text extends Roundcube\WIP\rcmail_action_utils_html2text {}

                break;
            case 'rcmail_action_utils_killcache':
                class rcmail_action_utils_killcache extends Roundcube\WIP\rcmail_action_utils_killcache {}

                break;
            case 'rcmail_action_utils_modcss':
                class rcmail_action_utils_modcss extends Roundcube\WIP\rcmail_action_utils_modcss {}

                break;
            case 'rcmail_action_utils_save_pref':
                class rcmail_action_utils_save_pref extends Roundcube\WIP\rcmail_action_utils_save_pref {}

                break;
            case 'rcmail_action_utils_spell':
                class rcmail_action_utils_spell extends Roundcube\WIP\rcmail_action_utils_spell {}

                break;
            case 'rcmail_action_utils_spell_html':
                class rcmail_action_utils_spell_html extends Roundcube\WIP\rcmail_action_utils_spell_html {}

                break;
            case 'rcmail_action_utils_text2html':
                class rcmail_action_utils_text2html extends Roundcube\WIP\rcmail_action_utils_text2html {}

                break;
            case 'rcmail':
                class rcmail extends Roundcube\WIP\rcmail {}

                break;
            case 'rcmail_action':
                abstract class rcmail_action extends Roundcube\WIP\rcmail_action {}

                break;
            case 'rcmail_attachment_handler':
                class rcmail_attachment_handler extends Roundcube\WIP\rcmail_attachment_handler {}

                break;
            case 'rcmail_bounce_stream_filter':
                class rcmail_bounce_stream_filter extends Roundcube\WIP\rcmail_bounce_stream_filter {}

                break;
            case 'rcmail_html_page':
                class rcmail_html_page extends Roundcube\WIP\rcmail_html_page {}

                break;
            case 'rcmail_install':
                class rcmail_install extends Roundcube\WIP\rcmail_install {}

                break;
            case 'rcmail_oauth':
                class rcmail_oauth extends Roundcube\WIP\rcmail_oauth {}

                break;
            case 'rcmail_output':
                abstract class rcmail_output extends Roundcube\WIP\rcmail_output {}

                break;
            case 'rcmail_output_cli':
                class rcmail_output_cli extends Roundcube\WIP\rcmail_output_cli {}

                break;
            case 'rcmail_output_html':
                class rcmail_output_html extends Roundcube\WIP\rcmail_output_html {}

                break;
            case 'rcmail_output_json':
                class rcmail_output_json extends Roundcube\WIP\rcmail_output_json {}

                break;
            case 'rcmail_resend_mail':
                class rcmail_resend_mail extends Roundcube\WIP\rcmail_resend_mail {}

                break;
            case 'rcmail_sendmail':
                class rcmail_sendmail extends Roundcube\WIP\rcmail_sendmail {}

                break;
            case 'rcmail_string_replacer':
                class rcmail_string_replacer extends Roundcube\WIP\rcmail_string_replacer {}

                break;
            case 'rcmail_utils':
                class rcmail_utils extends Roundcube\WIP\rcmail_utils {}

                break;
            case 'rcube_cache_apc':
                class rcube_cache_apc extends Roundcube\WIP\rcube_cache_apc {}

                break;
            case 'rcube_cache_db':
                class rcube_cache_db extends Roundcube\WIP\rcube_cache_db {}

                break;
            case 'rcube_cache_memcache':
                class rcube_cache_memcache extends Roundcube\WIP\rcube_cache_memcache {}

                break;
            case 'rcube_cache_memcached':
                class rcube_cache_memcached extends Roundcube\WIP\rcube_cache_memcached {}

                break;
            case 'rcube_cache_redis':
                class rcube_cache_redis extends Roundcube\WIP\rcube_cache_redis {}

                break;
            case 'rcube_db_mysql':
                class rcube_db_mysql extends Roundcube\WIP\rcube_db_mysql {}

                break;
            case 'rcube_db_param':
                class rcube_db_param extends Roundcube\WIP\rcube_db_param {}

                break;
            case 'rcube_db_pgsql':
                class rcube_db_pgsql extends Roundcube\WIP\rcube_db_pgsql {}

                break;
            case 'rcube_db_sqlite':
                class rcube_db_sqlite extends Roundcube\WIP\rcube_db_sqlite {}

                break;
            case 'html':
                class html extends Roundcube\WIP\html {}

                break;
            case 'html_button':
                class html_button extends Roundcube\WIP\html_button {}

                break;
            case 'html_checkbox':
                class html_checkbox extends Roundcube\WIP\html_checkbox {}

                break;
            case 'html_hiddenfield':
                class html_hiddenfield extends Roundcube\WIP\html_hiddenfield {}

                break;
            case 'html_inputfield':
                class html_inputfield extends Roundcube\WIP\html_inputfield {}

                break;
            case 'html_passwordfield':
                class html_passwordfield extends Roundcube\WIP\html_passwordfield {}

                break;
            case 'html_radiobutton':
                class html_radiobutton extends Roundcube\WIP\html_radiobutton {}

                break;
            case 'html_select':
                class html_select extends Roundcube\WIP\html_select {}

                break;
            case 'html_table':
                class html_table extends Roundcube\WIP\html_table {}

                break;
            case 'html_textarea':
                class html_textarea extends Roundcube\WIP\html_textarea {}

                break;
            case 'rcube':
                class rcube extends Roundcube\WIP\rcube {}

                break;
            case 'rcube_addressbook':
                abstract class rcube_addressbook extends Roundcube\WIP\rcube_addressbook {}

                break;
            case 'rcube_addresses':
                class rcube_addresses extends Roundcube\WIP\rcube_addresses {}

                break;
            case 'rcube_cache':
                class rcube_cache extends Roundcube\WIP\rcube_cache {}

                break;
            case 'rcube_charset':
                class rcube_charset extends Roundcube\WIP\rcube_charset {}

                break;
            case 'rcube_config':
                class rcube_config extends Roundcube\WIP\rcube_config {}

                break;
            case 'rcube_contacts':
                class rcube_contacts extends Roundcube\WIP\rcube_contacts {}

                break;
            case 'rcube_content_filter':
                class rcube_content_filter extends Roundcube\WIP\rcube_content_filter {}

                break;
            case 'rcube_csv2vcard':
                class rcube_csv2vcard extends Roundcube\WIP\rcube_csv2vcard {}

                break;
            case 'rcube_db':
                class rcube_db extends Roundcube\WIP\rcube_db {}

                break;
            case 'rcube_dummy_plugin_api':
                class rcube_dummy_plugin_api extends Roundcube\WIP\rcube_dummy_plugin_api {}

                break;
            case 'rcube_image':
                class rcube_image extends Roundcube\WIP\rcube_image {}

                break;
            case 'rcube_imap':
                class rcube_imap extends Roundcube\WIP\rcube_imap {}

                break;
            case 'rcube_imap_cache':
                class rcube_imap_cache extends Roundcube\WIP\rcube_imap_cache {}

                break;
            case 'rcube_imap_generic':
                class rcube_imap_generic extends Roundcube\WIP\rcube_imap_generic {}

                break;
            case 'rcube_imap_search':
                class rcube_imap_search extends Roundcube\WIP\rcube_imap_search {}

                break;
            case 'rcube_imap_search_job':
                class rcube_imap_search_job extends Roundcube\WIP\rcube_imap_search_job {}

                break;
            case 'rcube_ldap':
                class rcube_ldap extends Roundcube\WIP\rcube_ldap {}

                break;
            case 'rcube_ldap_generic':
                class rcube_ldap_generic extends Roundcube\WIP\rcube_ldap_generic {}

                break;
            case 'rcube_message':
                class rcube_message extends Roundcube\WIP\rcube_message {}

                break;
            case 'rcube_message_header':
                class rcube_message_header extends Roundcube\WIP\rcube_message_header {}

                break;
            case 'rcube_message_header_sorter':
                class rcube_message_header_sorter extends Roundcube\WIP\rcube_message_header_sorter {}

                break;
            case 'rcube_message_part':
                class rcube_message_part extends Roundcube\WIP\rcube_message_part {}

                break;
            case 'rcube_mime':
                class rcube_mime extends Roundcube\WIP\rcube_mime {}

                break;
            case 'rcube_mime_decode':
                class rcube_mime_decode extends Roundcube\WIP\rcube_mime_decode {}

                break;
            case 'rcube_output':
                abstract class rcube_output extends Roundcube\WIP\rcube_output {}

                break;
            case 'rcube_plugin':
                abstract class rcube_plugin extends Roundcube\WIP\rcube_plugin {}

                break;
            case 'rcube_plugin_api':
                class rcube_plugin_api extends Roundcube\WIP\rcube_plugin_api {}

                break;
            case 'rcube_result_index':
                class rcube_result_index extends Roundcube\WIP\rcube_result_index {}

                break;
            case 'rcube_result_multifolder':
                class rcube_result_multifolder extends Roundcube\WIP\rcube_result_multifolder {}

                break;
            case 'rcube_result_set':
                class rcube_result_set extends Roundcube\WIP\rcube_result_set {}

                break;
            case 'rcube_result_thread':
                class rcube_result_thread extends Roundcube\WIP\rcube_result_thread {}

                break;
            case 'rcube_session':
                abstract class rcube_session extends Roundcube\WIP\rcube_session {}

                break;
            case 'rcube_smtp':
                class rcube_smtp extends Roundcube\WIP\rcube_smtp {}

                break;
            case 'rcube_spellchecker':
                class rcube_spellchecker extends Roundcube\WIP\rcube_spellchecker {}

                break;
            case 'rcube_spoofchecker':
                class rcube_spoofchecker extends Roundcube\WIP\rcube_spoofchecker {}

                break;
            case 'rcube_storage':
                abstract class rcube_storage extends Roundcube\WIP\rcube_storage {}

                break;
            case 'rcube_string_replacer':
                class rcube_string_replacer extends Roundcube\WIP\rcube_string_replacer {}

                break;
            case 'rcube_text2html':
                class rcube_text2html extends Roundcube\WIP\rcube_text2html {}

                break;
            case 'rcube_tnef_decoder':
                class rcube_tnef_decoder extends Roundcube\WIP\rcube_tnef_decoder {}

                break;
            case 'rcube_uploads':
                trait rcube_uploads
                {
                    use Roundcube\WIP\rcube_uploads;
                }

                break;
            case 'rcube_user':
                class rcube_user extends Roundcube\WIP\rcube_user {}

                break;
            case 'rcube_utils':
                class rcube_utils extends Roundcube\WIP\rcube_utils {}

                break;
            case 'rcube_vcard':
                class rcube_vcard extends Roundcube\WIP\rcube_vcard {}

                break;
            case 'rcube_washtml':
                class rcube_washtml extends Roundcube\WIP\rcube_washtml {}

                break;
            case 'rcube_session_db':
                class rcube_session_db extends Roundcube\WIP\rcube_session_db {}

                break;
            case 'rcube_session_memcache':
                class rcube_session_memcache extends Roundcube\WIP\rcube_session_memcache {}

                break;
            case 'rcube_session_memcached':
                class rcube_session_memcached extends Roundcube\WIP\rcube_session_memcached {}

                break;
            case 'rcube_session_php':
                class rcube_session_php extends Roundcube\WIP\rcube_session_php {}

                break;
            case 'rcube_session_redis':
                class rcube_session_redis extends Roundcube\WIP\rcube_session_redis {}

                break;
            case 'rcube_spellchecker_atd':
                class rcube_spellchecker_atd extends Roundcube\WIP\rcube_spellchecker_atd {}

                break;
            case 'rcube_spellchecker_enchant':
                class rcube_spellchecker_enchant extends Roundcube\WIP\rcube_spellchecker_enchant {}

                break;
            case 'rcube_spellchecker_googie':
                class rcube_spellchecker_googie extends Roundcube\WIP\rcube_spellchecker_googie {}

                break;
            case 'rcube_spellchecker_pspell':
                class rcube_spellchecker_pspell extends Roundcube\WIP\rcube_spellchecker_pspell {}

                break;
        }
    }
}
