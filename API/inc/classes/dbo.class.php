<?php
	class dbo
	{
		//Localhost 
		/*
		private $host	="localhost";
		private $port   ="5432";
		private $db 	="eightsys_prod";
		private $user	="postgres";
		private $pass	="8S@DBS3rv3r#"; 
		*/

		//Api Amazon
		private $host	="localhost";
		private $port   ="5432";
		private $db 	="helpdesk";
		private $user	="postgres";
		private $pass	="admin"; //"Insp@1207?XT;";

		
		function dml($q)
		{
			
			$con_string = "host=".$this->host." port=".$this->port." dbname=".$this->db." user=".$this->user." password=".$this->pass;
			$link = pg_connect($con_string);

			pg_query($link, $q) or die(pg_last_error());
		}
		
		function get($q)
		{
			$con_string = "host=".$this->host." port=".$this->port." dbname=".$this->db." user=".$this->user." password=".$this->pass;
			$link = pg_connect($con_string);

			$res = pg_query($link, $q) or die(pg_last_error());
			pg_close($link);
			
			$data = array();
			$c = 0;
			while($row = pg_fetch_assoc($res))
			{	
				foreach($row as $col=>$val)
					$data[$c][$col] = $val;
				$c++;
			}
			
			return $data;
		}
		function get_scalar($q)
		{
			$con_string = "host=".$this->host." port=".$this->port." dbname=".$this->db." user=".$this->user." password=".$this->pass;
			$link = pg_connect($con_string);

			$res = pg_query($link, $q) or die(pg_last_error());
			$row = pg_fetch_array($res);
			pg_close($link);
			return $row[0];
		}
		
		function check($q)
		{
			$con_string = "host=".$this->host." port=".$this->port." dbname=".$this->db." user=".$this->user." password=".$this->pass;
			$link = pg_connect($con_string);

			$res = pg_query($link, $q) or die(pg_last_error());
			return (pg_num_rows($res)==0)?false:true;
		}
	}
?>