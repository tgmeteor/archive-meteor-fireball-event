// <source lang="javascript">
/*
  EditTools support: add a selector, change into true buttons, enable for all text input fields
  If enabled in preferences, the script puts the buttons into the WikiEditor Toolbar
  The special characters to insert are defined at [[MediaWiki:Edittools]].
*/

importStylesheetURI('http://commons.wikimedia.org/wiki/MediaWiki:Edittools.css');

var EditTools = {
  createSelector: function () {
    $spec = $j('#specialchars');
    $sb = $j('#specialchars p.specialbasic');

    // Only care if there is more than one
    if (!$spec.length || $sb.length <= 1) return;

    $sel = $j(document.createElement('select'));

    $sel.change(function () {
      EditTools.chooseCharSubset();
    });

    $sb.each(function (i) {
      id = $j(this).attr('id').replace(/.([0-9A-F][0-9A-F])/g, '%$1').replace(/_/g, ' ');
      $sel.append('<option value='+ i +'>' + decodeURIComponent (id) + '</option>');
    });

    $spec.prepend($sel);

    this.chooseCharSubset();
  },

  chooseCharSubset: function () {
    $sb = $j('#specialchars p.specialbasic');

    id = $j('#specialchars select').val();

    $wanted = $sb.eq(id);
    this.makeButtons($wanted);

    $sb.hide();

    $wanted.css('display', 'inline');

  },

  makeButtons: function ($wanted) {
    $links = $wanted.find('a');

    $links.each(function () {
      $button = $j('<button type="button">');
      $button.text($j(this).text());

      $button.click($j(this).attr('onclick'));

      $j(this).replaceWith($button);
      $j(this).blur();
    });
    $wanted.contents().not($j('button')).remove();
  },
  makeToolbarButtons: function () {
    // Add  Edittool section
    $j('#wpTextbox1').wikiEditor('addToToolbar', {
      'sections': {
        'Edittools': {
          'type': 'booklet',
          'label': 'Edittools',
          'pages': {
            'Edittools1': {
              'layout': 'characters',
              'label': 'Edittools2'
            }
          }
        }
      }
    });


    $section = $j('.page-Edittools1 div');
    $links = $j('#specialchars p.specialbasic').first().find('a');
    $links.each(function () {
      $button = $j('<span>');
      $button.text($j(this).text());

      $button.click($j(this).attr('onclick'));
      $section.append($button);
    });
    $j('.mw-editTools').remove();
  },

  last_active_textfield: null,

  enableForAllFields: function () {
    $j('textarea, input').focus(function () {
      EditTools.last_active_textfield = this.id;
    });
    insertTags = EditTools.insertTags;
  },

  getTextArea: function () {
    var txtarea = null;
    if (EditTools.last_active_textfield !== null) txtarea = $j('#' + EditTools.last_active_textfield).get(0);
    if (!txtarea) {
      txtarea = $j('textarea').first().get(0);
    }
    return txtarea;
  },

  registerTextField : function (evt)
  {
    var e = evt || window.event;
    var node = e.target || e.srcElement;
    if (!node) return;
    EditTools.last_active_textfield = node.id;
    return true;
  },

  insertTags: function (tagOpen, tagClose, sampleText) {
    var txtarea = EditTools.getTextArea();
    if (!txtarea) return;

    /* Usability initiative compatibility */
    if (typeof $j.fn.textSelection != 'undefined') {
      $j(txtarea).textSelection('encapsulateSelection', {
        'pre': tagOpen,
        'peri': sampleText,
        'post': tagClose
      });
      return;
    }

    var selText, isSample = false;

    function checkSelectedText() {
      if (!selText) {
        selText = sampleText;
        isSample = true;
      } else if (selText.charAt(selText.length - 1) == ' ') { // Exclude ending space char
        selText = selText.substring(0, selText.length - 1);
        tagClose += ' '
      }
    }

    if (document.selection && document.selection.createRange) { // IE/Opera
      // Save window scroll position
      var winScroll = 0;
      if (document.documentElement && document.documentElement.scrollTop) winScroll = document.documentElement.scrollTop;
      else if (document.body) winScroll = document.body.scrollTop;
      // Get current selection  
      txtarea.focus();
      var range = document.selection.createRange();
      selText = range.text;
      // Insert tags
      checkSelectedText();
      range.text = tagOpen + selText + tagClose;
      // Mark sample text as selected
      if (isSample && range.moveStart) {
        if (window.opera) tagClose = tagClose.replace(/\n/g, "");
        range.moveStart('character', -tagClose.length - selText.length);
        range.moveEnd('character', -tagClose.length);
      }
      range.select();
      // Restore window scroll position
      if (document.documentElement && document.documentElement.scrollTop) document.documentElement.scrollTop = winScroll;
      else if (document.body) document.body.scrollTop = winScroll;
    } else if (txtarea.selectionStart || txtarea.selectionStart == '0') { // Mozilla
      // Save textarea scroll position
      var textScroll = txtarea.scrollTop;
      // Get current selection
      txtarea.focus();
      var startPos = txtarea.selectionStart;
      var endPos = txtarea.selectionEnd;
      selText = txtarea.value.substring(startPos, endPos);
      // Insert tags
      checkSelectedText();
      txtarea.value = txtarea.value.substring(0, startPos) + tagOpen + selText + tagClose + txtarea.value.substring(endPos);
      // Set new selection
      if (isSample) {
        txtarea.selectionStart = startPos + tagOpen.length;
        txtarea.selectionEnd = startPos + tagOpen.length + selText.length;
      } else {
        txtarea.selectionStart = startPos + tagOpen.length + selText.length + tagClose.length;
        txtarea.selectionEnd = txtarea.selectionStart;
      }
      // Restore textarea scroll position
      txtarea.scrollTop = textScroll;
    }
  },


  setup: function () {
    var i18n_edittools = {
      'en': 'Use old edittools' // default
    };
    if (wgUserName !== null) JSconfig.registerKey('oldEdittools', true, i18n_edittools[wgUserLanguage] || i18n_edittools['en'], 3);
    else JSconfig.registerKey('oldEdittools', false, i18n_edittools[wgUserLanguage] || i18n_edittools['en'], 3);

    //Decide whether to use the toolbar or the bottom div
    if ($j('#toolbar').length || JSconfig.keys['oldEdittools'] || $j('#wpUploadDescription').length) {
      EditTools.createSelector();
      EditTools.enableForAllFields();
    } else {
      EditTools.makeToolbarButtons();
      EditTools.enableForAllFields();
    }
  }
};
$j(document).ready(function () {
    EditTools.setup();
});
// </source>