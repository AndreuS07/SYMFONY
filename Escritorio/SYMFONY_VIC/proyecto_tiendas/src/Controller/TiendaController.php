<?php 

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Tienda;
use Doctrine\DBAL\Types\TextType;

use Doctrine\Persistence\ManagerRegistry;


class TiendaController extends AbstractController{

   
    private $tiendas = [
        1 => ["nombre" => "ROSSELLIMAC", "horario"=> "De 10:00 a 21:00", "telefono" => "964 216 272", "email" => "rosellimac@gmail.com", "web"=> "rossellimac.es"],
        2 => ["nombre" => "MEDIA MARKT", "horario"=> "De 10:00 a 21:30", "telefono" => "900 205 000", "email" => "madiamarkt@gmail.com", "web"=> "mediamarkt.es"],
        5 => ["nombre" => "PCBOX", "horario"=> "De 10:00 a 20:00", "telefono" => "964 26 02 30", "email" => "pcbox@gmail.com", "web"=> "pcbox.com"],
        7 => ["nombre" => "GAME", "horario"=> "De 10:00 a 22:00", "telefono" => "964 26 21 96", "email" => "game@gmail.com", "web"=> "game.es"],
        9 => ["nombre" => "DICEWARS", "horario"=> "De 11:00 a 20:30", "telefono" => "635 58 68 11", "email" => "dicewars@gmail.com", "web"=> "dicewars.es"],
        11 =>["nombre" => "FNAC", "horario"=> "De 10:00 a 22:00","telefono" => "600 66 17 19", "email" => "fnac@gmail.com", "web"=> "fnac.es"],
        13 =>["nombre" => "Gameshop", "horario"=> "De 10:00 a 20:00","telefono" => "963 14 84 84", "email" => "gameshop@gmail.com", "web"=> "gameshop.es"]


    ];     

    #[Route('/tienda/insertar/{texto}', name:'insertar_tienda')] 
    public function insertar (ManagerRegistry $doctrine)
    {
        
    $entityManager = $doctrine->getManager();
    foreach($this->tiendas as $c){
    $tienda = new Tienda();
    $tienda->setNombre($c["nombre"]);
    $tienda->setHorario($c["horario"]);
    $tienda->setTelefono($c["telefono"]);
    $tienda->setEmail($c["email"]);
    $tienda->setWeb($c["web"]);
    $entityManager ->persist($tienda);
    }
    try
    {
    $entityManager->flush();
    
    return new Response("Tiendas insertados"); 
    }catch (\Exception $e){}
    
    return new Response("Error insertando tiendas");
}


    #[Route('/tienda/{codigo}', name:'ficha_tienda')] 
public function tienda (ManagerRegistry $doctrine, $codigo): Response{
$repositorio = $doctrine->getRepository(Tienda::class);
$tienda = $repositorio->find($codigo);

    return $this->render('ficha_tienda.html.twig', [
        'tienda' => $tienda
        ]);
    }


    #[Route('/tienda/buscar/{texto}', name:'buscar_tienda')] 
    public function buscar(ManagerRegistry $doctrine, $texto): Response{
    
    $repositorio = $doctrine->getRepository(Tienda::class);
    
    $tiendas = $repositorio->findByNombre($texto);
    
            return $this->render('lista_tiendas.html.twig', [
                'tiendas' => $tiendas
                ]);
            }



    #[Route("/tienda/update/{id}/{nombre}", name:"modificar_tienda")]

    public function update(ManagerRegistry $doctrine, $id, $nombre): Response{        $entityManager = $doctrine->getManager();
    $repositorio = $doctrine->getRepository(Tienda::class);
    $tienda = $repositorio->find($id);
    if ($tienda){
    $tienda->setNombre($nombre);
    try
    {
    $entityManager->flush();
    return $this->render('ficha_tienda.html.twig', [
    'tienda' => $tienda
    ]);
    }catch (\Exception $e) {
            return new Response("Error insertando tienda");
            }
            }else
            return $this->render('ficha_tienda.html.twig', [
            'tienda' => null
            ]);
            
            }
            

            #[Route("/tienda/delete/{id}", name:"eliminar_tienda")]

            public function delete(ManagerRegistry $doctrine, $id): Response{
            
            $entityManager = $doctrine->getManager();
            $repositorio = $doctrine->getRepository(Tienda::class);
            $tienda = $repositorio->find($id);
            if ($tienda){
            try
            {
            $entityManager ->remove ($tienda);
            $entityManager ->flush();
            
            return $this->render('eliminado.html.twig');

        
        } catch (\Exception $e){
            return new Response("Error eliminado objeto");
             }
            }else{
            return $this->render('ficha_tienda.html.twig', [
            'tienda' => null
            ]);
            
            }}

}

