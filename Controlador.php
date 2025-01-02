<?php
session_start();

require_once "classes/Produto.php";
require_once "classes/Clientes.php";
require_once "classes/Boleto.php";
require_once "classes/PayPal.php";
require_once "classes/CartaoCredito.php";
require_once "classes/Notification.php";

class Controlador  extends Notification
{

    public function index()
    {
        $prod = new Produto();
        $ret = $prod->gerarProdutos();

        require_once "public/home/home.php";
    }

    public function inserirCarrinho()
    {
        $id = 0;
        $cliente = (new Clientes())->obterClientes();


        if ($_GET && isset($_GET['id'])):

            $id = $_GET['id'];
            $linha = -1;
            $existe = false;

            if (isset($_SESSION['carrinho'])):
                foreach ($_SESSION['carrinho'] as $linha => $valor):
                    if ($valor['id'] == $id):
                        $existe = true;
                    endif;
                endforeach;
            endif;

            if (!$existe):
                $produto = (new Produto())->obterProdutoPorId($id);
                #var_dump($produto);
                if (isset($produto) && $produto !== null):
                    $_SESSION['carrinho'][$linha + 1]['id'] = $produto->getId();
                    $_SESSION['carrinho'][$linha + 1]['descricao'] = $produto->getDescricao();
                    $_SESSION['carrinho'][$linha + 1]['preco'] = $produto->getPreco();
                    $_SESSION['carrinho'][$linha + 1]['imagem'] = $produto->getImagem();
                    $_SESSION['carrinho'][$linha + 1]['qtde'] = 1;

                    if (!isset($_SESSION['qtdeProduto'])):
                        $_SESSION['qtdeProduto'] = 0;
                    endif;
                    $_SESSION['qtdeProduto'] += 1;

                endif;
            endif;

        endif;

        require_once "public/carrinho/index.php";
    }

    public function atualizarCarrinho()
    {

        if ($_GET):

            $linha = $_GET['linha'];

            if (isset($_SESSION['carrinho'][$linha])):
                $_SESSION['qtdeProduto'] -= $_SESSION['carrinho'][$linha]['qtde'];

                unset($_SESSION['carrinho'][$linha]);

            endif;

            header('location:index.php?arquivo=Controlador&metodo=inserirCarrinho');

        endif;

        if ($_POST):

            $linha = $_POST['linha'];
            $qtde = $_POST['quantidade'];

            if ($qtde > 0):
                $_SESSION['carrinho'][$linha]['qtde'] = $qtde;

                $_SESSION['qtdeProduto'] = 0;
                foreach ($_SESSION['carrinho'] as $itens):
                    $_SESSION['qtdeProduto'] += $itens['qtde'];
                endforeach;


            endif;
        endif;
    }

    public function finalizarCarrinho()
    {
       require_once "public/shared/header.php";
        if ($_POST):

            $clienteId = $_POST['cliente'];
            $formaPag = $_POST['formapagamento'];

            $cli = (new Clientes())->obterClientes();
            $cliSelecionado = null;
            foreach ($cli as $valor):
                if ($valor->getId() == $clienteId):
                    $cliSelecionado = $valor;
                    break;
                endif;
            endforeach;


            $formaPagamento = null;
            switch ($formaPag):
                case '1':
                    $formaPagamento = new Boleto();
                    $formaPag = "Boleto";
                    break;
                case '2':
                    $formaPagamento = new PayPal();
                    $formaPag = "PayPal";
                    break;
                case '3':
                    $formaPagamento = new CartaoCredito();
                    $formaPag = "Cartão de Credito";
                    break;

            endswitch;

            echo " <div class='container flex justify-center'> ";
               echo " <div class='box-6 pd-10 bg-branco radius mg-t-10'> ";
                   echo " <div class='box-12'> ";
                   echo "<h3 class=' poppins-medium fonte24'> Detalhes da compra </h3> ";
                   echo "<div class='divider mg-t-2 mg-b-2'></div>";
                   #LISTANDO DADOS DO CLIENTE
                   echo "<div class='box-12 flex justify-between'>";
                      echo "<p class='fonte14 espaco-letra poppins-medium'><strong class'fonte16'>Cliente:</strong>{$cliSelecionado->getNome()} </p>";
                      echo "<p class='fonte14 espaco-letra poppins-medium'><strong class'fonte16'>Documento:</strong>{$cliSelecionado->getCpf()} </p>";
                   echo "</div>";

                   echo "<div class='limpar'></div> <div class='divider mg-t-2 mg-b-2'></div>";
                   
                   #LISTANDO ITENS DO CARRINHO
                   echo "<div class='box-12 mg-t-2'>";
                      echo "<h3 class='poppins-medium fonte24 mg-b-2'> Itens no carrinho </h3> ";
                      echo "<div class='box-12'>";
                      if(isset($_SESSION['carrinho'])):
                        $total = 0;
                        foreach ($_SESSION['carrinho']  as $key => $valor):
                            $subTotal = $valor['qtde'] * $valor['preco'];
                            $total += $subTotal;
                            echo "<div class='box-12 bg-p3-paper radius pd-10 mg-b-2'>";
                               echo "<div class='box-2'>";
                                  echo "<img src='lib/img/{$valor['imagem']}' class=' logo-40' />";
                               echo "</div>";
                            echo "<div class='box-10'>";

                            echo "<p class='fonte14 espaco-letra poppins-medium'><strong class'fonte16'>Descrição:</strong>{$valor['descricao']} </p>";
                            echo "<p class='fonte14 espaco-letra poppins-medium'><strong class'fonte16'>Qtde:</strong>{$valor['qtde']} </p>";
                            echo "<p class='fonte14 espaco-letra poppins-medium'><strong class'fonte16'>Sub-total:</strong> R$ ". number_format($subTotal,2,',','.') ."</p>";
                 
                            echo "</div>";
                            echo "</div>";
                        endforeach;
                      endif;

                      echo "<div class='box-12'> <h4 class='txt-d fonte16 poppins-black fnc-cinza'>Total: <span class=' poppins-medium'>R$ ". number_format($total,2,',','.') ." </span></h4> </div>";
                      echo "<div class='box-12 mg-t-2 bg-p1-verde2 radius pd-10'> <p class='txt-c fnc-verde poppins-medium'> Pagamento realizado via {$formaPag} </p> </div>";

                   echo "<div>";  
                   #FIM DA LISTAGEM DE PRODUTOS
                   echo "<div class='box-12 mg-t-2'> <a href='index.php?arquivo=$formaPag&metodo=Pagar&parametro=$total' class='btn-100 bg-p1-amarelo fnc-branco'>Finalizar Carrinho </a>   </div>";

                   echo " </div> ";
               echo " </div> ";
            echo " </div> ";

            unset($_SESSION['carrinho']);
            unset($_SESSION['qtdeProduto']);
            

        endif;
    }
}
