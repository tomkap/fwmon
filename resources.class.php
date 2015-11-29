<?php
require_once('routeros_api.class.php');

class resources
{
	public $info   = [];
	public $rows   = '';
	public $t_head = '';

	private $struct = [
		'nat' => [
			'chain' => '<th>CHAIN</th>',
			'action' => '<th>ACTION</th>',
			'protocol' => '<th>PROTO</th>',
			'layer7-protocol' => '<th>L7_PROTO</th>',
			'src-address' => '<th>SRC_ADDR</th>',
			'dst-address' => '<th>DST_ADDR</th>',
			'in-interface' => '<th>IN_INT</th>',
			'out-interface' => '<th>OUT_INT</th>',
			'src-port' => '<th>SRC_PORT</th>',
			'dst-port' => '<th>DST_PORT</th>',
			'bytes' => '<th>BTS</th>',
			'packets' => '<th>PKTS</th>'
		],

		'filter' => [
			'chain' => '<th>CHAIN</th>',
			'action' => '<th>ACTION</th>',
			'protocol' => '<th>PROTO</th>',
			'layer7-protocol' => '<th>L7_PROTO</th>',
			'src-address' => '<th>SRC_ADDR</th>',
			'dst-address' => '<th>DST_ADDR</th>',
			'in-interface' => '<th>IN_INT</th>',
			'out-interface' => '<th>OUT_INT</th>',
			'src-port' => '<th>SRC_PORT</th>',
			'dst-port' => '<th>DST_PORT</th>',
			'bytes' => '<th>BTS</th>',
			'packets' => '<th>PKTS</th>'
		],

		'mangle' => [
			'chain' => '<th>CHAIN</th>',
			'action' => '<th>ACTION</th>',
			'new-mss' => '<th>NEW_MSS</th>',
			'passthrough' => '<th>PASSTHROUGH</th>',
			'tcp-flags' => '<th>TCP_FLAGS</th>',
			'protocol' => '<th>PROTO</th>',
			'in-interface' => '<th>IN_INT</th>',
			'tcp-mss' => '<th>TCP_MSS</th>',
			'log' => '<th>LOG</th>',
			'log-prefix' => '<th>LOG_PREFIX</th>',
			'bytes' => '<th>BTS</th>',
			'packets' => '<th>PKTS</th>',
			'invalid' => '<th>INVALID</th>',
			'dynamic' => '<th>DYNAMIC</th>',
			'disabled' => '<th>DISABLED</th>'
		],

		'layer7-protocol' => [
			'name' => '<th>NAME</th>',
			'regexp' => '<th>REGEXP</th>'
		],

		'connection' => [
			'protocol' => '<th>PROTO</th>',
			'src-address' => '<th>SRC_ADDR</th>',
			'dst-address' => '<th>DST_ADDR</th>',
			'orig-bytes' => '<th>ORIG_BYTES</th>',
			'orig-packets' => '<th>ORIG_PKTS</th>',
			'reply-src-address' => '<th>REPLY_S_ADDR</th>',
			'reply-dst-address' => '<th>REPLY_D_ADDR</th>',
			'repl-bytes' => '<th>REPL_BYTES</th>',
			'repl-packets' => '<th>REPL_PKTS</th>'
		]
	];


	public function resources($table) {
		$API = new routerosAPI();
		$API->debug = false;

		$config = json_decode(file_get_contents('./config.json'), true);

		if (!$API->connect($config['hostname'], $config['username'], $config['password'])) {
			die('API connection failed, aborting...');
		}

		$resources = $API->comm("/system/resource/print")['0'];

		$this->info['platform'] = '<p><b>Platform:</b>' . $resources['platform'] . '</p>';
		$this->info['model']    = '<p><b>Model:</b>' . $resources['board-name'] . '</p>';
		$this->info['version']  = '<p><b>Version:</b>' . $resources['version'] . '</p>';
		$this->info['cpu']      = '<p><b>CPU:</b>' . $resources['cpu'] . ' @ ' . $resources['cpu-frequency'] . 'MHz</p>';
		$this->info['uptime']   = '<p><b>Uptime:</b>' . $resources['uptime'] . '</p>';

		$this->info['cpu-load']       = '
				<b class="progress-header">CPU (<font size="4em">' . $resources['cpu-load'] . '%</font>)</b>
				<div class="progress progress-striped">
					<div class="progress-bar ' . $this->getProgressClass($resources['cpu-load'], false) . '" style="width: ' . $resources['cpu-load'] . '%"></div>
				</div>
		';

		$mem_perc = round(($resources['free-memory']/$resources['total-memory']) * 100);
		$this->info['free-memory']    = '
				<b class="progress-header">Free memory (<font size="4em">' . $mem_perc . '%</font>, ' . $resources['free-memory'] . 'KB)</b>
				<div class="progress progress-striped">
					<div class="progress-bar ' . $this->getProgressClass($mem_perc, true) . '" style="width: ' . $mem_perc . '%"></div>
				</div>
		';

		$hdd_perc = round(($resources['free-hdd-space']/$resources['total-hdd-space']) * 100);
		$this->info['free-hdd-space'] = '
				<b class="progress-header">Free disk (<font size="4em">' . $hdd_perc . '%</font>, ' . $resources['free-hdd-space'] . 'KB)</b>
				<div class="progress progress-striped">
					<div class="progress-bar ' . $this->getProgressClass($hdd_perc, true) . '" style="width: ' . $hdd_perc . '%"></div>
				</div>
		';


		if (!array_key_exists($table, $this->struct)) {
			$this->t_head = '<script>document.querySelector(\'table\').style.display = \'none\';</script>';
		} else {
			$this->t_head = '<tr>';
			foreach ($this->struct[$table] as $call => $head) {
				$this->t_head .= $head;
			}
			$this->t_head .= '</tr>';

			$results = $API->comm("/ip/firewall/$table/print");

			foreach($results as $result) {
				if ($table === 'nat' || $table === 'filter' || $table === 'mangle') {
					$this->t_rows .= '<tr title="" data-original-title="" type="button" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="';
					$this->t_rows .= $result['comment'] . '">';
				} else {
					$this->t_rows .= '<tr>';
				}

				foreach ($this->struct[$table] as $call => $head)
					$this->t_rows .= '<td>' . $result[$call] . '</td>';

				$this->t_rows .= '</tr>';
			}
		}

		$API->disconnect();
	}


	public function getProgressClass($percentage, $rev) {
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
}