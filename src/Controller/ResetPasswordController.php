<?php

namespace App\Controller;

use App\Classe\MailService;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
    * @Route("mot-de-passe-oublie", name="reset_password")
    */
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

            if ($user) {
                // enregistrer en base la demande de changement de mot de passe
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTime());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // Envoyer un mail à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe

                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]);

                $content = 'Bonjour ' . $user->getFirstName() . ',<br/><br/>Vous avez demandé à rénitialiser votre mot de passe.<br/><br/>';
                $content .= 'Merci de bien vouloir cliquer sur le lien suivant afin de <a href="' . $url . '">mettre à jour votre mot de passe</a>.';
                
                $mail = new MailService();
                $mail->send($user->getEmail(), $user->getFullName(), 'Réinitialiser votre mot de passe - La boutique', $content);

                $this->addFlash('notice', 'Vous allez recevoir un mail vous permettant le changement de votre mot de passe.');
            } else {
                $this->addFlash('notice', 'Cette adresse email est inconnue.');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    /**
    * @Route("modifer-mot-de-passe/{token}", name="update_password")
    */
    public function update(Request $request, $token, UserPasswordEncoderInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }

        // vérifier si le createdAt = now - 3 hour
        $now = new DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'Votre demande de mot de passe a expiré. Veuillez la renouveller.');
            return $this->redirectToRoute('reset_password');
        }

        // Rendre une vue avec un formulaire de validation d'un nouveau mot de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new_pwd = $form->get('new_password')->getData();

            //Encoder le mot de passe et envoi à la base de données
            $password = $encoder->encodePassword($reset_password->getUser(), $new_pwd);
            $reset_password->getUser()->setPassword($password);

            $this->entityManager->flush();

            // Redirection vers la page de connexion
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour.');

            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);


    }
}
