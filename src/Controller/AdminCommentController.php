<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\AdminCommentType;
use App\Service\PaginationService;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface as EMI;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * @Route("/admin/comments/{page<\d+>?1}", name="admin_comments")
     */
    public function index(CommentRepository $repo, $page, PaginationService $pagination)
    {
        $pagination->setEntityClass(Comment::class)
                    ->setLimit(5)           
                    ->setPage($page);

        return $this->render('admin/comment/index.html.twig', [
        'pagination' => $pagination
        ]);

    }

    /**
     * Modification d'un commentaire
     * 
     * @Route("/admin/comments/{id}/edit", name="admin_comment_edit")
     * 
     */
    public function edit(Comment $comment, Request $request, EMI $manager){

        $form = $this->createForm(AdminCommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',"Le commentaire n° {$comment->getId()} a bien été modifié !"
            );
        }

        return $this->render('admin/comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView()
        ]);
    }

    /**
     * Suppression d'un commentaire
     * 
     * @Route("/admin/comments/{id}/delete", name="admin_comment_delete")
     *
     */
    public function delete(Comment $comment, EMI $manager){

        $manager->remove($comment);
        $manager->flush();

        $this->addFlash(
        'success',
        "Le commentaire <strong>{$comment->getAuthor()->getFullName()}</strong> a bien été supprimé !"
        );

        return $this->redirectToRoute('admin_comments');

    }

     
    

}
