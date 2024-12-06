<?php

namespace App\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud};
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\HttpFoundation\{HeaderUtils, Response};
use Symfony\Component\Serializer\Context\Encoder\CsvEncoderContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

trait ExportCsvActionTrait
{
    abstract protected function getExportCsvFields(): iterable;

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(
            Crud::PAGE_INDEX,
            Action::new('exportcsv', 'Export CSV', 'fa fa-file-csv')
                ->linkToCrudAction('exportCsv')
                ->setCssClass('btn btn-secondary')
                ->createAsGlobalAction()
        );
    }

    public function exportCsv(AdminContext $context, SerializerInterface $serializer)
    {
        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::INDEX, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($fields));
        $filters = $this->container->get(FilterFactory::class)
            ->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $queryBuilder = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);
        $entities = $queryBuilder->getQuery()->getResult();

        $contextBuilder = (new CsvEncoderContextBuilder())
            ->withOutputUtf8Bom(true)
            ->withContext((new ObjectNormalizerContextBuilder())
                ->withAttributes(iterator_to_array($this->getExportCsvFields())))
            ->withDelimiter(';')
        ;

        $csv = $serializer->serialize($entities, 'csv', $contextBuilder->toArray());

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $filename = 'export_' . date_create()->format('d-m-y') . '.csv';
        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename),
        );

        return $response;
    }
}
