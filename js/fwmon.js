function refreshData() {
	var apiUrl = 'json_api.php';

	/* resources request */
	$.ajax({
		url: apiUrl + '?q=resources',
		dataType: 'json',
		statusCode: {
			404: function () {
				console.log('404 when fetching resources.');
			}
		}
	}).done(function (data) {
		document.getElementById('modalBody').innerHTML   = data.modalBody;
		document.getElementById('modalFooter').innerHTML = data.modalFooter;
	});

	/* table request */
	var table = document.querySelector('table').getAttribute('data-table');
	if (table.length > 2) {
		$.ajax({
			url: apiUrl + '?q=' + table,
			dataType: 'json',
			statusCode: {
				404: function () {
					console.log('404 when fetching a table.');
				}
			}
		}).done(function (data) {
			document.querySelector('thead').innerHTML = data.tHead;
			document.querySelector('tbody').innerHTML = data.tBody;

			$('[role="tooltip"]').remove(); // edge case - the user is hovering a row
			$('[data-toggle="popover"]').popover();
		});
	}
}


$('[data-toggle="popover"]').popover();

var interval = 20; // seconds
setInterval(refreshData, interval*1000);