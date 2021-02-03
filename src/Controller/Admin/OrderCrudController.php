<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
            ->add('index', 'detail');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Passée le'),
            TextField::new('user.getFullName', 'Utilisateur'), 
            MoneyField::new('total', 'Total sans transporteur')->setCurrency('EUR'),
            BooleanField::new('isPaid', 'Payée ?')->setFormTypeOption('disabled','disabled'),
            
            TextField::new('user.getUsername', 'Mail')->onlyOnDetail(),
            TextField::new('getDelivery', 'Informations client')->onlyOnDetail()->setCustomOption(self::OPTION_RENDER_AS_HTML, true),
            TextField::new('getProducts', 'Produits')->onlyOnDetail()->setCustomOption(self::OPTION_RENDER_AS_HTML, true),
            TextField::new('getCarrierName', 'Transporteur')->onlyOnDetail()
        ];
    }
}
