<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * @Route("/ads/{page<\d+>?1}", name="ads_index")
     */
    public function index(AdRepository $repo, $page, PaginationService $pagination)
    {
    
        $pagination->setEntityClass(Ad::class)
                   ->setPage($page)
                   ->setLimit(9);

        return $this->render('ad/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet de créer une annonce
     * 
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager){
        $ad= new Ad();

        $form= $this->createForm(AnnonceType:: class, $ad);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);

            }

            $ad->setAuthor($this->getUser());

            //$manager = $this->getDoctrine()->getManager(); dépendance!
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
               'success',
               "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistré !"
            );


            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
           
        }

        return $this-> render('ad/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * Permet d'afficher le formulaire d'édition
     * 
     * @Route("/ads/{slug}/edit", name = "ads_edit")
     * @Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier")
     *
     * @return Response
     */
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager){
        $form= $this->createForm(AnnonceType:: class, $ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);

            }
            //$manager = $this->getDoctrine()->getManager(); dépendance!
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
               'success',
               "Les modifications de l'annonce <strong>{$ad->getTitle()}</strong> ont bien été enregistrées !"
            );


            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
           
        }

        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
             'ad' => $ad
        ]);


    }

    /**
     * Permet d'afficher une seule annonce
     * 
     * @Route("/ads/{slug}", name="ads_show")
     *
     * @return Response
     */
    public function show($slug, Ad $ad){ //pas obligé de mettre le slug
        //je récupère l'annonce qui correspond au slug
        //$ad = $repo->findOneBySlug($slug); et on enlève AdRepository $repo des param

        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }

    /**
     * Suppression d'une annonce
     * 
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="Vous n'avez pas le droit d'accéder à cette ressource")
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager){
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
        );

        return $this->redirectToRoute("ads_index");


   }

}
