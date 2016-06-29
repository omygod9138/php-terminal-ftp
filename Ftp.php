<?php
Class Ftp{
	private $ftp_url, $username, $password, $conn_id, $lastFile, $process_ended, $lineBreak; 
	public function __construct($param = null){
		//include('conn.php');
		$this->ftp_url = $param['ftp_url'];
		$this->username = $param['username'];
		$this->password = $param['password'];
		//$this->dir = $param['dir'];//date( 'Y-m-d', strtotime( ' -1 day' ) );
		$this->db = $param['conn'];//$wow_db;
		$this->lineBreak = ( php_sapi_name() == 'cli' ) ? PHP_EOL : "<br>";
	}
	public function start(){
		if($this->login_ftp()){
			$this->chdir(array('web/wow'));
			while(true){
				echo 'current Dir '.$this->dir();
				echo $this->lineBreak;
				$folder = $this->get_files();
				if(is_array($folder) && !empty($folder)){
					
					foreach($folder AS $path){
						if(is_dir('web/wow/'.$path)){
							echo $path.'     dir';
							echo $this->lineBreak;
						}else{
							echo $path;
							echo $this->lineBreak;
						}
					}
					
					echo "Please enter command:";
					$line = trim(readline());
					echo "you entered ".trim($line);
					echo $this->lineBreak;
					$function = explode(' ', $line);
					$param = array();
					for($i =1; $i<count($function); $i++){
						$param[] = $function[$i];
					}
					print_r($function);
					print_r($param);
					if(is_callable($this->$function[0]($param))){
						echo 'calling function ';
						echo $lineBreak;
						call_user_func_array(array($this, $function[0]), $param);
						//$this->$function[0]('wow');
					}
				}
			}
		}
		
		/*
			if($this->login_ftp()){
			return true;
			//@ob_flush();
			//@flush();
			}else{
			return false;
			}
			$dir = $this->dir;
			if ($this->chdir()){
				echo "remote dir exist...".$this->lineBreak;
				@ob_flush();
				@flush();
				$contents = $this->get_files();
				if(!empty($contents)){
					if(!is_dir($dir)){
						mkdir($dir);
					}
					$local_dir = $dir."/";
					$this->lastFile = null;
					foreach($contents AS $arg[0]){
						$this->fs = ftp_size($this->conn_id, $arg[0]); 
						$arg[1] = fopen($dir.'/'.$arg[0], 'w');
						$fs = ftp_size($this->conn_id, $arg[0]); 
						$arg[1] = $local_dir.$arg[0]; // fopen($local_dir.$arg[0], 'w');
						echo "start ".date('H:i:s').$this->lineBreak;
						$this->downloadfile($arg[1], $arg[0]);
						echo "end ".date('H:i:s').$this->lineBreak;
						echo "----------------------------------------------".$this->lineBreak;
					}
					$this->handleDownloaded();
				}
			}
			
		}
		*/
		
	}
	public function connect_ftp(){
		return ftp_connect($this->ftp_url);
	}
	public function login_ftp(){
		$this->conn_id = $this->connect_ftp();
		return ftp_login($this->conn_id, $this->username, $this->password);
	}
	public function get_files(){
		return ftp_nlist($this->conn_id, ".");
	}
	public function chdir($path = array()){
		if(!empty($path)){
			return ftp_chdir($this->conn_id, $path[0]);
		}
	}
	public function cdup(){
		return ftp_cdup($this->conn_id);
	}
	public function downloadfile($arg = array()){
		/*
			arg[0] = $remote_file
			arg[1] = $local_file
		*/
		$this->fs = ftp_size ( $this->conn_id , $arg[0] );
		echo 'downloading...'.$arg[0].$this->lineBreak;
		//echo '<div id="'.$arg[0].'"></div>'."<br>";
		if(!isset($arg[1])) $arg[] = $arg[0];
		$ret = ftp_nb_get($this->conn_id, $arg[1], $arg[0], FTP_BINARY); 
		while($ret == FTP_MOREDATA){ 
			
			clearstatcache();
			$dld = filesize($arg[1]); 
			if ( $dld > 0 ){ 
			   // calculate percentage 
				$i = ($dld/$this->fs)*100; 
				echo ceil($i)."% downloaded\r";
			
			}   
			$ret = ftp_nb_continue ($this->conn_id); 
			
		}
		if($ret == FTP_FAILED){
		    echo "There was an error downloading the file...";
		    exit(1);
		}else if($ret == FTP_FINISHED){
			echo " download ended...".$arg[0].$this->lineBreak;
			
		}
		
	}
	
	public function dir(){
		return ftp_pwd($this->conn_id);
	}
	public function readline()
	{
		return rtrim(fgets(STDIN));
	}
	/*
	public function handleDownloaded(){
		$dir = $this->dir;
		$_dir = new DirectoryIterator($dir);
		$auc_day = strtotime( ' -1 day' );
		foreach ($_dir as $fileinfo){
			if (!$fileinfo->isDot()){
				//var_dump($fileinfo->getFilename());
				$file = $dir.'/'.$fileinfo->getFilename();
				$auction_data = json_decode(file_get_contents($file), true);
				$ins_data = '';
				foreach($auction_data['auctions'] AS $auction){
					$ins_data .= '('.$auction['auc'].', '.$auction['item'].', "'.$auction['owner'].'", '.$auction['buyout'].', '.$auction['quantity'].', '.$auc_day.'),';
				}
				$sql = 'INSERT IGNORE INTO auction_bak (auc, itemId, owner, buyout, quantity, auctime) VALUES '.rtrim($ins_data, ',');
				$this->db->query($sql); // PDO Driver DSN. Throws A PDOException.
				echo "insert into db...".$this->lineBreak;
			}
		}
		
	}
	*/
}