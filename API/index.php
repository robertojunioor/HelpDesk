<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

// Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

# Definindo pacotes de retorno em padrão JSON...
header('Content-Type: application/json;charset=utf-8');

# Carregando o framework Slim...
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

//require_once("class.phpmailer.php");
//use PHPMailer\src\PHPMailer;
//use PHPMailer\src\SMTP;
//use PHPMailer\src\Exception;

# Iniciando o objeto de manipulação da API SlimFramework
$app = new \Slim\Slim();
$app->response()->header('Content-Type', 'application/json;charset=utf-8');

# Função de teste de funcionamento da API...
$app->get('/', function () {
	echo "Bem-vindo a API do HELPDESK";
});

function inverteData($data){
    if (count(explode("/",$data)) > 1){
        return implode("-",array_reverse(explode("/",$data)));
    } elseif (count(explode("-",$data)) > 1){
        return implode("/",array_reverse(explode("-",$data)));
    }
};

function retornarKey(){
	return "key_helpdesk";
}

// ================================================================================
// USUARIO
// ================================================================================


############################## GERAL #####################################

$app->get('/buscar_cnpj/', function() use($app) {
	$cnpj = $app->request->get('p0');

	$url = "http://receitaws.com.br/v1/cnpj/".$cnpj;

	//Inicia o cURL
	$ch = curl_init($url);

	//Pede o que retorne o resultado como string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	//Ignora certificado SSL
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	//Manda executar a requisição
	$data = curl_exec($ch);

	//Fecha a conexão para economizar recursos do servidor
	curl_close($ch);

	//Retorna o resultado da requisição
	echo $data;
});

$app->get('/carregar_empresas/', function() use($app) {
	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT idempresa, razao_social FROM sys_empresa
	                  WHERE ativo = TRUE");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"   => "Falha na validação.",
	    	                     "idempresa" => "")] );
	}
});

$app->get('/carregar_departamentos/', function() use($app) {
	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT iddepartamento, descricao FROM sys_departamento
	                  WHERE ativo = TRUE
					  ORDER BY iddepartamento");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"   => "Falha na validação.",
	    	                     "iddepartamento" => "")] );
	}
});

$app->get('/carregar_municipios/', function() use($app) {
	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT idmunicipio, municipio FROM sys_municipio
	                  WHERE ativo = TRUE
					  ORDER BY municipio");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"     => "Falha na validação.",
	    	                     "idmunicipio" => "")] );
	}
});

$app->get('/listar_uf/', function() use($app) {
	$municipio  = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT u.sigla_uf
					  FROM sys_municipio m 
					  LEFT JOIN sys_uf u ON u.iduf = m.iduf
	                  WHERE m.ativo = TRUE
					  AND m.idmunicipio = $municipio");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"   => "Falha na validação.",
	    	                     "iduf"      => "")] );
	}
});
############################## GERAL #####################################



############################## MOVUSUARIO #####################################

$app->get('/listar_usuario/', function() use($app) {

	$codigo  = $app->request->get('p0');
	$nome    = $app->request->get('p1');
	$email   = $app->request->get('p2');
    $empresa = $app->request->get('p3');

	if ($codigo == '')
		$codigo = 'NULL';

	if ($nome == '')
		$nome = 'NULL';

	if ($email == '')
		$email = 'NULL';
	
	if($empresa == ''){
		$emp = 'NULL';
		$empresa = 0;
	}
	else
		$emp = 'OK';


	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("WITH tab_consulta AS (
						SELECT u.idusuario, LPAD(CAST (u.idusuario AS VARCHAR), 4, '0') AS codigo, 
							UPPER(nome) AS nome, UPPER(u.email) AS email,
							UPPER(e.razao_social) AS razao_social, u.idempresa, u.ativo
						FROM sys_usuario u
						LEFT JOIN sys_empresa e ON e.idempresa = u.idempresa
						WHERE u.ativo = TRUE
					  ) SELECT idusuario, codigo, nome, email, razao_social, ativo
					    FROM tab_consulta
						WHERE ativo = TRUE
						AND CASE WHEN ('$codigo' <> 'NULL') THEN codigo LIKE UPPER('%$codigo%') ELSE 1=1 END
						AND CASE WHEN ('$nome' <> 'NULL') THEN nome LIKE UPPER('%$nome%') ELSE 1=1 END
						AND CASE WHEN ('$email' <> 'NULL') THEN email LIKE UPPER('%$email%') ELSE 1=1 END
						AND CASE WHEN ('$emp' <> 'NULL') THEN idempresa IN ($empresa) ELSE 1=1 END
						ORDER BY codigo");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"   => "Falha na validação.",
	    	                     "idusuario" => "")] );
	}
});


