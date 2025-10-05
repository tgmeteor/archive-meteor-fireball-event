/* generated javascript */
var skin = 'vector';
var stylepath = 'http://bits.wikimedia.org/skins-1.5';

/* MediaWiki:Common.js */
//<source lang="javascript">
//Map over $j to $
//Will be done by ResourceLoader later on
//No script here is/should use prototype etc.
if (typeof $ == 'undefined') $=$j;


// AddPortletLink for all scripts
// addPortletLink will be removed with resourceloader, so I'll put a more compatible version here
// Additional code by Lupo
function addPortletLink(portlet, href, text, id, tooltip, accesskey, nextnode)
 {
    var target = null;
    switch (skin) {
    case 'standard':
    case 'cologneblue':
        $target = $j('#quickbar');
        break;
    case 'nostalgia':
        $target = $j('#footer');
        break;
    default:
        var root = document.getElementById(portlet);
        if (!root) {
            return null;
        }
        var node = root.getElementsByTagName('ul')[0];
        if (!node) {
            return null;
        }

        // unhide portlet if it was hidden before
        root.className = root.className.replace(/(^| )emptyPortlet( |$)/, "$2");

        var span = document.createElement('span');
        span.appendChild(document.createTextNode(text));

        var link = document.createElement('a');
        link.appendChild(span);
        link.href = href;

        var item = document.createElement('li');
        item.appendChild(link);
        if (id) {
            item.id = id;
        }

        if (accesskey) {
            link.setAttribute('accesskey', accesskey);
            tooltip += ' [' + accesskey + ']';
        }
        if (tooltip) {
            link.setAttribute('title', tooltip);
        }
        if (accesskey && tooltip) {
            updateTooltipAccessKeys(new Array(link));
        }

        if (nextnode && nextnode.parentNode == node) {
            node.insertBefore(item, nextnode);
        } else {
            node.appendChild(item);
            // IE compatibility (?)
        }

        return item;
    }

    if (!$target.length) return;
    
    $container = $j(document.createElement('span'));
    if (id) $container.attr('id', id);
    
    $container.append('<a href="'+ href + '" title="' + tooltip + '">' + text + '</a>');

    if (skin == 'nostalgia') {
        $container.append('&#124;')
    } else {
        $target.append('<br>');
    }
    $target.append($container);
    return $container;
}


/** onload handlers ************
 *  Simple fix such that crashes in one handler don't prevent later handlers from running.
 *
 *  Maintainer: [[User:Lupo]]
 */

