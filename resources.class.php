<?php

function getProgressClass($percentage, $rev) {
	if ($rev) {
		if ($percentage < 25) return 'progress-bar-danger';
		if ($percentage < 50) return 'progress-bar-warning';
		if ($percentage < 75) return 'progress-bar-success';
		return 'progress-bar-info';
	}

	if ($percentage < 25) return 'progress-bar-info';
	if ($percentage < 50) return 'progress-bar-success';
	if ($percentage < 75) return 'progress-bar-warning';
	return 'progress-bar-danger';
}

?>
