<?php

namespace App\Controller\Admin;


use App\Entity\User;
use App\Repository\CountryRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserCrudController extends AbstractCrudController
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;
    private $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $rq = $this->countryRepository->findAll();
        $country = [];
        foreach ($rq as $key => $entity) {
            // Pour le formulaire avec la relation il me faut l'entité
            $country += [$entity->getCountry() => $entity];
        }

        return [
            FormField::addPanel('User information')
                ->setIcon('fa fa-user'),
            NumberField::new('id', 'Id')
                ->onlyOnIndex(),
            EmailField::new('email'),

            TextField::new('name', 'Name'),
            TextField::new('firstname', 'Firstname'),
            ChoiceField::new('country')
                ->setChoices($country)
                ->onlyOnForms(),
            AssociationField::new('country')
                ->setTemplatePath('admin/customCountry.html.twig')
                ->hideOnForm(),
            TextField::new('address', 'Address'),
            TextField::new('city', 'City'),
            TextField::new('cp', 'Postal Code'),
            DateTimeField::new('registration_date')
                ->setFormat('dd.MM.yyyy  HH:mm:ss')
                ->onlyOnDetail(),
            DateTimeField::new('updatedAt')
                ->setFormat('dd.MM.yyyy  HH:mm:ss')
                ->onlyOnDetail(),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur'    => 'ROLE_USER',
                ])
                ->allowMultipleChoices(),
            TextField::new('imgFile', 'Image')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
            ImageField::new('img')
                ->setBasePath('/img/user')
                ->onlyOnIndex(),
            CollectionField::new('orders')
                ->setTemplatePath('/admin/orderForUser.html.twig')
                ->onlyOnDetail(),
            FormField::addPanel('Change password')
                ->setIcon('fa fa-key')
                ->onlyOnForms(),
            TextField::new('plainPassword')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'New password'],
                    'second_options' => ['label' => 'Repeat password'],
                ])
                ->setRequired(false)
                ->onlyOnForms(),

        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        $this->addEncodePasswordEventListener($formBuilder);

        return $formBuilder;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        $this->addEncodePasswordEventListener($formBuilder);

        return $formBuilder;
    }

    /**
     * @required
     */
    public function setEncoder(UserPasswordEncoderInterface $passwordEncoder): void
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function addEncodePasswordEventListener(FormBuilderInterface $formBuilder)
    {
        $formBuilder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData();
            if ($user->getPlainPassword()) {
                $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            }
        });
    }
}