if (typeof (onloadFuncts) != 'undefined') {

  // Enhanced version of jsMsg from wikibits.js. jsMsg can display only one message, subsequent
  // calls overwrite any previous message. This version appends new messages after already
  // existing ones.
  function jsMsgAppend (msg, className)
  {
    var msg_div = document.getElementById ('mw-js-message');
    var msg_log = document.getElementById ('mw-js-exception-log');
    if (!msg_log) {
      msg_log = document.createElement ('ul');
      msg_log.id = 'mw-js-exception-log';
      if (msg_div && msg_div.firstChild) {
        // Copy contents of msg_div into first li of msg_log
        var wrapper = msg_div.cloneNode (true);
        wrapper.id = "";
        wrapper.className = "";
        var old_stuff = document.createElement ('li');
        old_stuff.appendChild (wrapper);
        msg_log.appendChild (old_stuff);
      }
    }
    var new_item = document.createElement ('li');
    new_item.appendChild (msg);
    msg_log.appendChild (new_item);
    jsMsg (msg_log, className);
  }

  var Logger = {

    // Log an exception. If present, try to use a JS console (e.g., Firebug's). If no console is
    // present, or the user is a sysop, also put the error message onto the page itself.
    logException : function (ex) {
      try {
        var name = ex.name || "";
        var msg  = ex.message || "";
        var file = ex.fileName || ex.sourceURL || null; // Gecko, Webkit, others
        var line = ex.lineNumber || ex.line || null;    // Gecko, Webkit, others
        var logged = false;
        if (typeof (console) != 'undefined' && typeof (console.log) != 'undefined') {
          // Firebug, Firebug Lite, or browser-native or other JS console present. At the very
          // least, these will allow us to print a simple string.
          var txt = name + ': ' + msg;
          if (file) {
            txt = txt + '; ' + file;
            if (line) txt = txt + ' (' + line + ')';
          }
          if (typeof (console.error) != 'undefined') {
            if (   console.firebug
                || (   console.provider && console.provider.indexOf
                    && console.provider.indexOf ('Firebug') >= 0)
               )
            {
              console.error (txt + " %o", ex); // Use Firebug's object dump to write the exception
            } else {
              console.error (txt);
            }
          } else
            console.log (txt);
          logged = true;
        }
        if (!logged || wgUserGroups.join (' ').indexOf ('sysop') >= 0) {
          if (name.length == 0 && msg.length == 0 && !file) return; // Don't log if there's no info
          if (name.length == 0) name = 'Unknown error';
          // Also put it onto the page for sysops.
          var log  = document.createElement ('span');
          if (msg.indexOf ('\n') >= 0) {
            var tmp = document.createElement ('span');
            msg = msg.split ('\n');
            for (var i = 0; i < msg.length; i++) {
              tmp.appendChild (document.createTextNode (msg[i]));
              if (i+1 < msg.length) tmp.appendChild (document.createElement ('br'));
            }
            log.appendChild (document.createTextNode (name + ': '));
            log.appendChild (tmp);
          } else {
            log.appendChild (document.createTextNode (name + ': ' + msg));
          }
          if (file) {
            log.appendChild (document.createElement ('br'));
            var a = document.createElement ('a');
            a.href = file;
            a.appendChild (document.createTextNode (file));
            log.appendChild (a);
            if (line) log.appendChild (document.createTextNode (' (' + line + ')'));
          }
          jsMsgAppend (log, 'error');
        }
      } catch (anything) {
        // Swallow
      }
    }
  } // end Logger

  // Wrap a function with an exception handler and exception logging.
  function makeSafe (f) {
    return function () {
             try {
               return f.apply (this, arguments);
             } catch (ex) {
               Logger.logException (ex);
               return null;
             }
           };
  }

  // Wrap the already registered onload hooks
  for (var i = 0; i < onloadFuncts.length; i++)
    onloadFuncts[i] = makeSafe (onloadFuncts[i]);

  // Redefine addOnloadHook to catch future additions
  function addOnloadHook (hookFunct) {
    // Allows add-on scripts to add onload functions
    if (!doneOnloadHook) {
      onloadFuncts[onloadFuncts.length] = makeSafe (hookFunct);
    } else {
      makeSafe (hookFunct)();  // bug in MSIE script loading
    }
  }
} // end onload hook improvements

/** JSconfig ************
 * Global configuration options to enable/disable and configure
 * specific script features from [[MediaWiki:Common.js]] and
 * [[MediaWiki:Monobook.js]]
 * This framework adds config options (saved as cookies) to [[Special:Preferences]]
 * For a more permanent change you can override the default settings in your
 * [[Special:Mypage/monobook.js]]
 * for Example: JSconfig.keys[loadAutoInformationTemplate] = false;
 *
 *  Maintainer: [[User:Dschwen]]
 */

