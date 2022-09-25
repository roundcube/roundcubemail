$(document).ready(function () {
	if (window.rcmail && window.rcmail.env.skin == 'elastic') {
		rcmail.addEventListener('init', function () {

			let days = rcmail.env.stay_loggedin_days;
			let txt = rcmail.gettext('toggle', 'stay_loggedin');
			txt = txt.replace("#", days);

			let html = '';
			html += '<tr class="form-group row">';
			html += '<td class="title" style="display: none;"></td>';
			html += '<td class="input input-group input-group-lg">';
			html +=   '<div class="custom-control custom-switch" style="padding: 1em 0;">';
			html +=     '<input type="checkbox" class="custom-control-input" id="_stay_loggedin" name="_stay_loggedin" value="1">';
			html +=     '<label class="custom-control-label" for="_stay_loggedin">' + txt + '</label>';
			html +=   '</div>';
			html += '</td>';
			html += '</tr>';

			let element = $('#login-form table tbody');
			element.append(html);

		});
	}
});
