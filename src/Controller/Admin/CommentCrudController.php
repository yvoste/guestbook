<?php

namespace App\Controller\Admin;

use App\Entity\Comment;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;


class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('conference', 'Conference')
                ->setCustomOption('widget', 'native')
                ->setDefaultColumns('col-md-4 col-xxl-3'),
            TextField::new('author'),
            TextEditorField::new('text'),
            EmailField::new('email'),
            ImageField::new('img', 'Image pack, jpg ou png ratio 1:1')
                ->setCustomOption('basePath', '/images')
                ->setCustomOption('uploadDir', '/public/images')
                ->setCustomOption('uploadedFileNamePattern', '[uuid].[extension]')
                ->setRequired(false)
                ->setFormTypeOption('allow_delete', false)
                ->setFormTypeOption('upload_validate', function ($filename) {
                    $pathInfo = pathinfo($filename);
                    if (!in_array($pathInfo['extension'], ['jpg', 'jpeg', 'png'])) {
                        throw new InvalidArgumentException('Le fichier doit être de type jpg ou png uniquement.');
                    }
                    if (!file_exists($filename)) {
                        return $filename;
                    }

                    $index = 1;
                    while (file_exists($filename = sprintf('%s/%s_%d.%s', $pathInfo['dirname'], $pathInfo['filename'], $index, $pathInfo['extension']))) {
                        ++$index;
                    }

                    return $filename;
                }),
            // DateTimeField::new('date_add', 'Date de création'),
        ];
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $img = $entityInstance->getImgName();
        if ($img) {
            $base = $this->getParameter('kernel.project_dir');
            unlink($base . '/public/images/' . $img);
        }

        $conf = $entityInstance->getConference();
        if ($conf > 0) {
            $newNb = $conf->getNbComments() - 1;
            $conf->setNbComments($newNb);
            $entityManager->persist($conf);
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('conference'))
            ->add(TextFilter::new('email'));
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        $conf = $entityInstance->getConference();
        $newNb = $conf->getNbComments() + 1;
        $conf->setNbComments($newNb);
        $entityManager->persist($conf);

        //$entityInstance->setDateAdd($date);
        //dump($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }
}