var JSconfig =
{
 prefix : 'jsconfig_',
 keys : {},
 meta : {},

 //
 // Register a new configuration item
 //  * name          : String, internal name
 //  * default_value : String or Boolean (type determines configuration widget)
 //  * description   : String, text appearing next to the widget in the preferences, or an hash-object
 //                    containing translations of the description indexed by the language code
 //  * prefpage      : Integer (optional), section in the preferences to insert the widget:
 //                     0 : User profile         User profile
 //                     1 : Skin                 Appearance
 //                     2 : Math                 Date and Time
 //                     3 : Files                Editing
 //                     4 : Date and time        Recent Changes
 //                     5 : Editing              Watchlist
 //                     6 : Recent changes       Search Options
 //                     7 : Watchlist            Misc
 //                     8 : Search               Gadgets
 //                     9 : Misc
 //
 // Access keys through JSconfig.keys[name]
 //
 registerKey : function( name, default_value, description, prefpage )
 {
  if( typeof JSconfig.keys[name] == 'undefined' )
   JSconfig.keys[name] = default_value;
  else {
   // all cookies are read as strings,
   // convert to the type of the default value
   switch( typeof default_value )
   {
    case 'boolean' : JSconfig.keys[name] = ( JSconfig.keys[name] == 'true' ); break;
    case 'number'  : JSconfig.keys[name] = JSconfig.keys[name]/1; break;
   }
  }

  JSconfig.meta[name] = {
   'description' :
    description[wgUserLanguage] || description.en ||
    ( typeof(description) == "string" && description ) ||
    "<i>en</i> translation missing",
   'page' : prefpage || 0, 'default_value' : default_value };

  // if called after setUpForm(), we'll have to add an extra input field
  if( JSconfig.prefsTabs ) JSconfig.addPrefsInput( name );
 },

 readCookies : function()
 {
  var cookies = document.cookie.split("; ");
  var p =JSconfig.prefix.length;
  var i;

  for( var key=0; cookies && key < cookies.length; key++ )
  {
   if( cookies[key].substring(0,p) == JSconfig.prefix )
   {
    i = cookies[key].indexOf('=');
    //alert( cookies[key] + ',' + key + ',' + cookies[key].substring(p,i) );
    JSconfig.keys[cookies[key].substring(p,i)] = cookies[key].substring(i+1);
   }
  }
 },

 writeCookies : function()
 {
  var expdate = new Date();
  expdate.setTime(expdate.getTime()+1000*60*60*24*3650);  // expires in 3560 days
  for( var key in JSconfig.keys )
   document.cookie = JSconfig.prefix + key + '=' + JSconfig.keys[key] + '; path=/; expires=' + expdate.toUTCString();
 },

 evaluateForm : function()
 {
  var w_ctrl,wt;
  //alert('about to save JSconfig');
  for( var key in JSconfig.meta ) {
   w_ctrl = document.getElementById( JSconfig.prefix + key )
   if( w_ctrl )
   {
    wt = typeof JSconfig.meta[key].default_value;
    switch( wt ) {
     case 'boolean' : JSconfig.keys[key] = w_ctrl.checked; break;
     case 'string' : JSconfig.keys[key] = w_ctrl.value; break;
    }
   }
  }

  JSconfig.writeCookies();
  return true;
 },

 prefsTabs : false,

 setUpForm : function()
 {
  var prefChild = document.getElementById('preferences');
  if( !prefChild ) return;
  prefChild = prefChild.childNodes;

  //
  // make a list of all preferences sections
  //
  var tabs = new Array;
  var len = prefChild.length;
  for( var key = 0; key < len; key++ ) {
   if( prefChild[key].tagName &&
       prefChild[key].tagName.toLowerCase() == 'fieldset' )
    tabs.push(prefChild[key]);
  }
  JSconfig.prefsTabs = tabs;

  //
  // Create Widgets for all registered config keys
  //
  for( var key in JSconfig.meta ) JSconfig.addPrefsInput(key);

  $j('#preferences').parent().submit(JSconfig.evaluateForm);
 },

 addPrefsInput : function( key ) {
  var w_div = document.createElement( 'DIV' );

  var w_label = document.createElement( 'LABEL' );
  var wt = typeof JSconfig.meta[key].default_value;
  switch ( wt ) {
   case 'boolean':
    JSconfig.meta[key].description = " " + JSconfig.meta[key].description;
    break;
   case 'string': default:
    JSconfig.meta[key].description += ": ";
    break;
  }
  w_label.appendChild( document.createTextNode( JSconfig.meta[key].description ) );
  w_label.htmlFor = JSconfig.prefix + key;

  var w_ctrl = document.createElement( 'INPUT' );
  w_ctrl.id = JSconfig.prefix + key;

  // before insertion into the DOM tree
  switch( wt ) {
   case 'boolean':
    w_ctrl.type = 'checkbox';
    w_div.appendChild( w_ctrl );
    w_div.appendChild( w_label );
    break;
   case 'string': default:
    w_ctrl.type = 'text';
    w_div.appendChild( w_label );
    w_div.appendChild( w_ctrl );
    break;
  }

  JSconfig.prefsTabs[JSconfig.meta[key].page].appendChild( w_div );

  // after insertion into the DOM tree
  switch( wt ) {
   case 'boolean' : w_ctrl.defaultChecked = w_ctrl.checked = JSconfig.keys[key]; break;
   case 'string' : w_ctrl.defaultValue = w_ctrl.value = JSconfig.keys[key]; break;
  }
 }
};

