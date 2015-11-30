<?php
require_once('fwmon.class.php');

$fwmon = new fwmon($_GET['table']);

?><!DOCTYPE html><html>
<head>
	<title>FWMon :: MikroTik Firewall Monitor (https://github.com/tomkap/fwmon/)</title>

	<link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" href="css/custom.min.css">
	<link rel="stylesheet" href="css/main.css">
</head>
<body>
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">System resources</h4>
			</div>

			<div class="modal-body">
				<?php
					echo $fwmon->info['platform'];
					echo $fwmon->info['model'];
					echo $fwmon->info['version'];
					echo $fwmon->info['cpu'];
				?>
			</div>

			<div class="modal-footer">
				<?php
					echo $fwmon->info['uptime'];
					echo $fwmon->info['cpu-load'];
					echo $fwmon->info['memory-usage'];
					echo $fwmon->info['hdd-usage'];
				?>

				<div class="btn-group btn-group-justified">
					<a href="./?table=nat" class="btn btn-default">NAT</a>
					<a href="./?table=filter" class="btn btn-default">FILTER</a>
					<a href="./?table=mangle" class="btn btn-default">MANGLE</a>
					<a href="./?table=connection" class="btn btn-default">CONN</a>
					<a href="./?table=layer7-protocol" class="btn btn-default">L7-PROT</a>
				</div>
			</div>
		</div>
	</div>

	<table class="table table-striped table-hover">
		<thead><?php echo $fwmon->t_head; ?></thead>
		<tbody><?php echo $fwmon->t_rows; ?></tbody>
	</table>

	<script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script>
		$('[data-toggle="popover"]').popover();
	</script>
</body>
</html>