$app->get('/buscar_usuario/', function() use($app) {
	$idusuario  = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT LPAD(CAST (idusuario AS VARCHAR), 4, '0') AS codigo,
							 nome, email, senha, idempresa, iddepartamento, cliente, cpf
					  FROM sys_usuario
	                  WHERE ativo = TRUE
					  AND idusuario = $idusuario");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"   => "Falha na validação.",
	    	                     "idempresa" => "")] );
	}
});

$app->get('/salvar_usuario/', function() use($app) {
	$idusuario     = $app->request->get('p0');
	$empresa       = $app->request->get('p1');
	$cpf           = $app->request->get('p2');
	$nome          = $app->request->get('p3');
	$cliente       = $app->request->get('p4');
	$departamento  = $app->request->get('p5');
	$email         = $app->request->get('p6');
	$senha         = $app->request->get('p7');

	if ($departamento == '')
		$departamento = 'NULL';

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	$ivlen 			= openssl_cipher_iv_length($algoritimo="AES-128-CBC");
	$iv    			= openssl_random_pseudo_bytes($ivlen);
	$senha  		= openssl_encrypt($senha, $algoritimo, retornarKey(), $options=OPENSSL_RAW_DATA, $iv);
	$hmac   		= hash_hmac('sha256', $senha, retornarKey(), $as_binary=true);
	$criptografada  = base64_encode( $iv.$hmac.$senha );

	//UPDATE
	if ($idusuario > 0){
		
		$sql = "UPDATE sys_usuario SET idempresa = $empresa,
									   cpf = '$cpf', 
									   nome = '$nome',
									   cliente = '$cliente',
									   iddepartamento = $departamento,
									   email = '$email'";
		if (strlen($senha) < 30)
							$sql.= "  ,senha = '$criptografada'";
		
		$sql.= " WHERE idusuario = $idusuario";

		$query = $d->get($sql);

		echo json_encode( [array("message"   => "Sucesso")]);
	}else{
		//INSERT
		$query = $d->get(" INSERT INTO sys_usuario (idempresa, cpf, nome, cliente, iddepartamento, email, senha)
	                       VALUES($empresa, '$cpf', '$nome', '$cliente', $departamento, '$email', '$criptografada')");
		
		echo json_encode([array("message"   => "Sucesso")]);
	}
});

$app->get('/deletar_usuario/', function() use($app) {
	$idusuario = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//UPDATE
	$query = $d->get(" UPDATE sys_usuario SET ativo = FALSE WHERE idusuario = $idusuario");

	echo json_encode( [array("message"   => "Sucesso")]);
});
############################## MOVUSUARIO #####################################


############################## MOVDEPARTAMENTO #####################################
$app->get('/listar_departamento/', function() use($app) {

	$codigo     = $app->request->get('p0');
	$descricao  = $app->request->get('p1');

	if ($codigo == '')
		$codigo = 'NULL';

	if ($descricao == '')
		$descricao = 'NULL';


	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("WITH tab_consulta AS (
						SELECT iddepartamento, LPAD(CAST(iddepartamento AS VARCHAR), 4, '0') AS codigo, 
							   descricao, ativo
						FROM sys_departamento
						WHERE ativo = TRUE
					  ) SELECT iddepartamento, codigo, descricao, ativo
					    FROM tab_consulta
						WHERE ativo = TRUE
						AND CASE WHEN ('$codigo' <> 'NULL') THEN codigo LIKE ('%$codigo%') ELSE 1=1 END
						AND CASE WHEN ('$descricao' <> 'NULL') THEN descricao LIKE (UPPER('%$descricao%')) ELSE 1=1 END
						ORDER BY codigo");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"        => "Falha na validação.",
	    	                     "iddepartamento" => "")] );
	}
});


$app->get('/buscar_departamento/', function() use($app) {
	$iddepartamento  = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT LPAD(CAST (iddepartamento AS VARCHAR), 4, '0') AS codigo,
							 iddepartamento, descricao
					  FROM sys_departamento
	                  WHERE ativo = TRUE
					  AND iddepartamento = $iddepartamento");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"        => "Falha na validação.",
	    	                     "iddepartamento" => "")] );
	}
});