JSconfig.readCookies();
if( wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Preferences" )
 $j(document).ready(JSconfig.setUpForm);
/** extract a URL parameter from the current URL **********
 * From [[en:User:Lupin/autoedit.js]]
 *
 * paramName  : the name of the parameter to extract
 * url        : optional URL to extract the parameter from, document.location.href if not given.
 *
 * Local Maintainer: [[User:Dschwen]], [[User:Lupo]]
 */

function getParamValue( paramName, url)
{
 if (typeof (url) == 'undefined' || url === null) url = document.location.href;
 var cmdRe=RegExp( '[^#]*[&?]' + paramName + '=([^&#]*)' ); // Stop at hash
 var m=cmdRe.exec(url);
 if (m && m.length > 1) return decodeURIComponent(m[1]);
 return null;
}

/** &withJS= URL parameter *******
 * Allow to try custom scripts on the MediaWiki namespace without
 * editing [[Special:Mypage/monobook.js]]
 *
 * Maintainer: [[User:Platonides]], [[User:Lupo]]
 */
var extraJS = getParamValue("withJS"); // Leave here for backwards compatibility
(function (extraJS) {
 if (!extraJS) return;
 if (extraJS.match("^MediaWiki:[^&<>=%#]*\\.js$")) // Disallow some characters in file name
  importScript (extraJS);
 else {
  // Dont use alert but the jsMsg system. Run jsMsg only once the DOM is ready.
  $j(document).ready(function () {
   jsMsgAppend (document.createTextNode (extraJS + " javascript not allowed to be loaded."),'error');
  });
 }
})(extraJS);

/***** Edittools ********
 * Formatting buttons for special characters below the edit field
 * Also enables these buttons on any textarea or input field on
 * the page. Moved here from Monobook.js on 2009-09-09.
 *
 * Maintainers: [[User:Lupo]], [[User:DieBuche]]
 */
importScript('MediaWiki:Edittools.js');

//
// Collapsible tables
//
importScript('MediaWiki:CollapsibleTemplates.js');

/**** ImageAnnotator ******
 * Globally enabled per
 * http://commons.wikimedia.org/w/index.php?title=Commons:Village_pump&oldid=26818359#New_interface_feature
 *
 * Maintainer: [[User:Lupo]]
 ****/

if (wgNamespaceNumber != -1 && wgAction && (wgAction == 'view' || wgAction == 'purge')) {
  // Not on Special pages, and only if viewing the page
  if (typeof (ImageAnnotator_disable) == 'undefined' || !ImageAnnotator_disable) {
    // Don't even import it if it's disabled.
    importScript ('MediaWiki:Gadget-ImageAnnotator.js');
  }
}

/**** Special:Upload enhancements ******
 * moved to [[MediaWiki:Upload.js]]
 *
 *  Maintainer: [[User:Lupo]]
 ****/
JSconfig.registerKey('UploadForm_loadform', true,
 {
  'bg': 'Използване на логиката на новия формуляр за качвания',
  'en': 'Use new upload form logic', // default
  'mk': 'Искористете ја логиката на новиот образец за подигнување',
  'ru': 'Использовать новую логику формы загрузки'
 }, 3);
JSconfig.registerKey('UploadForm_newlayout', true,
 {
  'bg': 'Използване на облика на новия формуляр за качвания',
  'en': 'Use new upload form layout', // default
  'mk': 'Искористете го рувото на новиот образец за подигнување',
  'ru': 'Использовать новый интерфейс формы загрузки'
 }, 3);

function enableNewUploadForm ()
{
  var match = navigator.userAgent.match(/AppleWebKit\/(\d+)/);
  if (match) {
    var webKitVersion = parseInt(match[1]);
    if (webKitVersion < 420) return; // Safari 2 crashes hard with the new upload form...
  }
  var isNlWLM = (document.URL.indexOf ('uselang=nlwikilovesmonuments') >= 0);

  // honor JSConfig user settings
  if( !isNlWLM && !JSconfig.keys['UploadForm_loadform'] ) return;

  importScript( 'MediaWiki:UploadForm.js' );
  // Load additional enhancements for a special upload form (request by User:Multichill)
  if ( isNlWLM ) {
   importScript('MediaWiki:UploadFormNlWikiLovesMonuments.js');
  }
}

if (wgPageName == 'Special:Upload')
{
 importScript( 'MediaWiki:Upload.js' );
 // Uncomment the following line (the call to enableNewUploadForm) to globally enable the
 // new upload form. Leave the line *above* (the include of MediaWiki:Upload.js) untouched;
 // that script provides useful default behavior if the new upload form is disabled or
 // redirects to the old form in case an error occurs.
 enableNewUploadForm ();
}

// We may be running MediaWiki:UploadForm.js on this site. The following script changes the
// "reupload" links on image pages to go to the basic form.
if (wgNamespaceNumber == 6) importScript ('MediaWiki:UploadFormLinkFixer.js');
/**** QICSigs ******
 * Fix for the broken signatures in gallery tags
 * needed for [[COM:QIC]]
 *
 *  Maintainers: [[User:Dschwen]]
 ****/
if( wgPageName == "Commons:Quality_images_candidates/candidate_list" && wgAction == "edit" )
{
 importScript( 'MediaWiki:QICSigs.js' );
}

/**** VICValidate ******
 * Some basic form validation for creating new Valued image nominations
 * needed for [[COM:VIC]]
 *
 *  Maintainers: [[User:Dschwen]]
 ****/
if( wgPageName == "Commons:Valued_image_candidates" && wgAction == "view" )
{
 importScript( 'MediaWiki:VICValidate.js' );
}
/***** subPagesLink ********
 * Adds a link to subpages of current page
 *
 *  Maintainers: [[:he:משתמש:ערן]], [[User:Dschwen]]
 *
 *  JSconfig items: bool JSconfig.subPagesLink
 *                       (true=enabled (default), false=disabled)
 ****/
var subPagesLink =
{
 //
 // Translations of the menu item
 //
 i18n :
 {
  'bg': 'Подстраници',
  'ca': 'Subpàgines',
  'cs': 'Podstránky',
  'de': 'Unterseiten',
  'en': 'Subpages',    // default
  'et': 'Alamlehed',
  'eo': 'Subpaĝoj',
  'eu': 'Azpiorrialdeak',
  'es': 'Subpáginas',
  'fi': 'Alasivut',
  'fr': 'Sous-pages',
  'gl': 'Subpáxinas',
  'he': 'דפי משנה',
  'hr': 'Podstranice',
  'it': 'Sottopagine',
  'is': 'Undirsíður',
  'ko': '하위 문서 목록',
  'mk': 'Потстраници',
  'nl': "Subpagina's",
  'no': 'Undersider',
  'pl': 'Podstrony',
  'ru': 'Подстраницы'
 },

 install: function()
 {
  // honor user configuration
  if( !JSconfig.keys['subPagesLink'] ) return;

  if ( document.getElementById("t-whatlinkshere")
       &&  wgNamespaceNumber != -1   // Special:
       && wgNamespaceNumber != 6     // Image:
       &&  wgNamespaceNumber != 14   // Category:
     )
  {
   var subpagesText = subPagesLink.i18n[wgUserLanguage] || subPagesLink.i18n['en'];
   var subpagesLink = wgArticlePath.replace('$1','Special:Prefixindex/' + wgPageName +'/');

   addPortletLink( 'p-tb', subpagesLink, subpagesText, 't-subpages' );
  }
 }
}
JSconfig.registerKey('subPagesLink', true,
 {
  'bg': 'Показване на връзката Подстраници в менюто с инструменти',
  'cs': 'Zobrazovat v panelu nástrojů odkaz Podstránky',
  'en': 'Show a Subpages link in the toolbox', // default
  'mk': 'Покажи врска до потстраниците во алатникот',
  'pl': 'Pokaż w panelu bocznym link do podstron',
  'ru': 'Показывать ссылку на подстраницы в меню инструментов'
 }, 7);
$j(document).ready(subPagesLink.install);
/***** new os_createContainer ********
 * make the width of the search suggest window customizable
 *
 *  Maintainers: [[User:Dschwen]]
 ****/

// Translations of the message in the user preferences
if( typeof os_createContainer != 'undefined' ) {
  JSconfig.registerKey('os_suggest_width', "",
   {
    'bg': 'Ширина на падащото меню с AJAX предположения',
    'cs': 'Šířka AJAXového napovídače',
    'en': 'Custom AJAX suggestion box width', // default
    'mk': 'Широчина на кутијата со предлози со AJAX',
    'ru': 'Ширина выпадающей AJAX-подсказки'
   }, 6);
  var old_os_createContainer = os_createContainer;
  os_createContainer = function( r)
  {
   var c = old_os_createContainer( r );
   var w = JSconfig.keys['os_suggest_width'];
   if( w != "" ) c.style.width = w + "px";
   return c;
  }
}
/***** gallery_dshuf_prepare ********
 * prepare galleries which are surrounded by <div class="dshuf"></div>
 * for shuffling with dshuf (see below).
 *
 *  Maintainers: [[User:Dschwen]]
 ****/
function gallery_dshuf_prepare()
{
 var tables = document.getElementsByTagName("table");
 var divsorig, divs, newdiv, parent, j, i;

 for ( i = 0; i < tables.length; i++)
  if ( tables[i].className == 'gallery' &&
       tables[i].parentNode.className == 'dshuf' )
  {
   divsorig = tables[i].getElementsByTagName( 'div' );
   divs = [];
   for ( j = 0; j < divsorig.length; j++) divs.push(divsorig[j]);
   for ( j = 0; j < divs.length; j++)
    if ( divs[j].className == 'gallerybox' )
    {
     newdiv = document.createElement( 'DIV' );
     newdiv.className = 'dshuf dshufset' + i;
     while( divs[j].childNodes.length > 0 )
      newdiv.appendChild( divs[j].removeChild(divs[j].firstChild) );
     divs[j].appendChild( newdiv );
    }
  }
}
$j(document).ready(gallery_dshuf_prepare);
/***** dshuf ********
 * shuffles div elements with the class dshuf and
 * common class dshufsetX (X being an integer)
 * taken from http://commons.wikimedia.org/w/index.php?title=MediaWiki:Common.js&oldid=7380543
 *
 *  Maintainers: [[User:Gmaxwell]], [[User:Dschwen]]
 ****/
function dshuf(){
 var shufsets = {};
 var rx = new RegExp('dshuf'+'\\s+(dshufset\\d+)', 'i');
 var divs = document.getElementsByTagName("div");
 var i = divs.length;
 while( i-- )
 {
  if( rx.test(divs[i].className) )
  {
   if ( typeof shufsets[RegExp.$1] == "undefined" )
   {
    shufsets[RegExp.$1] = {};
    shufsets[RegExp.$1].inner = [];
    shufsets[RegExp.$1].member = [];
   }
   shufsets[RegExp.$1].inner.push( { key:Math.random(), html:divs[i].innerHTML } );
   shufsets[RegExp.$1].member.push(divs[i]);
  }
 }

 for( shufset in shufsets )
 {
  shufsets[shufset].inner.sort( function(a,b) { return a.key - b.key; } );
  i = shufsets[shufset].member.length;
  while( i-- )
  {
   shufsets[shufset].member[i].innerHTML = shufsets[shufset].inner[i].html;
   shufsets[shufset].member[i].style.display = "block";
  }
 }
}
$j(document).ready(dshuf);
//Adds a dismissable notice to Special:Watchlist
//Useful to use instead of the sitenotice for messages only
//relevant to registered users.
if( wgCanonicalSpecialPageName == "Watchlist" ) importScript( 'MediaWiki:WatchlistNotice.js' );

/***** localizeSignature ********
 * localizes the signature on Commons with the string in the user's preferred language
 *
 * Maintainer: [[User:Slomox]]
 ****/
function localizeSignature() {
 var talkTextLocalization = { ca: 'Discussió', cs: 'diskuse', de: 'Diskussion', fr: 'd', nds: 'Diskuschoon' };
 var talkText = talkTextLocalization[wgUserLanguage];
 if (!talkText) return;
 var spans=document.getElementsByTagName("span");
 for (var i = 0; i < spans.length; i++) {
  if ( spans[i].className == 'signature-talk' ) {
   spans[i].innerHTML = talkText;
  }
 }
}
$j(document).ready(localizeSignature);

//
// Add "Nominate for Deletion" to toolbar ([[MediaWiki:AjaxQuickDelete.js]])
// Maintainer: [[User:DieBuche]]
//
importScript('MediaWiki:AjaxQuickDelete.js');

//
// Import usergroup-specific stylesheet, only for admins atm
//
for( var key=0; wgUserGroups && key < wgUserGroups.length; key++ )
{
   if (wgUserGroups[key] =="sysop")
   {
       importStylesheet("MediaWiki:Admin.css");
   }
   else if (wgUserGroups[key] =="filemover")
   {
       importStylesheet("MediaWiki:Filemover.css");
   }
}

// Ajax Translation of /lang links, see [[MediaWiki:AjaxTranslation.js]]
// Maintainer: [[User:ערן]]
importScript('MediaWiki:AjaxTranslation.js');

// SVG images: adds links to rendered PNG images in different resolutions
function SVGThumbs() {
    var file = document.getElementById("file"); // might fail if MediaWiki can't render the SVG
    if (file && wgIsArticle && wgTitle.match(/\.svg$/i)) {
        var thumbu = file.getElementsByTagName('IMG')[0].src;
        if(!thumbu) return;

        function svgAltSize( w, title) {
            var path = thumbu.replace(/\/\d+(px-[^\/]+$)/, "/" + w + "$1");
            var a = document.createElement("A");
            a.setAttribute("href", path);
            a.appendChild(document.createTextNode(title));
            return a;
        }

        var p = document.createElement("p");
        p.className = "SVGThumbs";
        p.appendChild(document.createTextNode("This image rendered as PNG in other sizes"+": "));
        var l = [200, 500, 1000, 2000];
                for( var i = 0; i < l.length; i++ ) {
            p.appendChild(svgAltSize( l[i], l[i] + "px"));
            if( i < l.length-1 ) p.appendChild(document.createTextNode(", "));
                }
        p.appendChild(document.createTextNode("."));
        var info = getElementsByClassName( file.parentNode, 'div', 'fullMedia' )[0];
        if( info ) info.appendChild(p);
    }
}
$j(document).ready( SVGThumbs );

//Language & skin specific JavaScript and CSS.
//may be useful for renaming tab in main page in every language.
importScript('MediaWiki:Common.js/' + wgUserLanguage);
importStylesheet('MediaWiki:' + skin + '.css/' + wgUserLanguage);

/* Quick-adding a command CommonsDelinker's command line */
/* Local maintainer: [[User:Kwj2772]] */
// importScript('MediaWiki:CommonsDelinker.js');

/*Automatic language selection using javascript*/
importScript('MediaWiki:Multilingual description.js')

// per talkpage It will be useful to normalize date used by script (e.g. Flickrreview script)
function getISODate() { // UTC
    var date = new Date();
    var dd = date.getUTCDate();
    if (dd < 10) { dd = "0"+ dd.toString(); }
    var mm = date.getUTCMonth()+1;
    if (mm < 10) { mm = "0"+ mm.toString(); }
    var YYYY = date.getUTCFullYear();
    ISOdate = YYYY + '-' + mm + '-' + dd
    return (ISOdate);
}

// Sitenotice translation for all skins
$j(function(){
   if (wgUserLanguage != 'en') $j("#siteNotice p").load(wgServer+wgScript+"?title=MediaWiki:Sitenotice&uselang="+wgUserLanguage+" #bodyContent p");
});

// Hide title on all main pages and change the "Gallery" tab text to "Main page" (or equivalent
// in user's language) on all main pages and their talk pages
if (wgNamespaceNumber == 0 || wgNamespaceNumber == 1) {
    importScript('MediaWiki:MainPages.js');
}

//
// Change target of add-section links
// See Template:ChangeSectionLink
//
$j(document).ready(function ()
{
 var changeAddSection = document.getElementById('jsChangeAddSection')
 if (changeAddSection)
 {
  var addSection = document.getElementById('ca-addsection');
  if (addSection)
  {
   addSection.firstChild.setAttribute('href', wgScript +
    '?action=edit&section=new&title=' + encodeURIComponent(
    changeAddSection.getAttribute('title')));
  }
 }
});

/**
 * Add links to GlobalUsage and the CommonsDelinker log to file deletion log entries.
 *
 * Maintainer(s): [[User:Ilmari Karonen]]
 */
$j(document).ready(function () {
    // guard against multiple inclusion
    if (window.commonsDelinkerLogLinksAdded) return;
    window.commonsDelinkerLogLinksAdded = true;

    var content = document.getElementById("bodyContent") ||       // monobook & vector skins
                  document.getElementById("mw_contentholder") ||  // modern skin
                  document.getElementById("article");             // classic skins
    if (!content) return;

    var deletions = getElementsByClassName(content, "li", "mw-logline-delete");
    if (!deletions || !deletions.length) return;

    // create the links in advance so we can cloneNode() them quickly in the loop
    var guLink = document.createElement("a");
    guLink.className = "delinker-log-globalusage";
    guLink.appendChild(document.createTextNode("global usage"));

    var cdLink = document.createElement("a");
    cdLink.className = "delinker-log-link extiw";
    cdLink.appendChild(document.createTextNode("delinker log"));

    var span = document.createElement("span");
    span.className = "delinker-log-links";
    span.appendChild(document.createTextNode(" ("));
    span.appendChild(guLink);
    span.appendChild(document.createTextNode("; "));
    span.appendChild(cdLink);
    span.appendChild(document.createTextNode(")"));

    for (var i = 0; i < deletions.length; i++) {
        var match = null;
        for (var elem = deletions[i].firstChild; elem; elem = elem.nextSibling) {
            if (!elem.tagName || elem.tagName.toLowerCase() != 'a') continue;
            if (/mw-userlink/.test(elem.className)) continue;
            match = /^File:(.*)/.exec(getInnerText(elem));
            if (match) break;
        }
        if (match) {
            var filename = encodeURIComponent(match[1].replace(/ /g, "_"));
            guLink.href = wgScript + "?title=Special:GlobalUsage&target=" + filename;
            guLink.title = "Current usage of " + match[1] + " on all Wikimedia projects";
            cdLink.href = "http://toolserver.org/~delinker/index.php?image=" + filename;
            cdLink.title = "CommonsDelinker log for " + match[1];
            deletions[i].appendChild(span.cloneNode(true));
        }
    }
});

/*
 * Description: Stay on the secure server as much as possible
 * Maintainers: [[User:TheDJ]]
 */
if(wgServer == 'https://secure.wikimedia.org') {
    importScript( 'MediaWiki:Common.js/secure.js');
}

// Workaround for [[bugzilla:708]] via [[Template:InterProject]]
importScript('MediaWiki:InterProject.js');

//Extra interface tabs for (external) tools such as check usage
//This should add the possibility to opt-out via gadgets
//the "remove image tools" gadget will set load_extratabs to false,
//so this won't load. If that's undefined, assume opt-in
if(typeof (load_extratabs) == 'undefined') importScript('MediaWiki:Extra-tabs.js');

//</source>

/* MediaWiki:Vector.js */
/* <source lang="javascript"> Top of MediaWiki:Vector.js */

/* Anything that should be executed after the page and jQuery are loaded in this block */
jQuery(function(){

// MediaWiki doesn't support <noscript>
// Hide with JavaScript the other way arround by using class="noscript"
jQuery(".noscript").hide();

// Untill the bug is fixed to hide the License-dropdown on Reuploads this fix:
if(getParamValue('wpDestFile') !== null && getParamValue('wpForReUpload') === "1"){
  jQuery("tr.mw-htmlform-field-Licenses").hide();
}

});/* end of jQuery-document-ready */

// A workaround for bug 2831, http://bugzilla.wikimedia.org/show_bug.cgi?id=2831
// This comes from Wiktionary,
// http://en.wiktionary.org/w/index.php?title=MediaWiki:Monobook.js&diff=prev&oldid=1144333
if (/\.5B/.test(window.location.hash))
  window.location = window.location.hash.replace(/\.5B/g, "").replace(/\.5D/g, "");

//
// Wikiminiatlas for commons
//
if (wgServer == "https://secure.wikimedia.org") {
    var metaBase = "https://secure.wikimedia.org/wikipedia/meta";
} else {
    var metaBase = "http://meta.wikimedia.org";
}
importScriptURI(metaBase+'/w/index.php?title=MediaWiki:Wikiminiatlas.js' 
               + '&action=raw&ctype=text/javascript&smaxage=21600&maxage=86400' );

//
// Add ResizeGalleries script ([[MediaWiki talk:ResizeGalleries.js]])
//
// 
// Translations of the message in the user preferences  
var i18n_resize = {
 'bg': 'Оразмеряване на галериите и категориите според ширината на екрана',
 'mk': 'Променете ги широчините на галериите и категориите за да ги собере во екранот',
 'ru': 'Подстраивать ширину галерей и категорий (количество изображений в ряду) по размеру экрана',
 'en': 'Resize gallery and category widths to fit screen' // default
};
JSconfig.registerKey('resizeGalleries', true, i18n_resize[wgUserLanguage] || i18n_resize['en'], 3);
if( JSconfig.keys['resizeGalleries'] )
 importScript('MediaWiki:ResizeGalleries.js');


//Add a link to a RSS feed for each category page, in the toolbox.
importScript('MediaWiki:Catfood.js');

//
// Change target of add-section links
// See Template:ChangeSectionLink
//
addOnloadHook(function () 
{
 var changeAddSection = document.getElementById('jsChangeAddSection')
 if (changeAddSection)
 {
  var addSection = document.getElementById('ca-addsection');
  if (addSection)
  {
   addSection.firstChild.setAttribute('href', wgScript + 
    '?action=edit&section=new&title=' + encodeURIComponent(
    changeAddSection.getAttribute('title')));
  }
 }
});

// Attribution buttons
importScript('MediaWiki:Stockphoto.js');

/* Bottom of MediaWiki:Vector.js </source> */