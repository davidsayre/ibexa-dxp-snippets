<?php declare(strict_types=1);

namespace App\Block\Attribute;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Form\Type\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use App\Block\Attribute\FormTypeMapper\TagSelectorValueTransformer;

class TagSelectorAttributeType extends AbstractType
{

    private $tagsService;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
        $this->languageCode = "eng-US"; // TODO make this dynamic
        $this->tagSelectorValueTransformer = new TagSelectorValueTransformer($this->tagsService,$this->languageCode);
    }
    /*
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('allowRootTag')
            ->setAllowedTypes('allowRootTag', 'bool')
            ->setRequired('disableSubtree')
            ->setAllowedTypes('disableSubtree', 'array')
            ->setDefaults(
                [
                    'error_bubbling' => false,
                    'allowRootTag' => true,
                    'disableSubtree' => [],
                    'constraints' => static function (Options $options): array {
                        return [
                            new Constraints\Type(['type' => 'int']),
                            new Constraints\NotBlank(),
                            new TagConstraint(['allowRootTag' => $options['allowRootTag']]),
                        ];
                    },
                ]
            );
    }
    */

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAttribute('parent_id','test');
        $builder->addModelTransformer(
            new TagSelectorValueTransformer($this->tagsService, $this->languageCode)
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $tagSelectValueTransformer = new TagSelectorValueTransformer($this->tagsService, $this->languageCode);

        $data = $form->getData();
        $tagIdCsv = $tagSelectValueTransformer->hashToCsv($data);
        $extraFieldData = $tagSelectValueTransformer->tagIdsToFieldHashes(explode(",",$tagIdCsv));

        $view->vars += [
            'mainLocale' => $this->languageCode,
            //'ids' => '',
            'parent_ids' => $extraFieldData['parent_ids'],
            'keywords' => $extraFieldData['keywords'],
            'locales' => $extraFieldData['locales']

        ];
    }

    public function getBlockPrefix()
    {
        return 'tag_selector';
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }


}