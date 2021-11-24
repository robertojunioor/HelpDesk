<?php

	/*****************************
	*
	*	length	
	*	minlength
	*	maxlength
	*	rangelength
	*
	*	equalto
	*	min
	*	max
	*	range
	*
	*	nmber
	*	digits
	*
	*	compare
	*	required
	*	alpha
	*	alphanum
	*
	*	email
	*	url
	*	regexp
	*
	*
	*/


	class validation
	{
		var $errors = array();
	
		function length($input, $length, $id="some input")
		{
			if(strlen($input) == $length)
				return true;
			else
				$this->errors[] = "Inappropriate length for $id.";
			
			return false;
		}
		
		function minlength($input, $length, $id="some input")
		{
			if(strlen($input) >= $length)
				return true;
			else
				$this->errors[] = "Inappropriate length for $id.";
			
			return false;
		}
		
		function maxlength($input, $length, $id="some input")
		{
			if(strlen($input) <= $length)
				return true;
			else
				$this->errors[] = "Inappropriate length for $id.";
			
			return false;
		}
		
		function rangelength($input, $length, $id="some input")
		{
			$lengths = explode(",",$length);
			if(strlen($input) >= $lengths[0] && strlen($input) <= $lengths[1])
				return true;
			else
				$this->errors[] = "Inappropriate length for $id.";
			
			return false;
		}
		
		//Self
		
		function equalto($input, $values, $id="some input")
		{
			$vals = explode(",", $values);
			foreach($vals as $val)
			{
				if($input == $val)
					return true;
			}

			$this->errors[] = "Inappropriate value for $id.";
			return false;
		}
		
		function min($input, $value, $id="some input")
		{
			if($input >= $value)
				return true;
			else
				$this->errors[] = "Inappropriate value for $id.";
				
			return false;
		}
		
		function max($input, $value, $id="some input")
		{
			if($input <= $value)
				return true;
			else
				$this->errors[] = "Inappropriate value for $id.";
				
			return false;
		}
		
		function range($input, $length, $id="some input")
		{
			$lengths = explode(",",$length);
			if($input >= $lengths[0] && $input <= $lengths[1])
				return true;
			else
				$this->errors[] = "Inappropriate value for $id.";
			
			return false;
		}
		
		//number digit
		function number($input, $id="some input")
		{
			if(is_numeric($input))
				return true;
			else
				$this->errors[] = "Required numeric value for $id.";
			
			return false;
		}
		
		function digits($input, $id="some input")
		{
			if(is_numeric($input) && strpos($input, ".") == -1)
				return true;
			else
				$this->errors[] = "Required only digits value for $id.";
			
			return false;
		}
		
		//equal to compare
		function compare($input1, $input2, $id="Some inputs")
		{
			if($input1 == $input2)
				return true;
			else
				$this->errors[] = ucfirst($id)." do not match as required.";
			
			return false;
		}
		
		function required($input, $id="Some input")
		{
			if(isset($input))
			{
				if($input != "")
					return true;
			}
			else
				$this->errors[] = ucfirst($id)." was missing.";
			
			return false;
		}
		
		function alpha($input, $id = "Some input")
		{
			if(ctype_alpha($input))
				return true;
			else
				$this->errors[]= ucfirst($id)." needs to be only alphabests.";
			
			return false;
		}
		
		function alphanum($input, $id = "Some input")
		{
			if(ctype_alnum($input))
				return true;
			else
				$this->errors[]= ucfirst($id)." needs to be alphanumeric values.";
			
			return false;			
		}
		
		
		function email($input, $id="email")
		{
			if(preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$^", $input)) 
				return true;
			else
				$this->errors[]= "Inappropriate $id value.";
			
			return false;	
		}
		
		function errors($delimiter = "<br>")
		{
			return implode($delimiter, $this->errors);
		}
		
		/*
		function url()
		{
		}
		
		function regexp($input, $pattern)
		{
			if(!preg_match($pattern, $input)) 
			{
				$this->errors[]= (!($e==""))?$e:"Invalid Input";
			}
		}*/
	}
?>