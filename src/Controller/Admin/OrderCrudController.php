<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public const OPTION_RENDER_AS_HTML = 'renderAsHtml';

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('index', 'detail')
            ->remove('index', 'edit')
            ->remove('index', 'delete');
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
            BooleanField::new('isPaid', 'Payée ?')->setFormTypeOption('disabled','disabled'),
            TextField::new('getProducts', 'Produits')->onlyOnDetail()->setCustomOption(self::OPTION_RENDER_AS_HTML, true),

        ];
    }
}
