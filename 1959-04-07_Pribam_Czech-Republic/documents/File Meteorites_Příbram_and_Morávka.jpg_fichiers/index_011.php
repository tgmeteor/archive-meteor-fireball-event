/*
* StockPhoto - shows download and attribution buttons
* Original code 2010-09/10 by [[User:Magnus Manske]]
*/

function getParamValue(paramName, url) {
	if (typeof(url) == 'undefined' || url === null) url = document.location.href;
	var cmdRe = RegExp('[^#]*[&?]' + paramName + '=([^&#]*)'); // Stop at hash
	var m = cmdRe.exec(url);
	if (m && m.length > 1) return decodeURIComponent(m[1]);
	return null;
}


// Disabled for Opera 9.27 and below
if (typeof StockPhoto == 'undefined' && wgNamespaceNumber == 6 && (wgAction == 'view' || wgAction == 'purge') && (!$j.browser.opera || ($j.browser.opera && parseFloat($j.browser.version) > 9.27))) {

	// Import CSS definitions
	importStylesheet('MediaWiki:Stockphoto.css');

	// jQuery UI is not loaded on all pages:
	if (jQuery.ui == undefined) {
		importStylesheetURI(wgServer + '/w/extensions/UsabilityInitiative/css/vector/jquery-ui-1.7.2.css');
		importScriptURI(wgServer + '/w/extensions/UsabilityInitiative/js/js2stopgap/jui.combined.min.js');
	}

	var StockPhoto = {

		// Config
		rev: '31',
		show_social_bookmarks: false,

		// Misc
		ui_icon_download: 'http://upload.wikimedia.org/wikipedia/commons/thumb/9/92/Gnome-document-save.svg/50px-Gnome-document-save.svg.png',
		ui_icon_web: 'http://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Gnome-emblem-web.svg/50px-Gnome-emblem-web.svg.png',
		ui_icon_wiki: 'http://upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Tango_style_Wikipedia_Icon.svg/50px-Tango_style_Wikipedia_Icon.svg.png',
		ui_icon_email: 'http://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Gnome-mail-send.svg/50px-Gnome-mail-send.svg.png',
		ui_icon_help: 'http://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Dialog-information_on.svg/50px-Dialog-information_on.svg.png',
		ui_icon_remove: 'http://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Emblem-unreadable.svg/20px-Emblem-unreadable.svg.png',
		information_template_hints: ['fileinfotpl_desc', 'fileinfotpl_src'],
		icons_only: ['midi', 'ogg', 'flac'],
		horizontal_ui: ['midi', 'ogg', 'flac', 'pdf', 'djvu'],
		//license_patterns: "^Artistic-2$", "^BSD images$", "^OS OpenData$", "^Mozilla Public License$"],
		ogg_icon: stylepath + '/common/images/icons/fileicon-ogg.png',
		stockphoto_code1: undefined,
		stockphoto_author: undefined,
		stockphoto_license: undefined,
		nicetitle: undefined,
		file_icon: undefined,
		file_url: undefined,
		backlink_url: undefined,
		attribution: '',
		fade_target: '',
		gfdl_note: false,
		sbm_counter: 1,
		fromCommons: false,
		attrRequired: true,

		init: function () {
			if (typeof stockphoto_prevent != "undefined") return;
			if ($j.cookie && $j.cookie('StockPhotoDisabled')) return;
			if ($j('#file').length < 1) return;
			this.small_horizontal_layout = false;
			if (wgUserName !== null) this.small_horizontal_layout = true;
			if (getParamValue('stockphoto_show_social_bookmarks') !== null) this.show_social_bookmarks = true;
			var has_information = false;
			$j.each(this.information_template_hints, function (k, v) {
				if ($j('#' + v).length !== 0) has_information = true;
			});

			// No {{Information}}
			if (!has_information) return;

			// Has one or more problemtags
			// Changed to also include renames and normal deletes
			if ($j('.nuke').length) return;

			$j('#stockphoto_base').remove();
			var img_width = $j('#file img').width();
			var img_height = $j('#file img').height();
			var xoff = img_width + 40;
			var yoff = $j('#file').position().top + 5;
			if (!horizontal && img_height < 300) this.small_horizontal_layout = true;
			var horizontal = this.small_horizontal_layout;
			$j.each(this.horizontal_ui, function (k, v) {
				v = new RegExp('\.' + v + '$', 'i');
				if (wgTitle.match(v)) horizontal = true;
			});
			if ($j(window).width() < 1030) horizontal = true;

			// Initialize values
			this.share_this(-1);
			var html = "<div id='stockphoto_base' ";
			if (horizontal) html += "class='horizontal' >";
			else if (!$j("body.rtl").length) html += "class='vertical' style='left:" + xoff + "px;top:" + yoff + "px'>";
			else html += "class='vertical' style='left: 0px;top:" + yoff + "px'>";

			html += this.add_button_row(this.ui_icon_download, "call_download", this.i18n.download, this.i18n.all_sizes, horizontal);
			html += this.add_button_row(this.ui_icon_web, "call_web", this.i18n.use_this_file_web_short, this.i18n.on_a_website, horizontal);
			html += this.add_button_row(this.ui_icon_wiki, "call_wiki", this.i18n.use_this_file_wiki_short, this.i18n.on_a_wiki, horizontal);
			html += this.add_button_row(this.ui_icon_email, "send_email", this.i18n.email_link_short, this.i18n.to_this_file, horizontal);
			html += this.add_button_row(this.ui_icon_help, "call_help", this.i18n.information, this.i18n.about_reusing, horizontal);
			html += '<a title="' + this.i18n.remove_icons + '" id="stockphoto_remove"><img src="' + this.ui_icon_remove + '" /></a>';
			if (this.show_social_bookmarks) html += this.add_social_icons(horizontal);
			html += "</div>";
			if (this.small_horizontal_layout && $j("#file").length) $j("#filetoc").empty().append(html);
			else $j("#filetoc").after(html);
			$j('#stockphoto_remove').click(function () {
				$j.cookie('StockPhotoDisabled', true, {
					expires: 60,
					path: '/'
				});
				$j('#stockphoto_base').remove();
			});

			$j('#stockphoto_base img').parent().fadeTo(0, 0.7);
			$j('#stockphoto_base div').hover(function (evt) {
				$j(this).find('div:first a').fadeTo('fast', 1);
			}, function (evt) {
				$j(this).find('div:first a').fadeTo('fast', 0.7);
			});

		},

		// add_social_icons : function ( horizontal ) {
		// 	var elm = "div" ;
		// 	if ( $j.browser.msie && horizontal ) elm = "span" ;
		// 	var nl = encodeURIComponent("\n");
		// 	var bl = encodeURIComponent(StockPhoto.backlink_url);
		// 	var fl = encodeURIComponent(StockPhoto.file_url);
		// 	var ti = encodeURIComponent(StockPhoto.nicetitle + " - " + this.complete_attribution_text+" "+StockPhoto.i18n.from_wikimedia_commons);
		// 	var look = encodeURIComponent(StockPhoto.i18n.look_what_i_found)+bl+encodeURIComponent(" / ")+ti ;
		// 	var ret = "<"+elm+" id='stockphoto_social_bookmarks' " ;
		// 	ret += ">" ;
		// 	ret += this.add_social_icon ( this.i18n.share_on_facebook , "http://upload.wikimedia.org/wikinews/en/5/55/Facebook.png" , "http://www.facebook.com/sharer.php?u="+bl+"&q="+ti );
		// 	ret += this.add_social_icon ( this.i18n.share_on_digg , "http://upload.wikimedia.org/wikinews/en/9/95/Digg-icon.png" , "http://digg.com/submit?url="+bl+"&title="+ti );
		// 	ret += this.add_social_icon ( this.i18n.share_on_delicious , "http://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Delicious.svg/16px-Delicious.svg.png" , "http://delicious.com/post?url="+bl+"&title="+ti );
		// 	ret += this.add_social_icon ( this.i18n.share_on_reddit , "http://upload.wikimedia.org/wikinews/en/1/10/Reddit.png" , "http://reddit.com/submit?url="+bl+"&title="+ti );
		// 	ret += this.add_social_icon ( this.i18n.share_on_stumbleupon , "http://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Stumbleupon.svg/16px-Stumbleupon.svg.png" , "http://stumbleupon.com/submit?url="+bl+"&title="+ti );
		// 	if ( !this.small_horizontal_layout ) ret += "<br/>" ;
		// 	ret += this.add_social_icon ( this.i18n.share_on_yahoo_buzz , "http://upload.wikimedia.org/wikinews/en/d/de/Yahoo_buzz.png" , "http://buzz.yahoo.com/buzz?targetUrl="+bl+"&headline="+ti );
		// 	ret += this.add_social_icon ( this.i18n.share_on_identi_ca , "http://upload.wikimedia.org/wikipedia/commons/thumb/f/f1/Identica_Icon.png/16px-Identica_Icon.png" , "http://identi.ca/index.php?action=newnotice&status_textarea="+look );
		// 	ret += this.add_social_icon ( this.i18n.share_on_google_buzz , "http://upload.wikimedia.org/wikinews/en/4/4e/Buzz_icon.png" , "http://www.google.com/buzz/post?imageurl="+fl+"&message="+bl+nl+ti );
		// 	ret += this.add_social_icon ( this.i18n.share_on_twitter , "http://upload.wikimedia.org/wikinews/en/f/f7/Twitter.png" , "http://twitter.com/share?url=" + bl + "&text=" + encodeURIComponent(this.i18n.look_what_i_found) + ti + "&related=WM_POTD" );
		// 	ret += "</"+elm+">" ;
		// 	return ret ;
		// } ,
		// 
		// add_social_icon : function ( title , image_url , target_url ) {
		// 	var sbmid = "stockphoto_social_bookmarks_" + StockPhoto.sbm_counter ;
		// 	StockPhoto.sbm_counter++ ;
		// 	var ret = "<span class='stockphoto_social_icon' id='" + sbmid + "'" ;
		// 	ret += ">" ;
		// 	ret += "<a href='" + target_url + "' target='_blank' ><img title='" + title + "' border='0' src='" + image_url + "' /></a>" ;
		// 	ret += "</span>" ;
		// 	return ret ;
		// } ,
		add_button_row: function (icon_url, fkt, txt, html, horizontal) {
			if (this.small_horizontal_layout) {
				icon_url = icon_url.replace('/50px-', '/20px-');
			}
			var imgid = "stockphoto_icon_" + fkt;
			var a = "<a href='#' title='" + txt + " " + html + "' onclick='StockPhoto." + fkt + "(); return false;'>";

			var ret = "<span id='stockphoto_" + fkt + "'>";
			ret += "<span class='stockphoto_buttonrow_icon'>" + a + "<img id='" + imgid + "' src='" + icon_url + "' /></a></span>";
			ret += "<span class='stockphoto_buttonrow_text'>" + a;
			if (this.small_horizontal_layout) ret += txt + "</a>";
			else ret += "<b>" + txt + "</b></a><br/>" + html;
			ret += "</span></span>";
			return ret;
		},

		stockphoto_get_thumbnail_url: function (width) {
			if (this.isset(this.file_icon)) return this.file_icon;
			var thumb_url;
			var alt_title = wgCanonicalNamespace + ":" + wgTitle;
			$j('#file img').each(function () {
				if ($j(this).attr('alt') != alt_title) return;
				thumb_url = $j(this).attr('src').split('/');
			});

			var last = thumb_url.pop().replace(/^\d+px-/, width + 'px-');
			thumb_url.push(last);
			thumb_url = thumb_url.join('/');
			return thumb_url;
		},

		make_html_textarea: function () {
			var width = $j('#stockphoto_html_select').val();
			var type = $j("input[name='stockphoto_code_type']:checked").val();

			var thumb_url = this.stockphoto_get_thumbnail_url(width);

			var t;
			if (type == "html") t = "<a title='" + this.escapeAttribute(this.complete_attribution_text) + "' href='" + this.backlink_url + "'><img width='" + width + "' alt='" + this.escapeAttribute(this.nicetitle) + "' src='" + thumb_url + "'/></a>";
			else if (type == "bbcode") t = "[url=" + this.backlink_url + "][img]" + thumb_url + "[/img][/url]\n[url=" + this.backlink_url + "]" + this.nicetitle + "[/url]" + this.stockphoto_license + ", " + this.i18n.by + " " + this.stockphoto_author + ", " + this.i18n.from_wikimedia_commons;
			$j('#stockphoto_html').text(t);
		},

		get_author_attribution: function (use_html) {

			var author = $j.trim($j("#fileinfotpl_aut + td").text());
			var source = $j.trim($j("#fileinfotpl_src + td").text());

			// Remove boiler template; not elegant, but...
			if (author.match(/This file is lacking author information/)) author = '';
			if (author.match(/^[Uu]nknown$/)) author = '';
			author = author.replace(/\s*\(talk\)$/i, "");

			if (author.indexOf('Original uploader was') != -1) {
				author = author.replace(/\s*Original uploader was\s*/g, "");
				this.fromCommons = true;
			}
			// Remove boiler template; not elegant, but...
			if (source.match(/This file is lacking source information/)) source = '';
			if (author !== '' && $j('#own-work').length) { // Remove "own work" notice
				source = '';
				this.fromCommons = true;
			}
			if (author !== '' && source.length > 50) source = ''; // Remove long source info
			if (author.substr(0, 3) == "[&#9660;]") {
				author = author.substr(3);
				author = $j.trim(author.split("Description").shift());
			}

			this.attribution = '';
			if (author !== '') this.attribution = author;
			if (source != '') {
				if (this.attribution != '') this.attribution += " (" + source + ")";
				else this.attribution = source;
			}
			this.stockphoto_author = this.attribution;
			if (author !== '') this.attribution = this.i18n.by_u + " " + this.attribution;
			else this.attribution = this.i18n.see_page_for_author;

			if ($j('#creator').length) {
				this.attribution = $j('#creator').text();
			}

			if ($j('licensetpl_aut').length) {
				if (use_html) this.attribution = $j('licensetpl_aut').html();
				else this.attribution = $j('licensetpl_aut').text();
			}

			if ($j('licensetpl_attr').length) {
				if (use_html) this.attribution = $j('licensetpl_attr').html();
				else this.attribution = $j('licensetpl_attr').text();
			}

			if ($j("#fileinfotpl_credit + td").length) {
				if (use_html) this.attribution = $j("#fileinfotpl_credit + td").html();
				else this.attribution = $j("#fileinfotpl_credit + td").text();
			}

		},

		get_license: function (generate_html) {
			var licenses = new Array();
			$readable = $j('.licensetpl');

			if (!$readable.length) {
				this.stockphoto_license = "[" + this.i18n.see_page_for_license + "]";
				return;
			}
			$readable.each(function () {
				var cL = {};

				cL['link'] = $j(this).find('.licensetpl_link').html();
				cL['short'] = $j(this).find('.licensetpl_short').html();
				cL['long'] = $j(this).find('.licensetpl_long').html();
				cL['attr'] = $j(this).find('.licensetpl_attr').html();
				cL['aut'] = $j(this).find('.licensetpl_aut').html();
				cL['link_req'] = $j(this).find('.licensetpl_link_req').html();
				cL['attr_req'] = $j(this).find('.licensetpl_attr_req').html();

				if (cL['short'] !== null) licenses.push(cL);
			});

			if (licenses.length > 0) {
				$j.each(licenses, function (k, v) {
					if (v['attr_req'] == "false") StockPhoto.attrRequired = false;
					if (v['short'].indexOf('GFDL') != -1) StockPhoto.gfdl_note = true;
					if (generate_html && v['link']) {
						licenses[k] = '<a href="' + v['link'] + '">' + v['short'] + '</a>';
					} else {
						if (v.link_req == "true") {
							licenses[k] = v['short'] + ' (' + v['link'] + ')';
						} else {
							licenses[k] = v['short'];
						}
					}
				});

				if (licenses.length > 1) {
					var l2 = licenses.pop();
					var l1 = licenses.pop();
					licenses.push(l1 + " " + this.i18n.or + " " + l2);
				}
				this.stockphoto_license = " [" + licenses.join(', ') + "]";
			} else {
				this.stockphoto_license = " [" + this.i18n.see_page_for_license + "]";
			}
		},

		get_attribution_text: function () {
			from = (this.fromCommons) ? this.i18n.from_wikimedia_commons : this.i18n.via_wikimedia_commons;
			html = ($j("#stockphoto_attribution_html:checked").length) ? true : false;

			this.get_license(html);
			this.get_author_attribution(html);

			if ($j("#fileinfotpl_credit + td").length) text = this.attribution;
			else text = this.attribution + this.stockphoto_license;

			if (html) text += ", <a href='" + this.escapeAttribute(this.backlink_url) + "'>" + from + "</a>";
			else text += ", " + from;

			return text;
		},

		refresh_attribution: function () {
			$j("#stockphoto_attribution").val(StockPhoto.get_attribution_text());
		},

		createDialogRow: function (label, prefill, id) {
			idtext = (id) ? "id='" + id + "'" : ""
			return "<div class='stockphoto_dialog_row'><b>" + label + ":</b><br><input type='text' readonly " + idtext + " onClick=select() value='" + prefill + "'/></div>";
		},

		share_this: function (ui_mode) {
			this.complete_attribution_text = this.get_attribution_text();

			this.file_url = $j("#file > a").attr("href");
			if (!this.file_url) this.file_url = $j("#file > div > div > a").attr("href");
			if (!this.file_url) this.file_url = $j("div.fullMedia a").attr("href");

			this.nicetitle = wgTitle.split('.');
			this.nicetitle.pop();
			this.nicetitle = this.nicetitle.join('.');

			$j.each(this.icons_only, function (i, v) {
				var re = new RegExp('\.' + v + '$', 'i');
				if (!wgPageName.match(re)) return;
				StockPhoto.file_icon = StockPhoto.ogg_icon;
			});

			this.backlink_url = "http://commons.wikimedia.org/wiki/" + encodeURI(wgPageName);

			var widths = [75, 100, 120, 240, 500, 640, 800, 1024];

			if (ui_mode == -1) return;

			var html = "";
			html += this.createDialogRow(this.i18n.page_url, this.escapeAttribute(this.backlink_url));
			html += this.createDialogRow(this.i18n.file_url, this.escapeAttribute(this.file_url));
			html += this.createDialogRow(this.i18n.attribution, this.escapeAttribute(this.complete_attribution_text), 'stockphoto_attribution');
			html += "<input id='stockphoto_attribution_html' onclick='StockPhoto.refresh_attribution()' type='checkbox' /><label for='stockphoto_attribution_html'>" + this.i18n.html + "</label>";
			if (this.gfdl_note) html += "<br/><span class='stockphoto_note'>" + this.i18n.gfdl_warning + "</span>";
			if (!this.attrRequired) html += "<br/><span class='stockphoto_note'>" + this.i18n.no_attr + "</span>";

			switch (ui_mode) {
			case 1:

				dtitle = this.i18n.download_this_file;
				if (this.isset(this.file_url)) {
					html += "<div><b>" + this.i18n.download_image_file + ":</b><br>";
					var dl_links = new Array();
					$j.each(widths, function (i, v) {
						if (StockPhoto.isset(StockPhoto.file_icon)) return;
						dl_links.push("<a href='" + StockPhoto.stockphoto_get_thumbnail_url(v) + "'>" + v + "px</a>");
					});
					if (this.file_url) dl_links.push("<a href='" + this.file_url + "'>" + this.i18n.full_resolution + "</a>");
					if (dl_links.length) html += dl_links.join(" | ");
					else html += "<i>" + this.i18n.not_available + "</i>";
					html += "</div>";
				}


				break;

			case 2:
				dtitle = this.i18n.use_this_file_web;
				html += "<div class='stockphoto_dialog_row'><div style='float:right'>";
				html += "<input type='radio' name='stockphoto_code_type' value='html' id='stockphoto_code_type_html' onchange='StockPhoto.make_html_textarea();return false' checked /><label for='stockphoto_code_type_html'>" + StockPhoto.i18n.html + "</label> ";
				html += "<input type='radio' name='stockphoto_code_type' value='bbcode' id='stockphoto_code_type_bbcode' onchange='StockPhoto.make_html_textarea();return false' /><label for='stockphoto_code_type_bbcode'>" + StockPhoto.i18n.bbcode + "</label> ";

				html += "<select id='stockphoto_html_select' onchange='StockPhoto.make_html_textarea();return false'>";
				var best_fit = 75;
				if (this.isset(this.file_icon)) {
					best_fit = 120;
					html += "<option value='120'>120" + this.i18n.px_wide_icon + "</option>";
				} else {
					$j.each(widths, function (i, v) {
						if (v <= $j('#file img').width()) best_fit = v;
						html += "<option value='" + v + "'>" + v + StockPhoto.i18n.px_wide + "</option>";
					});
				}
				html += "</select></div>";
				html += "<b>" + this.i18n.html + "/" + this.i18n.bbcode + ":</b><textarea onClick=select() id='stockphoto_html' style='font-size:9pt'>";
				html += "</textarea></div>";

				break;

			case 3:
				dtitle = this.i18n.use_this_file_wiki;

				html = this.createDialogRow(this.i18n.thumbnail, this.escapeAttribute("[[File:" + wgTitle + "|thumb|" + this.nicetitle + "]]"));
				html += this.createDialogRow(this.i18n.image, this.escapeAttribute("[[File:" + wgTitle + "|" + this.nicetitle + "]]"));

				break;
			}

			$j("<div style='display:none' id='stockphoto_dialog'>" + html + "</div>").dialog({
				modal: true,
				width: 610,
				height: "auto",
				title: dtitle,
				close: function () {
					$j(this).remove();
				}
			});
			$j('#stockphoto_html_select').val(best_fit);

			this.make_html_textarea();
			$j('#stockphoto_attribution_html').prev().css('width', '90%');
		},

		call_download: function () {
			StockPhoto.share_this(1);
		},

		call_web: function () {
			StockPhoto.share_this(2);
		},

		call_wiki: function () {
			StockPhoto.share_this(3);
		},

		call_help: function () {
			window.location.href = wgArticlePath.replace("$1", StockPhoto.i18n.reusing_content_url);
		},

		send_email: function () {
			var url = "mailto:?subject=" + encodeURIComponent(StockPhoto.nicetitle) + "&body=" + encodeURIComponent(StockPhoto.backlink_url + "\n\n" + this.complete_attribution_text + " " + StockPhoto.i18n.from_wikimedia_commons);
			window.location.href = url;
		},

		escapeAttribute: function (s) {
			if (typeof s == "undefined") return "";
			return s.replace(/\n/g, ' ').replace(/\r/g, ' ').replace(/"/g, '&quot;');
		},

		isset: function (v) {
			return (typeof(v) != 'undefined');
		},

		i18n: {
			download: 'Download',
			download_this_file: "Download this file",
			use_this_file_web: "Use this file on the web",
			use_this_file_web_short: "Use this file",
			use_this_file_wiki: "Use this file on a wiki",
			use_this_file_wiki_short: "Use this file",
			email_link_short: "Email a link",
			information: "Information",
			remove_icons: "Remove these icons",
			all_sizes: "all sizes",
			on_a_website: "on the web",
			on_a_wiki: "on a wiki",
			to_this_file: "to this file",
			about_reusing: "about reusing",
			look_what_i_found: "Look what I found on Wikimedia Commons : ",
			from_wikimedia_commons: "from Wikimedia Commons",
			via_wikimedia_commons: "via Wikimedia Commons",
			by: "by",
			by_u: "By",
			see_page_for_author: "See page for author",
			see_page_for_license: "see page for license",
			page_url: "Page URL",
			file_url: "File URL",
			attribution: "Attribution",
			no_attr: "Attribution not legally required",
			or: "or",
			gfdl_warning: "Using this file might require attaching a full copy of the <a href='http://en.wikipedia.org/wiki/GNU_Free_Documentation_License'>GFDL</a>",
			download_image_file: "Download image file",
			full_resolution: "Full resolution",
			not_available: "not available",
			share_this_file: "Share this file",
			html: "HTML",
			bbcode: "BBCode",
			px_wide_icon: "px wide (icon)",
			px_wide: "px wide",
			wikipedia_instant_commons: "Wikimedia/InstantCommons",
			thumbnail: "Thumbnail",
			image: "Image",
			share_on_facebook: "Bookmark with Facebook",
			share_on_digg: "Share on Digg.com",
			share_on_delicious: "Share on delicious",
			share_on_reddit: "Share on reddit.com",
			share_on_stumbleupon: "Share on stumbleupon.com",
			share_on_yahoo_buzz: "Share on Yahoo! Buzz",
			share_on_identi_ca: "Share on identi.ca",
			share_on_google_buzz: "Share on Google Buzz",
			share_on_twitter: "Share on twitter.com",
			reusing_content_url: "Commons:Reusing_content_outside_Wikimedia"
		}
	}

	if (wgUserLanguage != 'en') {
		importScript('MediaWiki:Stockphoto.js/' + wgUserLanguage);
	}
	$j(document).ready(function () {
		StockPhoto.init();
	});
}

// i18n on subpages [[MediaWiki:StockPhoto.js/langcode]]:
// StockPhoto.i18n = { ... }