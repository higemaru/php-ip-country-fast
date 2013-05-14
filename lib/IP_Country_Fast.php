<?php

class IP_Country_Fast {
	function __construct($dbpath = './') {
        date_default_timezone_set('Asia/Tokyo');
		$this->bit0 = substr(pack('N', pow(2,31)),0,1);
		$this->bit1 = substr(pack('N', pow(2,30)),0,1);
		$this->mask = array();
		$this->dtoc = array();
		$this->cc = array();

		for ( $i = 0; $i < 32; $i++ )
			array_push( $this->mask, pack('N', pow(2, 31 - $i)) );
		for ( $i = 0; $i < 256; $i++ )
			array_push( $this->dtoc, substr( pack('N', $i), 3, 1) );

		// cc.gif
		if ( ! $cc_ultra = file_get_contents($dbpath . 'cc.gif') )
			exit();
		$cc_num = strlen($cc_ultra) / 3;
		for ( $i = 0; $i < $cc_num; $i++ ) {
			$ccchar = substr($cc_ultra, 3 * $i + 1, 2);
			if ( $ccchar === '--' ) { $ccchar = null; }
			$this->cc[ substr( $cc_ultra, 3 * $i, 1 ) ] = $ccchar;
		}

		// ip.gif
		if ( ! $this->ip_db = file_get_contents($dbpath . 'ip.gif') )
			exit();
	}

	public function inet_atocc($inet_a) {
		$ip_regex = '/^(\d|[01]?\d\d|2[0-4]\d|25[0-5])\.(\d|[01]?\d\d|2[0-4]\d|25[0-5])\.(\d|[01]?\d\d|2[0-4]\d|25[0-5])\.(\d|[01]?\d\d|2[0-4]\d|25[0-5])$/';
		if ( preg_match($ip_regex, $inet_a, $matches) ) {
			return $this->inet_ntocc($this->dtoc[$matches[1]].$this->dtoc[$matches[2]].$this->dtoc[$matches[3]].$this->dtoc[$matches[4]]);
		}
		else {
			return null;
		}
	}

	public function inet_ntocc($inet_n) {
		$pos = 4;
		$null = substr(pack('N',0), 0, 1);
		$nullnullnull = $null . $null . $null;

		$byte_zero = substr($this->ip_db, $pos, 1);

		for ($i=0; $i<=31; $i++) {
			if( ($inet_n & $this->mask[$i]) === $this->mask[$i] ) {
				if ( ($byte_zero & $this->bit1) === $this->bit1 ) {
					$unpacked = unpack('N', $nullnullnull . ($byte_zero ^ $this->bit1) );
					$pos = $pos + 1 + $unpacked[1];
				}
				else {
					$unpacked = unpack('N', $null . substr($this->ip_db, $pos, 3));
					$pos = $pos + 3 + $unpacked[1];
				}
			}
			else {
				if ( ($byte_zero & $this->bit1) === $this->bit1 ) {
					$pos = $pos + 1;
				}
				else {
					$pos = $pos + 3;
				}
			}
			$byte_zero = substr($this->ip_db,$pos,1);
			if( ($byte_zero & $this->bit0) === $this->bit0 ) {
				if ( ($byte_zero & $this->bit1) === $this->bit1) {
					return $this->cc[ substr($this->ip_db, $pos + 1, 1) ];
				}
				else {
					return $this->cc[$byte_zero ^ $this->bit0];
				}
			}
		}
	}
}