$app->get('/salvar_departamento/', function() use($app) {
	$iddepartamento  = $app->request->get('p0');
	$descricao       = $app->request->get('p1');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//UPDATE
	if ($iddepartamento > 0){
		$query = $d->get(" UPDATE sys_departamento SET descricao = UPPER('$descricao')
							WHERE iddepartamento = $iddepartamento");

		echo json_encode( [array("message"   => "Sucesso")]);
	}else{
		//INSERT
		$query = $d->get(" INSERT INTO sys_departamento (descricao) VALUES(UPPER('$descricao'))");
		
		echo json_encode([array("message"   => "Sucesso")]);
	}
});

$app->get('/deletar_departamento/', function() use($app) {
	$iddepartamento = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//UPDATE
	$query = $d->get(" UPDATE sys_departamento SET ativo = FALSE WHERE iddepartamento = $iddepartamento");

	echo json_encode( [array("message"   => "Sucesso")]);
});

############################## MOVDEPARTAMENTO #####################################


############################## MOVEMPRESA #####################################

$app->get('/listar_empresa/', function() use($app) {

	$codigo    = $app->request->get('p0');
	$cnpj      = $app->request->get('p1');
	$razao     = $app->request->get('p2');
	$fantasia  = $app->request->get('p3');

	if ($codigo == '')
		$codigo = 'NULL';

	if ($cnpj == '')
		$cnpj = 'NULL';
	
	if ($razao == '')
		$razao = 'NULL';

	if ($fantasia == '')
		$fantasia = 'NULL';


	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("WITH tab_consulta AS (
						SELECT idempresa, LPAD(CAST(idempresa AS VARCHAR), 4, '0') AS codigo, 
							   cnpj, razao_social, nome_fantasia, ativo
						FROM sys_empresa
						WHERE ativo = TRUE
					  ) SELECT idempresa, codigo, razao_social, nome_fantasia, ativo, cnpj
					    FROM tab_consulta
						WHERE ativo = TRUE
						AND CASE WHEN ('$codigo' <> 'NULL') THEN codigo LIKE ('%$codigo%') ELSE 1=1 END
						AND CASE WHEN ('$cnpj' <> 'NULL') THEN cnpj LIKE ('%$cnpj%') ELSE 1=1 END
						AND CASE WHEN ('$razao' <> 'NULL') THEN razao_social LIKE (UPPER('%$razao%')) ELSE 1=1 END
						AND CASE WHEN ('$fantasia' <> 'NULL') THEN nome_fantasia LIKE (UPPER('%$fantasia%')) ELSE 1=1 END
						ORDER BY codigo");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"        => "Falha na validação.",
	    	                     "idempresa" => "")] );
	}
});

$app->get('/buscar_empresa/', function() use($app) {
	$idempresa  = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT LPAD(CAST (e.idempresa AS VARCHAR), 4, '0') AS codigo,
							 e.idempresa, e.cnpj, e.razao_social, e.nome_fantasia, e.cep, e.logradouro, e.numero,
							 e.bairro, e.complemento, e.telefone, e.email, e.idmunicipio,
							 m.municipio, u.sigla_uf
					  FROM sys_empresa e
					  LEFT JOIN sys_municipio m ON m.idmunicipio = e.idmunicipio
					  LEFT JOIN sys_uf u ON u.iduf = m.iduf
	                  WHERE e.ativo = TRUE
					  AND e.idempresa = $idempresa");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message" => "Falha na validação.", "idempresa" => "")] );
	}
});

