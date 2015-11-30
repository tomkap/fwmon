<?php
require_once('routeros_api.class.php');

class fwmon
{
	/* JSON */
	public $json    = '';

	/* HTML resources */
	public $info    = [];

	/* HTML table */
	public $t_rows  = '';
	public $t_head  = '';
	public $t_links = [
		'nat' => '<a href="./?table=nat" class="btn btn-default">NAT</a>',
		'filter' => '<a href="./?table=filter" class="btn btn-default">FILTER</a>',
		'mangle' => '<a href="./?table=mangle" class="btn btn-default">MANGLE</a>',
		'connection' => '<a href="./?table=connection" class="btn btn-default">CONN</a>',
		'layer7-protocol' => '<a href="./?table=layer7-protocol" class="btn btn-default">L7-PROT</a>'
	];

	private $API;

	/* table name => [API call => HTML table header] */
	private $struct = [
		'nat' => [
			'chain' => 'CHAIN',
			'action' => 'ACTION',
			'protocol' => 'PROTO',
			'layer7-protocol' => 'L7_PROTO',
			'src-address' => 'SRC_ADDR',
			'dst-address' => 'DST_ADDR',
			'in-interface' => 'IN_INT',
			'out-interface' => 'OUT_INT',
			'src-port' => 'SRC_PORT',
			'dst-port' => 'DST_PORT',
			'to-addresses' => 'TO_ADDR',
			'to-ports' => 'TO_PORTS',
			'bytes' => 'BTS',
			'packets' => 'PKTS'
		],

		'filter' => [
			'chain' => 'CHAIN',
			'action' => 'ACTION',
			'protocol' => 'PROTO',
			'layer7-protocol' => 'L7_PROTO',
			'src-address' => 'SRC_ADDR',
			'dst-address' => 'DST_ADDR',
			'in-interface' => 'IN_INT',
			'out-interface' => 'OUT_INT',
			'src-port' => 'SRC_PORT',
			'dst-port' => 'DST_PORT',
			'bytes' => 'BTS',
			'packets' => 'PKTS'
		],

		'mangle' => [
			'chain' => 'CHAIN',
			'action' => 'ACTION',
			'new-mss' => 'NEW_MSS',
			'passthrough' => 'PASSTHROUGH',
			'tcp-flags' => 'TCP_FLAGS',
			'protocol' => 'PROTO',
			'in-interface' => 'IN_INT',
			'out-interface' => 'OUT_INT',
			'tcp-mss' => 'TCP_MSS',
			'bytes' => 'BTS',
			'packets' => 'PKTS',
		],

		'layer7-protocol' => [
			'name' => 'NAME',
			'regexp' => 'REGEXP'
		],

		'connection' => [
			'protocol' => 'PROTO',
			'src-address' => 'SRC_ADDR',
			'dst-address' => 'DST_ADDR',
			'orig-bytes' => 'ORIG_BYTES',
			'orig-packets' => 'ORIG_PKTS',
			'repl-bytes' => 'REPL_BYTES',
			'repl-packets' => 'REPL_PKTS'
		]
	];


	public function fwmon() {
		$this->API = new routerosAPI();
		$this->API->debug = false;

		$config = json_decode(file_get_contents('./config.json'), true);

		if (!$this->API->connect($config['hostname'], $config['username'], $config['password']))
			die('API connection failed, aborting...');
	}


	public function buildJSONTable($table) {
		if (!array_key_exists($table, $this->struct)) {
			$this->json = json_encode(['Error' => 'Unknown table.']);
		} else {
			$output = [];

			$this->buildHTMLTable($table);

			$output['tHead'] = $this->t_head;
			$output['tBody'] = $this->t_rows;

			$this->json = json_encode($output);
		}
	}


	public function buildHTMLTable($table) {
		if (!array_key_exists($table, $this->struct)) {
			$this->t_head = '<script>document.querySelector(\'table\').style.display = \'none\';</script>';
		} else {
			$this->t_links[$table] = str_replace('btn-default', 'btn-default active disabled', $this->t_links[$table]);

			$this->t_head = '<tr>';
			foreach ($this->struct[$table] as $call => $head) {
				$this->t_head .= '<th>' . $head . '</th>';
			}
			$this->t_head .= '</tr>';

			$results = $this->API->comm("/ip/firewall/$table/print");

			foreach($results as $result) {
				if ($table === 'nat' || $table === 'filter' || $table === 'mangle') {
					$this->t_rows .= '<tr title="" data-original-title="" type="button" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="';
					$this->t_rows .= $result['comment'] . '">';
				} else {
					$this->t_rows .= '<tr>';
				}

				foreach ($this->struct[$table] as $call => $head) {
					if (array_key_exists($call, $result))
						$this->t_rows .= '<td>' . $result[$call] . '</td>';
					else
						$this->t_rows .= '<td>-</td>';
				}

				$this->t_rows .= '</tr>';
			}
		}
	}


	public function buildJSONResources() {
		$output = [];

		$this->buildHTMLResources();

		$output['modalBody']   = $this->info['platform'] . $this->info['model'] . $this->info['version'] . $this->info['cpu'];
		$output['modalFooter'] = $this->info['uptime'] . $this->info['cpu-load']. $this->info['memory-usage'] . $this->info['hdd-usage'];

		$this->json = json_encode($output);
	}


	public function buildHTMLResources() {
		$resources = $this->API->comm("/system/resource/print")['0'];

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

		$mem_inuse = $resources['total-memory'] - $resources['free-memory'];
		$f_mem     = $this->formatBytes($mem_inuse);
		$mem_perc  = round(($mem_inuse/$resources['total-memory']) * 100);
		$this->info['memory-usage']    = '
				<b class="progress-header">Memory usage (<font size="4em">' . $mem_perc . '%</font>, ' . $f_mem . ')</b>
				<div class="progress progress-striped">
					<div class="progress-bar ' . $this->getProgressClass($mem_perc, false) . '" style="width: ' . $mem_perc . '%"></div>
				</div>
		';

		$hdd_inuse = $resources['total-hdd-space'] - $resources['free-hdd-space'];
		$f_hdd     = $this->formatBytes($hdd_inuse);
		$hdd_perc  = round(($hdd_inuse/$resources['total-hdd-space']) * 100);
		$this->info['hdd-usage'] = '
				<b class="progress-header">HDD usage (<font size="4em">' . $hdd_perc . '%</font>, ' . $f_hdd . ')</b>
				<div class="progress progress-striped">
					<div class="progress-bar ' . $this->getProgressClass($hdd_perc, false) . '" style="width: ' . $hdd_perc . '%"></div>
				</div>
		';
	}


	private function getProgressClass($percentage, $rev) {
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


	private function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 

		$bytes /= (1 << (10 * $pow)); 

		return round($bytes, $precision) . ' ' . $units[$pow]; 
	}

	public function __destruct() {
		$this->API->disconnect();
	}
}
