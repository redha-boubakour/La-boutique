<?php

namespace App\Controller\Admin;

use App\Classe\MailService;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    public const OPTION_RENDER_AS_HTML = 'renderAsHtml';

    private $entityManager;
    private $crudUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, CrudUrlGenerator $crudUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->crudUrlGenerator = $crudUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation', 'Lancer la préparation')->setCssClass('btn btn-info')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery', 'Lancer la livraison')->setCssClass('btn btn-info')->linkToCrudAction('updateDelivery');

        return $actions
            ->add('detail', $updatePreparation)
            ->add('detail', $updateDelivery)
            ->add('index', 'detail')
            ->remove('index', 'edit')
            ->remove('index', 'delete');
    }

    public function updatePreparation(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();

        $url = $this->crudUrlGenerator->build()
        ->setController(OrderCrudController::class)
        ->setAction('index')
        ->generateUrl();

        if ($order->getState() == 1) {
            $order->setState(2);
            $this->entityManager->flush();

            $this->addFlash('notice', "<span style='color:green'><strong>La commande " . $order->getReference() . " est bien <u>en cours de préparation</u>.</strong></span>");

            $mailService = new MailService();
            $content = 'Bonjour ' . $order->getUser()->getFirstname() . '. Votre commande est en cours de préparation';
            $mailService->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Confirmation du lancement de la préparation !', $content);

            return $this->redirect($url);
        } else {
            $this->addFlash('notice', "<span style='color:red'><strong>La commande " . $order->getReference() . " n'a toujours pas été <u>payée</u>.</strong></span>");

            return $this->redirect($url);
        }
    }

    public function updateDelivery(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();

        $url = $this->crudUrlGenerator->build()
        ->setController(OrderCrudController::class)
        ->setAction('index')
        ->generateUrl();

        if ($order->getState() == 2) {
            $order->setState(3);
            $this->entityManager->flush();

            $this->addFlash('notice', "<span style='color:green'><strong>La commande " . $order->getReference() . " est bien <u>en cours de livraison</u>.</strong></span>");

            $mailService = new MailService();
            $content = 'Bonjour ' . $order->getUser()->getFirstname() . '. Votre commande est en cours de livraison';
            $mailService->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Confirmation du lancement de la livraison !', $content);

            return $this->redirect($url);
        } else {
            $this->addFlash('notice', "<span style='color:red'><strong>La commande " . $order->getReference() . " n'a toujours pas été <u>préparée</u>.</strong></span>");

            return $this->redirect($url);
        }
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Passée le'),
            TextField::new('user.getFullName', 'Utilisateur'), 
            TextField::new('user.getUsername', 'Mail')->onlyOnDetail(),
            TextField::new('getDelivery', 'Informations client')->onlyOnDetail()->setCustomOption(self::OPTION_RENDER_AS_HTML, true),
            MoneyField::new('total', 'Total produit(s)')->setCurrency('EUR'),
            TextField::new('getCarrierName', 'Transporteur')->onlyOnDetail(),
            MoneyField::new('getCarrierPrice', 'Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée' => 0,
                'Payée' => 1,
                'Préparation en cours' => 2,
                'Livraison en cours' => 3
            ]),
            TextField::new('getProducts', 'Produits')->onlyOnDetail()->setCustomOption(self::OPTION_RENDER_AS_HTML, true),

        ];
    }
}
