<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	*																	     *
	*	@author Prefeitura Municipal de Itaja�								 *
	*	@updated 29/03/2007													 *
	*   Pacote: i-PLB Software P�blico Livre e Brasileiro					 *
	*																		 *
	*	Copyright (C) 2006	PMI - Prefeitura Municipal de Itaja�			 *
	*						ctima@itajai.sc.gov.br					    	 *
	*																		 *
	*	Este  programa  �  software livre, voc� pode redistribu�-lo e/ou	 *
	*	modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme	 *
	*	publicada pela Free  Software  Foundation,  tanto  a vers�o 2 da	 *
	*	Licen�a   como  (a  seu  crit�rio)  qualquer  vers�o  mais  nova.	 *
	*																		 *
	*	Este programa  � distribu�do na expectativa de ser �til, mas SEM	 *
	*	QUALQUER GARANTIA. Sem mesmo a garantia impl�cita de COMERCIALI-	 *
	*	ZA��O  ou  de ADEQUA��O A QUALQUER PROP�SITO EM PARTICULAR. Con-	 *
	*	sulte  a  Licen�a  P�blica  Geral  GNU para obter mais detalhes.	 *
	*																		 *
	*	Voc�  deve  ter  recebido uma c�pia da Licen�a P�blica Geral GNU	 *
	*	junto  com  este  programa. Se n�o, escreva para a Free Software	 *
	*	Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA	 *
	*	02111-1307, USA.													 *
	*																		 *
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
require_once ("include/clsBase.inc.php");
require_once ("include/clsDetalhe.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/pmieducar/geral.inc.php" );

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Motivo Suspens&atilde;o" );
		$this->processoAp = "607";
		$this->addEstilo('localizacaoSistema');
	}
}

class indice extends clsDetalhe
{
	/**
	 * Titulo no topo da pagina
	 *
	 * @var int
	 */
	var $titulo;

	var $cod_motivo_suspensao;
	var $ref_usuario_exc;
	var $ref_usuario_cad;
	var $nm_motivo;
	var $descricao;
	var $data_cadastro;
	var $data_exclusao;
	var $ativo;

	function Gerar()
	{
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		session_write_close();

		$this->titulo = "Motivo Suspens&atilde;o - Detalhe";
		

		$this->cod_motivo_suspensao=$_GET["cod_motivo_suspensao"];

		$tmp_obj = new clsPmieducarMotivoSuspensao( $this->cod_motivo_suspensao );
		$registro = $tmp_obj->detalhe();
                
                if( class_exists( "clsPmieducarBiblioteca" ) )
		{
			$obj_ref_cod_biblioteca = new clsPmieducarBiblioteca( $registro["ref_cod_biblioteca"] );
			$det_ref_cod_biblioteca = $obj_ref_cod_biblioteca->detalhe();
			$registro["ref_cod_biblioteca"] = $det_ref_cod_biblioteca["nm_biblioteca"];
			if( class_exists( "clsPmieducarInstituicao" ) )
			{
				$registro["ref_cod_instituicao"] = $det_ref_cod_biblioteca["ref_cod_instituicao"];
				$obj_ref_cod_instituicao = new clsPmieducarInstituicao( $registro["ref_cod_instituicao"] );
				$det_ref_cod_instituicao = $obj_ref_cod_instituicao->detalhe();
				$registro["ref_cod_instituicao"] = $det_ref_cod_instituicao["nm_instituicao"];
			}
			else
			{
				$registro["ref_cod_instituicao"] = "Erro na geracao";
				echo "<!--\nErro\nClasse nao existente: clsPmieducarInstituicao\n-->";
			}
                }
                
               if( class_exists( "clsPmieducarEscola" ) )
	       {
				$registro["ref_cod_escola"] = $det_ref_cod_biblioteca["ref_cod_escola"];
				$obj_ref_cod_escola = new clsPmieducarEscola( $registro["ref_cod_escola"] );
				$det_ref_cod_escola = $obj_ref_cod_escola->detalhe();
				$idpes = $det_ref_cod_escola["ref_idpes"];
			if ($idpes)
			{
					$obj_escola = new clsPessoaJuridica( $idpes );
					$obj_escola_det = $obj_escola->detalhe();
					$registro["ref_cod_escola"] = $obj_escola_det["fantasia"];
			}
			else
			{
					$obj_escola = new clsPmieducarEscolaComplemento( $registro["ref_cod_escola"] );
					$obj_escola_det = $obj_escola->detalhe();
					$registro["ref_cod_escola"] = $obj_escola_det["nm_escola"];
			}
		}

                $obj_permissoes = new clsPermissoes();
		$nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);
                
                if ($nivel_usuario == 1)
		{
			if( $registro["ref_cod_instituicao"] )
			{
				$this->addDetalhe( array( "Institui&ccedil;&atilde;o", "{$registro["ref_cod_instituicao"]}") );
			}
		}
                
                if ($nivel_usuario == 1 || $nivel_usuario == 2)
		{
			if( $registro["ref_cod_escola"] )
			{
				$this->addDetalhe( array( "Escola", "{$registro["ref_cod_escola"]}") );
			}
		}

                   if( $registro["ref_cod_biblioteca"] )
		{
				$this->addDetalhe( array( "Biblioteca", "{$registro["ref_cod_biblioteca"]}") );
		}

		if( ! $registro )
		{
			header( "location: educar_motivo_suspensao_lst.php" );
			die();
		}

		if( $registro["nm_motivo"] )
		{
			$this->addDetalhe( array( "Motivo Suspens&atilde;o", "{$registro["nm_motivo"]}") );
		}
		if( $registro["descricao"] )
		{
			$this->addDetalhe( array( "Descri&ccedil;&atilde;o", "{$registro["descricao"]}") );
		}

		$obj_permissoes = new clsPermissoes();
		if( $obj_permissoes->permissao_cadastra( 607, $this->pessoa_logada, 11 ) )
		{
		$this->url_novo = "educar_motivo_suspensao_cad.php";
		$this->url_editar = "educar_motivo_suspensao_cad.php?cod_motivo_suspensao={$registro["cod_motivo_suspensao"]}";
		}

		$this->url_cancelar = "educar_motivo_suspensao_lst.php";
		$this->largura = "100%";

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_biblioteca_index.php"                  => "i-Educar - Biblioteca",
         ""                                  => "Detalhe do motivo de suspens&atilde;o"
    ));
    $this->enviaLocalizacao($localizacao->montar());		
	}
}

// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm( $miolo );
// gera o html
$pagina->MakeAll();
?>