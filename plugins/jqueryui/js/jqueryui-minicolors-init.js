rcube_webmail.prototype.jqueryui_minicolors_init = function (config) {
	$.fn.miniColors = $.fn.minicolors;
	$("input.colors").minicolors(config);
}
