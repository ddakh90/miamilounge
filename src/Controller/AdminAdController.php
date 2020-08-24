<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     */
    public function index(AdRepository $repo, $page, PaginationService $pagination)
    {
         $pagination->setEntityClass(Ad::class)
                    ->setPage($page); 

        return $this->render('admin/ad/index.html.twig', [
            'pagination' => $pagination
        ]);
    } 

    /**
     * Permet d'afficher le formulaire d'édition
     * 
     * @Route("admin/ads/{id}/edit", name = "admin_ads_edit")
     * 
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager){
        $form= $this->createForm(AnnonceType:: class, $ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
               'success',
               "Les modifications de l'annonce <strong>{$ad->getTitle()}</strong> ont bien été enregistrées !"
            );
        }
        return $this->render('/admin/ad/edit.html.twig', [
            'form' => $form->createView(),
             'ad' => $ad
        ]);

    }

    /**
     * Suppression d'une annonce
     * 
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager){
        if(count($ad->getBookings()) > 0){
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer cette annonce {$ad->getTitle()} car elle possède des réservations en cours! "
            );
        }else{
        
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
        'success',
        "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
        );
    }

        return $this->redirectToRoute('admin_ads_index');   
    }
}
