/**
 * to benefit of [[:Template:Multilingual_description]]
 * 
 * Implements language selection for multilingual elements
 * 
 * In certain environments, it's not feasible to neatly box away each
 * different language into its own section of the site. By marking elements
 * multilingual, you can emulate this behavior by only displaying the 
 * message in the user's language. This reduces the "Tower of Babel" effect.
 * 
 * @author Edward Z. Yang (Ambush Commander), Rewritten by DieBuche
 */

/* Configuration: */

// in your monobook.js, set ls_enable = false to stop the javascript
// the cookie name we use to stash the info.
// change this if you are porting it to another wiki!
var ls_cookie = 'commonswiki_language_js';

// link to the language select page
var ls_help_url = 'http://meta.wikimedia.org/wiki/Meta:Language_select';

// strings that are part of the widgets
var ls_string_help = {
    'de': 'Sprachauswahl:',
    'en': 'Language select:',
    'eo': 'Lingvoelekto:',
    'fr': 'Selecteur de langue:',
    'ko': '언어 선택:',
    'mk': 'Јазик:',
    'nds': 'Spraakutwahl:',
    'nl': 'Taal:',
    'pl': 'Wybierz język:',
    'ru': 'Выбор языка:'
};
var ls_string_select = {
    'de': 'Auswahl',
    'en': 'Select',
    'eo': 'Elekti',
    'fr': 'Seulement ce langage',
    'ko': '선택',
    'mk': 'Одбери',
    'nds': 'Utwählen',
    'nl': 'Selecteer',
    'pl': 'Wybierz',
    'ru': 'Выбрать'
};
var ls_string_showall = {
    'de': 'Alle anzeigen',
    'en': 'Show all',
    'eo': 'ĉiuj',
    'fr': 'Tous les langages',
    'ko': '모두 보기',
    'mk': 'Сите',
    'nds': 'All wiesen',
    'nl': 'Toon alles',
    'pl': 'Pokaż wszystkie',
    'ru': 'Показать все'
};


// autodetects a browser language
function ls_getBrowserLanguage() {
    return navigator.userLanguage || navigator.language || navigator.browserLanguage;
}

// sets a new language to the cookie
function ls_setCookieLanguage(language) {
    $j.cookie(ls_cookie, escape(language), {
        expires: 100,
        path: '/'
    });
}

// deletes the cookie
function ls_deleteCookieLanguage(language) {
    $j.cookie(ls_cookie, null, {
        path: '/'
    });
}
function ls_deleteOldCookieLanguage(language) {
    $j.cookie(ls_cookie, null, {
        path: '/wiki'
    });
}
// grabs the ISO 639 language code based
// on either the browser or a supplied cookie
function ls_getLanguage() {
    var language = '';

    // Priority:
    //  1. Cookie
    //  2. wgUserLanguage global variable
    //  3. Browser autodetection
    // grab according to cookie
    language = $j.cookie(ls_cookie);

    // grab according to wgUserLanguage if user is logged in
    if (!language && window.wgUserLanguage && wgUserGroups !== null) {
        language = wgUserLanguage;
    }

    // grab according to browser if none defined
    if (!language) language = ls_getBrowserLanguage();

    // inflexible: can't accept multiple languages
    // remove dialect/region code, leaving only the ISO 639 code
    language = language.replace(/-.*?/, '');

    return language;
}

var ls_string_help_text = ls_string_help[wgUserLanguage] || ls_string_help['en'];
var ls_string_showall_text = ls_string_showall[wgUserLanguage] || ls_string_showall['en'];

// build widget for changing the language cookie
function ls_buildWidget(language) {

    $container = $j(document.createElement('div'));
    // link to language select description page
    $container.html('<a href="' + ls_help_url + '" class="ls_link">' + ls_string_help_text + '</a> ')
    $select = $j(document.createElement('select'));


    seen = {};
    $j('[lang]').each(function() {
        var lang = $j(this).attr('lang')
        if (!seen[lang]) {
            seen[lang] = true;
            $select.append('<option>' + lang + '</option>');
        }
    });
    $select.prepend('<option  value="showall">' + ls_string_showall_text + '</option>');
    $select.attr('id', 'langselector');
    $select.val(ls_getLanguage());
    $select.change(function() {
        ls_setCookieLanguage($j('#langselector').val());
        ls_apply($j('#langselector').val());
    });
    $container.append($select);
    if ($j('#file').length) $j('#file').append($container);
    else $j('#bodyContent').prepend($container);

}
var mls;

// main body of the function
function ls_init() {
    //if (typeof(ls_enable) != 'undefined') return;

    //Remove old cookie (has wrong path, too long expiry)
    ls_deleteOldCookieLanguage();

    //disabling the gadget on special pages
    if (wgCanonicalNamespace == "Special") return;

    // only activated in view , purge, historysubmit or submit mode
    if (! ((wgAction == 'view')
    || (wgAction == 'purge')
    || (wgAction == 'edit')
    || (wgAction == 'historysubmit')
    || (wgAction == 'submit')
    ))
    return;

    var collapsDesc = false;

    // grab an array of multilingual elements
    mls = $j('.multilingual');

    //Find {{en|...}} parent elements
    dls = $j('div.description[lang]').parent();

    //Only collaps if more than 4 descriptions
    dls.each(function() {
        if ($j(this).find('[lang]').length > 4 && $j(this).attr('id') != 'bodyContent' && $j(this).attr('id') != 'LangTableLangs') collapsDesc = true;
    })

    // Only build form if there are MLDs on page.
    if (!mls.length && !collapsDesc) return;

    if (collapsDesc) mls = dls.add('.multilingual');

    ls_buildWidget();
    ls_apply(ls_getLanguage());
}

function ls_apply(language) {
    // if language is blank, delete the cookie and then recalculate
    if (!language) {
        ls_deleteCookieLanguage();
        language = ls_getLanguage();
    }

    mls.each(function() {
        $iaParent = $j(this).parent('[className^=image_annotation_content]');
        if ($iaParent.length) return true;
        
        if( $j(this).attr('id') == 'bodyContent' || $j(this).attr('id') == 'LangTableLangs') return true;
        
        $requestedLang = $j(this).find('[lang=' + language + ']');

        if ($requestedLang.length) {
            $j(this).children('[lang!=' + language + ']').hide();
            $j(this).children('[lang=' + language + ']').show();
        } else {
            $j(this).children('[lang]').show();
        }
    });
}
// register as onload function
$j(document).ready(function() {
    ls_init();
});