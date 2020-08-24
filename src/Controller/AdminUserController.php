<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/users/{page<\d+>?1}", name="admin_user_index")
     */
    public function index(UserRepository $repo, $page, PaginationService $pagination)
    {
        $pagination->setEntityClass(User::class)
                    ->setLimit(5)           
                    ->setPage($page);

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Edition d'un compte
     * 
     * @Route("/admin/users/{id}/edit/", name="admin_user_edit")
     * 
     * @return Response
     */
    public function edit(User $user, Request $request, EntityManagerInterface $manager){
        $form= $this->createForm(AccountType::class, $user);
            

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success',"La compte de {$user->getFullName()} a bien été modifiée !");

            return $this->redirectToRoute("admin_user_index");
        }

        return $this->render('admin/user/edit.html.twig',[
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Suppression d'une réservation
     * 
     * @Route("/admin/users/{id}/delete", name="admin_user_delete")
     *
     * @return Response
     */
    public function delete(User $user, EntityManagerInterface $manager){
        try{
        $manager->remove($user);
        $manager->flush();

        $this->addFlash('success',"Le compte de {$user->getFullName()} a bien été supprimée !");
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlash('danger', "Le compte de {$user->getFullName()} a des reservations, vous ne pouvez pas le supprimer !");
        }
        return $this->redirectToRoute('admin_user_index');
    }

    
}
