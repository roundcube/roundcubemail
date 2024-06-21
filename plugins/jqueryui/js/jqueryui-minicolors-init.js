rcmail.addEventListener('jqueryui-minicolors-init', function (config) {
	$.fn.miniColors = $.fn.minicolors;
	$("input.colors").minicolors(config);
});
