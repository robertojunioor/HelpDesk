<?php include_once("inc/common.inc.php");
	
	class address extends dbo
	{
		var $table_name 	= "sys_user_address";
		var $table_pk 		= "iduser_address";
	
		var $table_columns = array(
		
			"iduser_address" 				=> array("id"	=> "iduser_address", 
												"rules" => array("required" =>true, "number"=>true)),
													
			"iduser"		 				=> array("id"	=> "iduser", 
												"rules" => array("required" =>true, "number"=>true)),

			"address_name" 					=> array("id"	=> "address_name", 
												"rules" => array("required" =>true)),

			"cep" 							=> array("id"	=> "cep", 
												"rules" => array("required" =>true)),
												
			"idcidade" 						=> array("id"	=> "idcidade", 
												"rules" => array("required" =>true, "number"=>true)),
			
			"idestado" 						=> array("id"	=> "idestado", 
												"rules" => array("required" =>true, "number"=>true)),

			"logradouro" 					=> array("id"	=> "logradouro", 
												"rules" => array("required" =>true)),

			"numero"						=> array("id"	=> "numero", 
												"rules" => array("required" =>true)),	
												
			"complemento"					=> array("id"	=> "complemento", 
												"rules" => array("required" =>false)),
												
			"bairro"						=> array("id"	=> "bairro", 
												"rules" => array("required" =>false)),
												
			"ponto_referencia"				=> array("id"	=> "ponto_referencia", 
												"rules" => array("required" =>false)),
												
			"tipo"							=> array("id"	=> "tipo", 
												"rules" => array("required" =>false)),
			
			"address_available"				=> array("id"	=> "address_available", 
												"rules" => array("required" =>false))
			
			//"newslater" 			=> array("id"	=> "newslater", 
			//									"rules" => array("required" =>true)),

		);
	
		var $join_tables	= "batches";
		var $join_condition	= "trainee_batch_ID = batch_ID";
		
		//-- Do not edit below this line -----------------------------------------//

		var $errors			= "";
		
		function __construct($action="", $data="", $files="")
		{
			$allowed_calls = array("INSERT","UPDATE", "DELETE");
			
			if(in_array(strtoupper($action),$allowed_calls))
				$this->{strtolower($action)}($data, $files);
		}
		
		function insert($data, $recieved_files)
		{

			//print_r($data);
			//exit();

			if(!is_array($data)) { die("Insert expects parameter to be array."); }
			
			$this->validate($data);
			$uploaded_files = $this->upload_files($recieved_files);
			//if($this->errors != "") { die($this->errors); }
			
			$cols = " ";
			$vals = " ";

			//print_r($data);
			//exit();

			foreach($data as $col => $val)
			{
				if(array_key_exists($col, $this->table_columns) && $col != $this->table_pk)
				{
					$cols .= $col.", ";
					$vals .= "'".$val."', ";
				}
			}
			foreach($uploaded_files as $col=>$val)
			{
				$cols .= $col.", ";
				$vals .= "'".$val."', ";
			}
			
			$q = "insert into ".$this->table_name." (".rtrim($cols,", ").") values(".rtrim($vals,", ").")";
			$this->dml($q);
		}
		
		function select($columns="*", $condition="1=1")
		{
			$join = ($this->join_condition != "") ? "AND ".$this->join_condition : "";
			$q = "select ".$columns." from ".$this->table_name.",".$this->join_tables." where ".$condition." ".$join;
			echo $q;
			return $this->get($q);
		}
		
		function update($data, $recieved_files)
		{
			if(!is_array($data)) { die("Update expects parameter to be array."); }
			if(!array_key_exists($this->table_pk, $data)) { die("Update expects primary key column value to be provided. No primary value recieved."); }
			
			$this->validate($data);
			$uploaded_files = $this->upload_files($recieved_files, "UPDATE");

			//print_r($recieved_files);
			//print_r($uploaded_files);
			//exit();
			
			//print_r($data);
			//exit();
			//print_r($this->errors);
			//exit();

			//$erros = $this->errors;
			//print_r($erros);
			//exit();
			//if($this->errors != "") { die($this->errors); } //eu não entendi pq usar isso!? Mto menos pq da erro quando não passa todas as informações - Thomas 18/11/2015
						
			//print_r($this);
			//exit();
			
			$q = "UPDATE ".$this->table_name." SET ";
			foreach($data as $col => $val)
			{
				if(array_key_exists($col, $this->table_columns) && $col != $this->table_pk){
					if($val != ''){
						$q .= $col." = '".$val."', ";
					} else {
						$q .= $col." = null, ";
					}
				}
			}
			foreach($uploaded_files as $col=>$val)
			{
				$q .= $col." = '".$val."', ";
			}
			
			//print_r($q);
			//exit();
			
			$q = rtrim($q,", ");
			$q .= " WHERE ".$this->table_pk." = ".$data[$this->table_pk];
			
			//print_r($q);
			//exit();
			
			$this->dml($q);
		}
		
		function delete($ids)
		{	
			$q = "delete from ".$this->table_name." where ".$this->table_pk." in(".$ids.")";
			$this->dml($q);
		}
		
		function validate($data)
		{
			$v = new validation();
			
			$has_errors = false;
			$cols 		= " ";
			$vals		= " ";
			
			foreach($data as $col => $val)
			{
				//If column recieved in post/get belogns to this table
				if(array_key_exists($col, $this->table_columns) && $col != $this->table_pk)
				{
					//Skip validation for file columns - NOT NEEDED!
					//if (isset($this->table_columns[$col]["isfile"])) { continue; }
					
					//Loop through rules for this column
					foreach($this->table_columns[$col]["rules"] as $validation_type=>$validation_arg)
					{
						//If this rule has additional arguments
						if($validation_arg !== true)
						{
							//echo "(if) $col -- v->".$validation_type."(".$val.", ".$validation_arg.") <br>";
							if(!$v->$validation_type($val, $validation_arg, $this->table_columns[$col]["id"])) $has_errors = true;
						}
						//If this rule does not require any arguments
						else
						{
							//echo "(else) $col -- v->".$validation_type."(".$val.") <br>";
							if(!$v->$validation_type($val, $this->table_columns[$col]["id"])) $has_errors = true;
						}
					}
				}
			}
			
			$this->errors .= ($has_errors) ? $v->errors("<br>")."<br>" : "";
			return !$has_errors;
		}
		
		private function upload_files($recieved_files, $mode="INSERT")
		{
			$files = array();
			foreach($recieved_files as $col=>$uploaded_file_vars)
			{
				if(strtoupper($mode) == "UPDATE")
				{
					if(empty($uploaded_file_vars["name"])) continue;
				}
				
			//print_r($recieved_files);
			//print_r($this->table_columns);
			//exit();

				if(array_key_exists($col, $this->table_columns))
				{
					if (isset($this->table_columns[$col]["isfile"]))
					{
						foreach($this->table_columns[$col]["rules"] as $rule=>$value)
						{
							$files[$this->table_columns[$col]["id"]][$rule] = $value;
						}
							$files[$this->table_columns[$col]["id"]]["file_info"] = $recieved_files[$col];
					}
				}
			}
			
			if(empty($files)) return array();
			
			$f = new file_upload($files);
			if(!$f->validate_all())
			{
				$this->errors .= implode("<br>",$f->errors);
				return;
			}
			
			$f->save();
			
			$uploaded_files = array();
			
			foreach($recieved_files as $col=>$uploaded_file_vars)
			{
				if(strtoupper($mode) == "UPDATE")
				{
					if(empty($uploaded_file_vars["name"])) continue;
				}
			
				if(array_key_exists($col, $this->table_columns))
				{
					if (isset($this->table_columns[$col]["isfile"]))
					{
						$uploaded_files[$col] = $f->give_name($this->table_columns[$col]["id"]);
					}
				}
			}
			
			return $uploaded_files;
		}
	}

?>