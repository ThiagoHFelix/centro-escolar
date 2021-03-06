<?php

defined('BASEPATH') OR exit('No direct script access allowed');

 class Curso_model extends CI_Model {


 public function __construct(){

   parent::__construct();

   //Conectando a base de dados
   /***************************************************/
      $this->load->library('session');
      $database = $this->session->userdata('database');

      if($database !=  NULL)
                   $this->load->database($database);
       else
                   $this->load->database();
   /***************************************************/


 }//construtor padrão

 //Insere um novo curso
  public function insert(array $dados){
    return  $this->db->insert('CURSO',$dados);
  }//insert
  
  /**
   * Inseri dados na tabela de relaçao entre Curso e Materia
   * @param array $dados Dados da relaçao
   */
  public function insertRelacao(array $dados)
  {
  
      return $this->db->insert('CURSO_MATERIA',$dados);
      
  }//insertRelacao
  

 //Retorna a quantidade de tupla existe na tabela
 public function getAllTupla(){
   return  $this->db->count_all('CURSO');
 }//getAllTupla

 //Retorna todos os resultados da tabela
 //Retorna NULL se nada for encontrado
 public function getAll($limit = NULL,$offset = NULL){

   $retorno = $this->db->get('CURSO',$limit,$offset);

   if($retorno->num_rows() > 0)
      return $retorno->result_array();
   else
      return NULL;

 }//getAll

//Retorna todos os resultados que forem encontrados com a clausula Where
//Retorna NULL se nada for encontrado
 public function getWhere(array $dados_where){

   $this->db->where($dados_where);
   $return = $this->db->get('CURSO');

   if($return->num_rows() > 0)
      return $return->result_array();
   else
      return NULL;

 }//getWhere

 /**
  *  Busca todas as Materias do curso
  * @param int $idCurso ID do Curso que sera buscado
  * @return type Array se for encontada alguma materia, NULL caso contrario
  */
 public function getMaterias(int $idCurso)
 {
     
     $this->db->where(array('FK_CURSO_ID' => $idCurso));
     $return = $this->db->get('CURSO_MATERIA');
     
     if($return->num_rows() > 0)
         return $return->result_array();
     else
         return NULL;
     
 }//getMaterias
 
 
 public function query($query){
     
      $return = $this->db->query($query);
     
     if($return->num_rows() > 0):
         return $return->result_array();
     else:
         return NULL;
     endif;
     
     
 }
 
 
//Ativa status de curso no banco de dados
//Retorna TRUE se ativar com sucesso e FALSE se falhar
 public function ativar(integer $id){

   $dado_insert = array( 'STATUS' => 'Ativado' );

   $this->db->where('ID',$id);

   return $this->db->insert('CURSO',$dados_insert);


 }//ativar

 //Desativa status de curso no banco de dados
 //Retorna TRUE se ativar com sucesso e FALSE se falhar
public function desativar(){

  $dado_insert = array( 'STATUS' => 'Desativado' );

  $this->db->where('ID',$id);

  return $this->db->insert('CURSO',$dados_insert);

}//desativar

//Atualiza dados na base de dados
//Retorna TRUE or FALSE
public function update(array $dados_where,array $dados_update){

  $this->db->where($dados_where);

  return $this->db->update('CURSO',$dados_update);

}







}//class
