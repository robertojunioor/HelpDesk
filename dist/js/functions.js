function createOrDestroyTable(table, option) {
  if (option == "c") {
    $(table).DataTable({
      pageLength: 10,
      aLengthMenu: [
        [-1, 10, 25, 50, 100],
        ["TODOS", 10, 25, 50, 100],
      ],
      columnDefs: [{ orderable: false, targets: 1 }],
    });
  } else {
    $(table).DataTable().destroy();
  }
}

function createOrDestroyTable2(table, option) {
  if (option == "c") {
    $(table).DataTable({
      pageLength: -1,
      'lengthChange': false,
      "paging": true,
      columnDefs: [{ orderable: false, targets: 1 }],
    });
    
  } else {
    $(table).DataTable().destroy();
  }
}

function API(nomeAPI, parametros) {
  var pars;
  let newobj = {};

  if (parametros.length > 0) {
    for (let x = 0; x <= parametros.length; x++) {
      newobj[`p${x}`] = parametros[x];
    }
  }

  $.ajax({
    type: "GET",
    cache: false,
    url: "http://26.144.229.107/PI-2/API/index.php/" + nomeAPI + "/",
    timeout: 3000,
    contentType: "application/json; charset=utf-8",
    data: newobj,
    dataType: "text",
    async: false,
    error: function (request, error) {
      //alert("erro " + nomeAPI);
      Swal.fire('Oops..','Tente novamente mais tarde, se o erro persistir entre em contato com o administrador do sistema.','warning');
    },
    success: function (result) {
      pars = JSON.parse(result);
    },
  });

  return pars;
}

function zerosAEsquerda(value){
  if (value != ""){
    return value.padStart(4, '0');
  }else
    return '';
}

function validarEmail(edit) {
  var re = /\S+@\S+\.\S+/;

  if (re.test(edit.value) == false){
    edit.className = "form-control campo_invalido";  
  }else{
    edit.className = "form-control";
  }
}

function modalSucesso(titulo, tempo){
  Swal.fire({
    position: 'center',
    icon: 'success',
    title: titulo,
    showConfirmButton: false,
    timer: tempo
  });
}

function loginAutomatico(){
  if (localStorage.getItem('lembrar_login') == 'true') 
    window.location.href = 'pages/menuPrincipal.html';   
}

function validarLogin(){
  var usuario_login   = document.getElementById('edtUsuario').value;
  var senha           = document.getElementById('edtSenha').value;
  var checkbox        = document.querySelector('input[type="checkbox"]');
  var lembrar         = checkbox.checked;

  if ((usuario_login != "") && (senha != "")){
      //Validando se o usuario e senha existem
      var matriz = [usuario_login, senha];

      $.each(API('validar_usuario', matriz), function(i, user){
          if (user.message != "Failed"){       
              if (lembrar == true){
                  localStorage.setItem('email_login', usuario_login);
                  localStorage.setItem('lembrar_login', lembrar);
              }
              
              localStorage.setItem('idusuario_logado', user.idusuario);
              localStorage.setItem('nome_usuario', user.nome);
              localStorage.setItem('tipo_usuario', user.cliente);

              window.location.href = 'pages/menuPrincipal.html';   
          }else{
              Swal.fire({
                  icon: 'warning',
                  title: 'Usuário ou senha inválidos!',
                  text: '',
                  footer: ''
              });
          }
      });
  }else{
      Swal.fire({
        icon: 'warning',
        title: 'Atenção',
        text: 'Preencha os campos para realizar o login',
        button: false,
        footer: ''
      });
  }
};

function logoff(){
  localStorage.clear();
  window.location.href="../index.html";
}

function validarUsuarioLogado(){
  if (localStorage.getItem('nome_usuario') == null){
    window.location.href = "../index.html";
  }
}

function carregarDepartamento(edit, id){
  //Limpando o campo do departamento
  $(edit).empty();
 
  var item = '<option></option>';

  //Enviando uma requisição para a API
  $.each(API('carregar_departamentos',[]), function(i, departamento){
    
    if (departamento.iddepartamento != ''){
      
      if (id == departamento.iddepartamento){
        item += '<option selected value='+departamento.iddepartamento+'>'+departamento.descricao+'</option>';
      }else{
        item += '<option value='+departamento.iddepartamento+'>'+departamento.descricao+'</option>';
      }
    }
      
  });

  $(edit).append(item);
}


function validarCNPJ(cnpj) {
  var b = [ 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 ]
  var c = String(cnpj).replace(/[^\d]/g, '')
  
  if(c.length !== 14)
      return false

  if(/0{14}/.test(c))
      return false

  for (var i = 0, n = 0; i < 12; n += c[i] * b[++i]);
  if(c[12] != (((n %= 11) < 2) ? 0 : 11 - n))
      return false

  for (var i = 0, n = 0; i <= 12; n += c[i] * b[i++]);
  if(c[13] != (((n %= 11) < 2) ? 0 : 11 - n))
      return false

  return true
}