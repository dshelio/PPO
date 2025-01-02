<?php 
require_once 'classes/Notification.php';
require_once 'classes/PagamentoInterface.php';
class CartaoCredito extends Notification implements PagamentoInterface
{
    public function pagar($valor){
        if($_GET):
            $_GET['parametro'] ? (float) $_GET['parametro'] : 0;
            $valor =$_GET['parametro'];
         endif;
         $msg =  "Pagamento no valor de ".number_format($valor,2 ,',','.')." realizado via Cartão de Credito...</br>";
         return $this->success($msg,'Controlador', 'index');
     }
    }