<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Service\OrderBuilderService;
use App\Trait\ExportCsvActionTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Filters};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, BooleanField, ChoiceField, DateTimeField, IdField};
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    use ExportCsvActionTrait;

    public function __construct(
        public readonly OrderBuilderService $orderBuilderService,
        public readonly AdminContextProvider $adminContextProvider,
        public readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    protected function getExportCsvFields(): iterable
    {
        return [
            'id',
            'user' => ['email', 'name', 'address'],
            'dish' => ['name', 'price'],
            'status',
            'isDelivery',
            'createdAt',
            'updatedAt',
        ];
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Заказ')
            ->setEntityLabelInPlural('Заказы')
            ->setSearchFields(['user'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add('status')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('user')->setSortProperty('name');
        yield AssociationField::new('dish')->setSortProperty('name');
        yield ChoiceField::new('status');
        yield BooleanField::new('isDelivery');
        yield DateTimeField::new('created_at')->renderAsText()->hideOnForm();
        yield DateTimeField::new('updated_at')->renderAsText()->hideOnForm();
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Order $entityInstance
     * @return void
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $dish = $entityInstance->getDish();
        try {
            $this->orderBuilderService->updateRelated($dish, $entityInstance, $entityManager, isSaved: true);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Order $entityInstance
     * @return void
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->orderBuilderService->deleteOrder($entityInstance->getId());
    }
}
