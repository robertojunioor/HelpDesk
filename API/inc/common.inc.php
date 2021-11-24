<?php 
error_reporting(E_ALL ^ E_DEPRECATED);
@session_start();

//-------------------------------------------------------------------------------------------//
//-- (C) TRETANZ CONSULTANCY SERVICES PVT. LTD. ---------------------------------------------//		
//-------------------------------------------------------------------------------------------//


//-- SETUP TIMEZONE -------------------------------------------------------------------------//		
	
	date_default_timezone_set("Asia/Calcutta") or die("Failed to setup TimeZone");

//-- STOP EXTERNAL REQUESTS -----------------------------------------------------------------//	

	//if(external_request()) or { die("Sorry. External Requests are not allowed.") }
	
//-- AUTOLOAD CLASSES -----------------------------------------------------------------------//	

/*	spl_autoload_register($className)
	{
		require_once "classes/".$className.".class.php";
	}
*/
	
	spl_autoload_register(function($class) {
    	include 'classes/' . $class . '.class.php';
	});
	
//-- FILTER REQUEST -------------------------------------------------------------------------//	

	if($_SERVER['REQUEST_METHOD']=="POST")		{ pre_process($_POST); }
	else if($_SERVER['REQUEST_METHOD']=="GET")	{ pre_process($_GET); }
	
//-- COMMON FUNCTIONS -----------------------------------------------------------------------//
	
	function external_request()
	{ 
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!isset($_SERVER['HTTP_REFERER'])) { return true; }
			
			$host = preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']);
			if ($host AND $_SERVER['HTTP_REFERER'])
			{
				$refparts = @parse_url($_SERVER['HTTP_REFERER']);
				$refhost  = $refparts['host'] . ((int)$refparts['port'] ? ':' . (int)$refparts['port'] : '');
				if (strpos($refhost, $host) === false)
				{
					return true;
				}
			}
			return true;
		}
		return true;
	}
	
	function pre_process(& $dump, $allow_html='')
	{
		$magic_q = get_magic_quotes_gpc();
		
		if(is_array(($dump)))
		{
			foreach($dump as $x=>$y)
			{
				if(is_array($y))
					pre_process($y,$allow_html);
				else
				{
					if($magic_q)
						$dump[$x] = stripslashes($y);
					$dump[$x] = trim($y);
					$dump[$x] = strip_tags($y,$allow_html);
					$dump[$x] = escape_str($y);
				}
			}    
		}
		else
		{
			if($magic_q)
				$dump[$x] = stripslashes($y);
			$dump = trim($dump);
			$dump = strip_tags($dump,$allow_html);
			$dump = escape_str($dump);
		}
	}
	
	function escape_str($inp)
	{
		if(!empty($inp) && is_string($inp))
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		else
			return $inp;
	}

//-- APP SPECIFIC FUNCTIONS -----------------------------------------------------------------//
	
	
	
	
//-----login--session condition--------------------------------------------------------------//
//
//		if(!isset($_SESSION['status']))
//			$_SESSION['status'] = '0';
//		if(!isset($_SESSION['pwd_error']))
//			$_SESSION['pwd_error'] = '0';
//			
		// $url = basename($_SERVER['REQUEST_URI']);
			  
		// if(!($url==="index.php" || $url==="reset.php"))
		// {
			// if( ! isset($_SESSION['userID']))
				// header("location:index.php");
		// }

	
?>