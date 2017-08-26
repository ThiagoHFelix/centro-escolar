<?php

defined('BASEPATH') OR exit('No direct script access allowed');


/*
 * Classe responsável pelo controle de usuário do administrador
 */

class Manage extends CI_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->model('Administrador_model', 'administrador');
        $this->load->helper(array('url', 'funcoes'));
        $this->load->library(array('session', 'pagination'));
    }

    /**
     * Lista a table com todos os administradores e toda as opções de manipulação
     * @param type $valor_pagina Número de paginação (Opcional)
     */
    public function administrador($valor_pagina = 0) {

        isSessionStarted();

        $is_search = FALSE;
        $CountRows = NULL;
        $TableData = NULL;
        $offset = $valor_pagina;
        $perPage = 8;
        $escolha = NULL;
        $escolha2 = NULL;
        $field_table = '';
        $data_table = '';
        $total_rows = 0;
        $value_post = $this->input->post('table_search');

        //Limpar busca
        if ($this->input->post('clear_search') !== NULL):
            $this->session->set_userdata('table_search', '');
            $value_post = '';
        endif;




        //Verificação para saber qual sql se deve executar
        if ((strcmp($value_post, '') != 0) || (strcmp($this->session->userdata('table_search'), '') != 0)):

            if ((strcmp($this->input->post('table_search'), '') != 0)):
                $this->session->set_userdata('table_search', $this->input->post('table_search'));
                $this->session->set_userdata('dropdown_search', $this->input->post('dropdown_search'));
            endif;

            $data_table = $this->session->userdata('table_search');
            $field_table = $this->session->userdata('dropdown_search');

            $CountRows = $this->administrador->get_total_tupla($data_table,$field_table);
            $TableData = $this->administrador->get_all_pessoa($offset,$perPage,TRUE,$data_table,$field_table);

        else:
            $this->session->set_userdata('table_search', '');
            $value_post = '';
            $CountRows = $this->administrador->get_total_tupla();
            $TableData = $this->administrador->get_all_pessoa($offset,$perPage);
           endif;



        $config = array(
            'base_url' => base_url('/manage/administrador'),
            'per_page' => $perPage,
            'num_links' => 3,
            'uri_segment' => 3,
            'total_rows' => $CountRows,
            'full_tag_open' => '<ul class="pagination"  style="float:right" >',
            'full_tag_close' => '</ul>',
            'first_link' => TRUE,
            'last_link' => FALSE,
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            'cur_tag_open' => '<li class="active"><a>',
            'cur_tag_close' => '</a></li>',
            'first_tag_open' => '<li>',
            'first_tag_close' => '</li>',
            'prev_tag_open' => '<li>',
            'prev_tag_close' => '</li>',
            'next_tag_open' => '<li>',
            'next_tag_close' => '</li>',
            'first_link' => 'Primeiro',
            'prev_link' => 'Anterior',
            'next_link' => 'Próximo'
        );

        //Campos da tabela
        $dados['table_field'] = '
           <th  style="text-align: center">ID</th>
           <th  style="text-align: center">Nome</th>
           <th  style="text-align: center">Email</th>
           <th  style="text-align: center">Status</th>
           <th  style="text-align: center">Ações</th>
           ';

        //Opções de campos da drodown_search
        $dados['dropdown_options'] = '
            <option value="primeiroNome">Nome</option>
            ';

        //Deixar o dropdown selecionado
        $escolha = (strcmp($field_table, 'ID') == 0) ? '<option selected>ID</option>' : '<option>ID</option>';
        $escolha2 = (strcmp($field_table, 'Email') == 0) ? '<option selected>Email</option>' : '<option>Email</option>';
        $dados['dropdown_options'] = $dados['dropdown_options'] . $escolha . $escolha2;


        //Titulo da view
        $dados['title'] = 'Administradores';

        //Inicializo a classe de paginação
        $this->pagination->initialize($config);

        //Criação do HTML com os links
        $dados['pagination'] = $this->pagination->create_links();



        $dados['table'] = $TableData;

        $this->load->view('/administrador/manage/manage', $dados);
    }

    /**
     * Carrega o perfil do usuário
     * @param type $userid
     */
    public function userProfile($entidade, $userid = NULL) {

        isSessionStarted();


        //Verificação de parâmetro
        if (!is_numeric($userid)):
            show_404();
        else:

            $resultado = NULL;

            switch($entidade){

            case 'administrador':
                $this->load->model('Administrador_model','administrador');
                $resultado = $this->administrador->getPessoaById($userid);
            break;

            case 'Professor':
                $this->load->model('Professor_model','professor');
                $resultado = $this->professor->getPessoaById($userid);
            break;

            case 'Aluno':
                $this->load->model('Aluno_model','aluno');
                $resultado = $this->aluno->getPessoaById($userid);
            break;



            }//switch

            //Usuário não existe
            if ($resultado == NULL):
                show_404();
            else:

                $dados = $resultado;
                $dados['entidade'] = $entidade;

                $this->load->view('administrador/manage/user/userprofile', $dados);

            endif;
        endif;
    }

    //Verifica se o cadastro existe e o redireciona para o método de cadastro
    public function cadastro($entidade = '') {

        isSessionStarted();

        //Seleciona o método de cadastro
        switch(strtoupper($entidade)){

          case 'ADMINISTRADOR':
            $this->cadAdministrador();
          break;

          default: show_404();

        }//switch

    }//cadastro


    /**
     * Faz o cadastro do administrador no banco de dados
     */
    private function cadAdministrador() {

        $this->load->library(array('form_validation','session'));
        $this->load->model('Administrador_model','administrador');

        //Criando regras para a validação do formulário
        $this->form_validation->set_rules('primeiroNome', '"Primeiro nome"', 'trim|required|max_length[25]');
        $this->form_validation->set_rules('sobrenome', '"Sobrenome"', 'trim|required|max_length[60]');
        $this->form_validation->set_rules('sexo', '"Sexo"', 'trim|required');
        $this->form_validation->set_rules('nascimento', '"Nascimento"', 'trim|required');
        $this->form_validation->set_rules('senha', '"Senha"', 'trim|required|max_length[20]|min_length[5]');
        $this->form_validation->set_rules('conf_senha', '"Confirmação da senha"', 'trim|required|max_length[20]|min_length[5]|matches[senha]');
        $this->form_validation->set_rules('email', '"Email"', 'valid_email|max_length[40]');


        //inicio a verificação da regras
        if ($this->form_validation->run()){

          //busco dados para verificação do campo sexo
          $sexo_inserido = $this->input->post('sexo');

          //busco dados para verificação do email no banco de dados
          $email_inserido = $this->input->post('email');
          $retorno = $this->administrador->get_pessoa($email_inserido);

          //verificação do campo sexo
          if(strcmp(strtoupper($sexo_inserido),'SEXO') == 0){

              $this->session->set_flashdata('mensagem_usuario','<p>Campo "Sexo" não foi selecionado</p>');

          }//if | campo sexo
          //verificação do email no banco de dados
          else if($retorno != NULL){

             $this->session->set_flashdata('mensagem_usuario','<p>Este email já está cadastrado</p>');

          }//if | retorno


          //Email não está cadastrado no banco de dados
          else{

            $nome_imagem = NULL;
            $local_imagem = NULL;

          if($_FILES['imagem']['name'] != NULL){

            $nome_imagem = uniqid().'-'.time();

            //Configuração do upload da imagem
            $config['file_name'] = $nome_imagem;
            $config['upload_path'] = './user_img/';
            $config['allowed_types'] = 'jpg|png';
            $config['max_size'] = 1024;
            $config['max_width'] = 1024;
            $config['max_height'] = 768;

            $this->load->library('upload',$config);

            if(!$this->upload->do_upload('imagem')){

              $retorno = $this->upload->display_errors();
              $this->session->set_flashdata('mensagem_usuario',$retorno);

            }//if | do_upload fail
            else{

              $dados_img = $this->upload->data();
              $local_imagem = $dados_img['file_path'].''.$dados_img['file_name'];


            }//else | do_upload sucess

          }//if | Imagem inserida
          else{

              $local_imagem = base_url('/user_img/avatar.png');

          }//else | imagem não inserida

                $dados = array(

                    'PRIMEIRONOME' => "" . $this->input->post('primeiroNome') . "",
                    'SOBRENOME' => "". $this->input->post('sobrenome') . "",
                    'NASCIMENTO' => "" . $this->input->post('nascimento') . "",
                    'STATUS' => 'Ativado',
                    'ESTADO' => "" . $this->input->post('estado') . "",
                    'RUA' => "" . $this->input->post('rua') . "",
                    'CEP' => "" . $this->input->post('cep') . "",
                    'BAIRRO' => "" . $this->input->post('bairro') . "",
                    'CIDADE' => "" . $this->input->post('cidade') . "",
                    'NUMRESIDENCIA' => $this->input->post('residencia'),
                    'SENHA' => "" . $this->input->post('senha') . "",
                    'SEXO' => "" . $this->input->post('sexo') . "",
                    'CPF' => "" . $this->input->post('cpf') . "",
                    'RG' => "" . $this->input->post('rg') . "",
                    'TELEFONE' => "" . $this->input->post('telefone') . "",
                    'EMAIL' => "" . $this->input->post('email') . "",
                    'FOTO' => "" . $local_imagem . ""

                );


                $retorno = $this->administrador->insert_pessoa($dados);
                if(!$retorno){

                      $this->session->set_flashdata('mensagem_usuario','<p> Não foi possível cadastrar o administrador no banco de dados <br/> CONTATE O ADMINISTRADOR </p> - CADASTRO DE PESSOA');

                }//if | retorno pessoa
                else{

                  $dados_pessoa = $this->administrador->get_pessoa_only($dados['EMAIL']);



                  $dados = array ( 'FK_PESSOA_ID' => $dados_pessoa[0]['ID']);
                  $retorno = $this->administrador->insert_adm($dados);

                  if(!$retorno){

                        $this->session->set_flashdata('mensagem_usuario','<p> Não foi possível cadastrar o administrador no banco de dados <br/> CONTATE O ADMINISTRADOR </p> - CADASTRO DE ADMINISTRADOR');

                  }//if | retorno administrador
                  else{

                      $this->session->set_flashdata('mensagem_usuario','<p> Cadastro realizado com sucesso =D =D =D =D </p>');

                  }//else | cadastro sucess


                } //else | retorno pessoa


              }//else | Todos os dados validados

          }//if | Validação de dados
          else{

            $this->session->set_flashdata('mensagem_usuario',validation_errors());

          }// Dados não validados



            //Carregamento da view de cadastro
            $this->load->view('administrador/manage/cadastro_administrador');


    }//cadAdministrador


}//class
