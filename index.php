<?php 

if($_GET):

    $controller = $_GET['arquivo'];
    $metodo = $_GET['metodo'];

    if(isset($_GET['parametro'])):
        $parametro = $_GET['parametro'];
    endif;

    require_once "classes/".$controller.".php";
    

    $obj = new $controller();
    if(isset($_GET['parametro']) && $_GET['parametro'] !== ''):
        $obj->$metodo($parametro); 
    else:
        $obj->$metodo();
    endif;
    

else:
    require_once "classes/Controlador.php";
    $obj = new Controlador();
    $obj->index();

endif;

?>