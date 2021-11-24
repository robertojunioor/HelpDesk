function carregarMenu (fTela, fTelaPai) {
    var item_parametrizacao;
    var add_item;

    $.each(API('telas_search', [localStorage.getItem("idusuario_logado")]), function (i, dados) {
        if (dados.visualizar == 't') {
            if (dados.treeview == 't') {
                add_item = false;

                if (fTelaPai == dados.descricao)
                    item_parametrizacao = "<li class='nav-item has-treeview menu-open'>";
                else
                item_parametrizacao = "<li class='nav-item has-treeview'>";   
                
               
                if (fTelaPai == dados.descricao)
                    item_parametrizacao+= "  <a href='#' class='nav-link active'>";
                else
                    item_parametrizacao+= "  <a href='#' class='nav-link'>";   

                item_parametrizacao+= "         <i class='nav-icon "+dados.icone+"'></i>"+
                                      "         <p>"+dados.descricao_web+"<i class='right fas fa-angle-left'></i></p>"+
                                      "     </a>";

                $.each(API('subtelas_search', [localStorage.getItem("idusuario_logado"), dados.idtela]), function (i, subdados) {
                    if (subdados.visualizar == 't') {

                        item_parametrizacao += "<ul class='nav nav-treeview'>"+
                                                "  <li class='nav-item'>"+
                                                "    <a href='"+subdados.caminho+"' class='nav-link' id='"+subdados.caminho+"'>"+
                                                "      <i class='nav-icon "+subdados.icone+"'></i>"+
                                                "      <p>"+subdados.descricao_web+"</p>"+
                                                "    </a>"+
                                                "  </li>"+
                                                "</ul>";
                        add_item = true;
                    }
                });

                item_parametrizacao += "</li>";


                if (add_item == true)
                    $('#menuPrincipal').append(item_parametrizacao);
            } else { 
                $('#menuPrincipal').append("<li class='nav-item has-treeview'>"+
                                           "  <a href='"+dados.caminho+"' class='nav-link' id='"+dados.caminho+"'>"+
                                           "    <i class='nav-icon "+dados.icone+"'></i>"+
                                           "    <p>"+dados.descricao_web+"</p>"+
                                           "  </a>"+
                                           "</li>");
            }
        }
    });

    document.getElementById(fTela).classList.add("active");
}