$app->get('/salvar_empresa/', function() use($app) {
	$idempresa    = $app->request->get('p0');
	$cnpj 		  = $app->request->get('p1');
	$razao   	  = $app->request->get('p2');
	$fantasia     = $app->request->get('p3');
	$cep 	      = $app->request->get('p4');
	$logradouro   = $app->request->get('p5');
	$numero  	  = $app->request->get('p6');
	$bairro  	  = $app->request->get('p7');
	$municipio    = $app->request->get('p8');
	$complemento  = $app->request->get('p9');
	$telefone     = $app->request->get('p10');
	$email        = $app->request->get('p11');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//UPDATE
	if ($idempresa > 0){
		$query = $d->get(" UPDATE sys_empresa SET cnpj = '$cnpj',
		                                          razao_social = '$razao',
												  nome_fantasia = '$fantasia',
												  cep = '$cep',
												  logradouro = '$logradouro',
												  numero = '$numero',
												  bairro = '$bairro', 
												  idmunicipio = $municipio,
												  complemento = '$complemento',
												  telefone = '$telefone',
												  email = '$email'
							WHERE idempresa = $idempresa");

		echo json_encode( [array("message"   => "Sucesso")]);
	}else{
		//INSERT
		$query = $d->get(" INSERT INTO sys_empresa (cnpj, razao_social, nome_fantasia, cep, logradouro, numero, bairro, idmunicipio,
		                                            complemento, telefone, email) 
				           VALUES('$cnpj', '$razao', '$fantasia', '$cep', '$logradouro', '$numero', '$bairro',
						          $municipio,
								  '$complemento', '$telefone', '$email')");
		echo json_encode([array("message"   => "Sucesso")]);
	}
});

$app->get('/deletar_empresa/', function() use($app) {
	$idempresa = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//UPDATE
	$query = $d->get("UPDATE sys_empresa SET ativo = FALSE WHERE idempresa = $idempresa");

	echo json_encode( [array("message"   => "Sucesso")]);
});

############################## MOVEMPRESA #####################################

############################## MenuPrincipal ##################################

$app->get('/salvar_suporte/', function() use($app) {
	$idsuporte      = $app->request->get('p0');
	$idusuario      = $app->request->get('p1');
	$titulo         = $app->request->get('p2');
	$descricao      = $app->request->get('p3');
	$iddepartamento = $app->request->get('p4');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//UPDATE
	if ($idsuporte > 0){
		$query = $d->get(" UPDATE sys_suporte SET iddepartamento = $iddepartamento,
		                                          titulo_suporte = remover_acentos(UPPER('$titulo')),
												  descricao = remover_acentos(UPPER('$descricao'))
						   WHERE idsuporte = $idsuporte");

		echo json_encode( [array("message"   => "Sucesso")]);
	}else{
		//INSERT
		$query = $d->get(" INSERT INTO sys_suporte (idusuario, iddepartamento, status_suporte, titulo_suporte, descricao)
		                   VALUES ($idusuario, $iddepartamento, 0, remover_acentos(UPPER('$titulo')), remover_acentos(UPPER('$descricao')))");
		echo json_encode([array("message"   => "Sucesso")]);
	}
});

$app->get('/deletar_chamado/', function() use($app) {
	$idsuporte = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//UPDATE
	$query = $d->get(" UPDATE sys_suporte SET ativo = FALSE WHERE idsuporte = $idsuporte");

	echo json_encode( [array("message"   => "Sucesso")]);
});

$app->get('/carregar_chamado/', function() use($app) {

	$id = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT s.idsuporte, LPAD(CAST(s.idsuporte AS VARCHAR), 8, '0') AS codigo,
							 s.status_suporte AS status, s.titulo_suporte,
							 to_char(s.dt_abertura, 'dd/mm/YYYY') AS dt_abertura,
							 to_char(s.hr_abertura, 'HH24:MI:SS') AS hr_abertura
					  FROM sys_suporte s
					  WHERE s.ativo = TRUE
					  AND s.idusuario = $id
					  ");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"        => "Falha na validação.",
	    	                     "idsuporte" => "")] );
	}
});

$app->get('/carregar_relatorio/', function() use($app) {

	$id = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("WITH tab_consulta AS (
						SELECT unnest(ARRAY[0, 1, 2, 3]) AS status
					  )SELECT COALESCE((SELECT COALESCE(COUNT(*), 0) AS qtde
										FROM sys_suporte s
										WHERE s.ativo = TRUE
										AND s.idusuario = $id
										AND s.status_suporte = t.status 
										GROUP BY status_suporte
										ORDER BY status_suporte), 0) AS qtde
					   FROM tab_consulta t");
	 
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"   => "Falha na validação.",
	    	                     "idsuporte" => "")] );
	}
});


############################## LOGN ###########################################

