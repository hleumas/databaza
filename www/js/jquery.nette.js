/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */

jQuery.extend({
	nette: {
		updateSnippet: function (id, html) {
			$("#" + id).html(html);
		},

		success: function (payload) {
			// redirect
			if (payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}

			// snippets
			if (payload.snippets) {
				for (var i in payload.snippets) {
					jQuery.nette.updateSnippet(i, payload.snippets[i]);
				}
			}
		}
	}
});

jQuery.ajaxSetup({
	success: jQuery.nette.success,
	dataType: "json"
});

$("div.gridito").livequery(function () {
$(this).gridito();
});

$("a.ajax").live("click", function (event) {
event.preventDefault();
$.get(this.href);
$.spin(event);
// zobrazení spinneru a nastavení jeho pozice
});

$.extend({spin: function(event) {
    $("#ajax-spinner").show().css({
        position: "absolute",
        left: event.pageX + 20,
        top: event.pageY + 40
    });}});

// spiner
$(function () {
    // vhodně nastylovaný div vložím po načtení stránky
    $('<div id="ajax-spinner"></div>').appendTo("body").ajaxStop(function () {
        // a při události ajaxStop spinner schovám a nastavím mu původní pozici
        $(this).hide().css({
            position: "fixed",
            left: "50%",
            top: "50%"
        });
    }).hide();
});
