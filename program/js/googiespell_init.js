rcube_webmail.prototype.googiespell_init = function (asseturl, baseurl, use_dict, lang_chck_spell, lang_rsm_edt, lang_close, lang_revert, lang_no_error_found, lang_learn_word, languages, currentLanguage) {
    window.googie = new GoogieSpell(
        asseturl + '/images/googiespell/',
        baseurl + '&lang=',
        use_dict
    );
    googie.lang_chck_spell = lang_chck_spell;
    googie.lang_rsm_edt = lang_rsm_edt;
    googie.lang_close = lang_close;
    googie.lang_revert = lang_revert;
    googie.lang_no_error_found = lang_no_error_found;
    googie.lang_learn_word = lang_learn_word;
    googie.setLanguages(languages);
    googie.setCurrentLanguage(currentLanguage);
    googie.setDecoration(false);
    googie.decorateTextarea(rcmail.env.composebody);
};