$app->get('/validar_usuario/', function() use($app) {
	$email = $app->request->get('p0');
	$senha = $app->request->get('p1');

	$senha_salva = '';

	$conn = pg_connect("host=localhost port=5432 dbname=helpdesk user=postgres password=admin");

	$result = pg_exec($conn, "SELECT idusuario, nome, email, senha, cliente 
	                          FROM sys_usuario
							  WHERE ativo = TRUE
							  AND email = '$email'");

	while ($row = pg_fetch_array($result)){
		$senha_salva = $row["senha"];
		$nome        = $row["nome"];
		$cliente     = $row["cliente"];
		$id          = $row["idusuario"];
	}

	if ($senha_salva != ''){
		$senha_salva        = base64_decode($senha_salva);
		$ivlen          	= openssl_cipher_iv_length($algoritimo="AES-128-CBC");
		$iv            	    = substr($senha_salva, 0, $ivlen);
		$hmac           	= substr($senha_salva, $ivlen, $sha2len=32);
		$senha_salva   	    = substr($senha_salva, $ivlen+$sha2len);
		$senha_original     = openssl_decrypt($senha_salva, $algoritimo, retornarKey(), $options=OPENSSL_RAW_DATA, $iv);
		$calcmac 			= hash_hmac('sha256', $senha_salva, retornarKey(), $as_binary=true);
		
		if (hash_equals($hmac, $calcmac)){
			if ($senha == $senha_original){
				echo json_encode( [array("message"    => "Success",
									 	 "nome"       => $nome,
										 "cliente"    => $cliente,
										 "idusuario"  => $id)]);
			}else{
				echo json_encode( [array("message"    => "Failed")]);
			}
		}
	}else{
		echo json_encode( [array("message"   => "Failed")]);	
	}
});

$app->get('/carregar_chamados_departamento/', function() use($app) {

	$id          = $app->request->get('p0');
	$idcarregado = substr($app->request->get('p1'), 0, -1);

	if ($idcarregado == '')
		$idcarregado = 0;

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT s.idsuporte, LPAD(CAST(s.idsuporte AS VARCHAR), 8, '0') AS codigo,
							 s.status_suporte AS status, remover_acentos(s.titulo_suporte) AS titulo_suporte,
							 to_char(s.dt_abertura, 'dd/mm/YYYY') AS dt_abertura,
							 SPLIT_PART(u.nome,' ', 1)||' '||SUBSTRING(SPLIT_PART(u.nome,' ', 2), 1, 1)||'.' AS nome, 
							 e.apelido
					  FROM sys_suporte s
					  LEFT JOIN sys_usuario u ON u.idusuario = s.idusuario
				  	  LEFT JOIN sys_empresa e ON e.idempresa = u.idempresa
					  WHERE s.ativo = TRUE
					  AND CASE WHEN ('$idcarregado' <> '') THEN s.idsuporte NOT IN ($idcarregado) ELSE TRUE END
					  AND s.iddepartamento = (SELECT iddepartamento FROM sys_usuario WHERE idusuario = $id)
					  AND s.status_suporte = 0
					  ORDER BY dt_abertura, hr_abertura");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"        => "Falha na validação.",
	    	                     "idsuporte" => "")] );
	}
});

$app->get('/validar_chamados_departamento/', function() use($app) {

	$id          = $app->request->get('p0');
	$idcarregado = substr($app->request->get('p1'), 0, -1);

	if ($idcarregado == '')
		$idcarregado = 0;

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query usuario
	$query = $d->get("SELECT s.idsuporte
					  FROM sys_suporte s
					  WHERE s.ativo = FALSE
					  AND s.idsuporte IN ($idcarregado)
					  AND s.status_suporte = 0
					  ORDER BY dt_abertura, hr_abertura");
	
	if (count($query) > 0) {
		echo json_encode($query);
	} else {
	    // tell the user login failed
	    echo json_encode( [array("message"        => "Falha na validação.",
	    	                     "idsuporte" => "")] );
	}
});

################################### MENU ###################################
  
$app->get('/telas_search/',function() use($app) {
	//recebe os parametros
	$idusuario = $app->request->get('p0');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query email
	$query = $d->get("SELECT t.*,
							 COALESCE((SELECT visualizar FROM sys_controle_acesso WHERE idusuario = $idusuario AND idtela = t.idtela),FALSE) AS visualizar,
							 COALESCE((SELECT editar FROM sys_controle_acesso WHERE idusuario = $idusuario AND idtela = t.idtela),FALSE) AS editar
					  FROM sys_tela t
					  WHERE t.ativo = TRUE
					  AND t.menu_principal = TRUE
					  ORDER BY t.idtela ");

	if (count($query) > 0) {
		echo json_encode($query);
	} else {
		// set response code
		//http_response_code(401);
	 
		// tell the user login failed
		echo json_encode( [array("message" => "Failed.",
								 "senha"   => "null" )] );
	}	
});


$app->get('/subtelas_search/',function() use($app) {
	//recebe os parametros
	$idusuario = $app->request->get('p0');
	$idtela = $app->request->get('p1');

	// files needed to connect to database
	include_once("inc/common.inc.php");  
	$d = new dbo();

	//query email
	$query = $d->get("SELECT t.*,
							 COALESCE((SELECT visualizar FROM sys_controle_acesso WHERE idusuario = $idusuario AND idtela = t.idtela),FALSE) AS visualizar,
							 COALESCE((SELECT editar FROM sys_controle_acesso WHERE idusuario = $idusuario AND idtela = t.idtela),FALSE) AS editar
					 FROM sys_tela t
					 WHERE t.ativo = TRUE
					 AND t.idtela_principal = $idtela
					ORDER BY t.descricao_web ");

	if (count($query) > 0) {
		echo json_encode($query);
	} else {
		// set response code
		//http_response_code(401);
	 
		// tell the user login failed
		echo json_encode( [array("message" => "Failed." )] );
	}	
});


# Executar a API (deixá-la acessível)...
$app->run();
?>