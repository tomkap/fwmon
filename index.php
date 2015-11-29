<?php

require('routeros_api.class.php');

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

$API = new routerosAPI();
$API->debug = false;

$config = json_decode(file_get_contents('./config.json'), true);

if (!$API->connect($config['hostname'], $config['username'], $config['password'])) {
	die('API connection failed, aborting...');
}

$results = $API->comm("/ip/firewall/$_GET[table]/print");
$resources = $API->comm("/system/resource/print");
$resources = $resources['0']; // <- nice api

$mem_perc = round(($resources['free-memory']/$resources['total-memory']) * 100);
$hdd_perc = round(($resources['free-hdd-space']/$resources['total-hdd-space']) * 100);

$API->disconnect();

?><!DOCTYPE html><html>
<head>
	<title>FWMon :: MikroTik Firewall Monitor (https://github.com/tomkap/fwmon/)</title>

	<link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" href="css/custom.min.css">
</head>

<style>
	body {
		width: 85%;
		margin: 0 auto;
		margin-bottom: 150px;
	}
	tr {
		cursor: pointer;
	}

	.modal-body {
		text-align: center;
	}
	.modal-footer {
		text-align: center;
	}
	.modal-footer .btn-group {
		margin-top: 30px;
	}
	.progress-header {
		float: left;
		width: 45%;
		text-align: right;
		margin-right: 15px;
	}
	.progress {
		margin-top: 15px;
	}
	p b {
		margin-right: 6px;
	}
</style>

<body>
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">System resources</h4>
			</div>

			<div class="modal-body">
				<p><b>Platform:</b> <?php echo $resources['platform'] ?></p>
				<p><b>Model:</b> <?php echo $resources['board-name'] ?></p>
				<p><b>Version:</b> <?php echo $resources['version'] ?></p>
				<p><b>CPU:</b><?php echo $resources['cpu'] . ' @ ' . $resources['cpu-frequency'] . 'MHz'; ?></p>
			</div>

			<div class="modal-footer">
				<p><b>Uptime:</b> <?php echo $resources['uptime']; ?></p>

				<b class="progress-header">CPU (<font size="4em"><?php echo $resources['cpu-load']; ?>%</font>)</b>
				<div class="progress progress-striped">
					<div class="progress-bar <?php echo getProgressClass($resources['cpu-load'], false); ?>" style="width: <?php echo $resources['cpu-load']; ?>%"></div>
				</div>

				<b class="progress-header">Free memory (<font size="4em"><?php echo $mem_perc; ?>%</font>, <?php echo $resources['free-memory']; ?>KB)</b>
				<div class="progress progress-striped">
					<div class="progress-bar <?php echo getProgressClass($mem_perc, true); ?>" style="width: <?php echo $mem_perc; ?>%"></div>
				</div>

				<b class="progress-header">Free disk (<font size="4em"><?php echo $hdd_perc; ?>%</font>, <?php echo $resources['free-hdd-space']; ?>KB)</b>
				<div class="progress progress-striped">
					<div class="progress-bar <?php echo getProgressClass($hdd_perc, true); ?>" style="width: <?php echo $hdd_perc; ?>%"></div>
				</div>

				<!--
				<b class="progress-header">Disk bad blocks (<font size="4em"><?php echo $resources['bad-blocks']; ?>%</font>)</b>
				<div class="progress progress-striped">
					<div class="progress-bar <?php echo getProgressClass($resources['bad-blocks'], false); ?>" style="width: <?php echo $resources['bad-blocks']; ?>%"></div>
				</div>
				-->

				<div class="btn-group btn-group-justified">
					<a href="./?table=nat" class="btn btn-default">NAT</a>
					<a href="./?table=filter" class="btn btn-default">FILTER</a>
				</div>
			</div>
		</div>
	</div>

	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>CHAIN</th>
				<th>ACTION</th>
				<th>PROTO</th>
				<th>L7_PROTO</th>
				<th>SRC_ADDR</th>
				<th>DST_ADDR</th>
				<th>IN_INT</th>
				<th>OUT_INT</th>
				<th>SRC_PORT</th>
				<th>DST_PORT</th>
				<th>BTS</th>
				<th>PKTS</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($results as $result) { ?>
			<tr title="" data-original-title="" type="button" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="<?php echo $result['comment']; ?>">
				<td><?php echo $result['chain']; ?></td>
				<td><?php echo $result['action']; ?></td>
				<td><?php echo $result['protocol']; ?></td>
				<td><?php echo $result['layer7-protocol']; ?></td>
				<td><?php echo $result['src-address']; ?></td>
				<td><?php echo $result['dst-address']; ?></td>
				<td><?php echo $result['in-interface']; ?></td>
				<td><?php echo $result['out-interface']; ?></td>
				<td><?php echo $result['src-port']; ?></td>
				<td><?php echo $result['dst-port']; ?></td>
				<td><?php echo $result['bytes']; ?></td>
				<td><?php echo $result['packets']; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script>
		$('[data-toggle="popover"]').popover();
	</script>
</body>
</html>
