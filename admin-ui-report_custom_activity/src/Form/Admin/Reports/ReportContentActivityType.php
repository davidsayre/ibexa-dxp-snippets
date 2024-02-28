<?php

namespace App\Form\Admin\Reports;

use App\Form\ChoiceList\Loader\TagChoiceDepth2Loader;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\SectionService;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportContentActivityType extends AbstractType {

    const BUTTON_DOWNLOAD_XML = 'download_xml';
    const BUTTON_DOWNLOAD_CSV = 'download_csv';

    const LANGUAGE_CODE_FIELD = 'language_code';
    const DEFAULT_DATE_FIELD = 'modified';
    const DEFAULT_LIMIT = 25;
    const DEFAUL_DAYS_AGO = 90;

    const FORM_IDENTIFIER = 'caf_';

    private ContentTypeService $contentTypeService;
    private SectionService $sectionService;

    private ParameterBagInterface $parameterBag;
    private TagsService $tagsService;

    public function __construct(
        ContentTypeService $contentTypeService,
        SectionService $sectionService,
        ParameterBagInterface $parameterBag,
        TagsService $tagsService
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->sectionService = $sectionService;
        $this->parameterBag = $parameterBag;
        $this->tagsService = $tagsService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('date_field',
                ChoiceType::class,
                [
                    'label' => "Date filter field",
                    'required' => true,
                    'choices' => $this->getDateFieldChoices(),
                    'data' => self::DEFAULT_DATE_FIELD
                ]
            )
            ->add('date_from',
                DateType::class, [
                    'label' => "Start Date",
                    'required' => false,
                    'data' => $this->getDateFromDefault()
                ]
            )
            ->add('date_to',
                DateType::class, [
                    'label' => "End Date",
                    'required' => false,
                    'data' => $this->getDateToDefault()
                ]
            )
            ->add('section_id',
                ChoiceType::class, [
                    'label' => "Site Section",
                    'required' => false,
                    'choices'=> $this->getContentSections()
                ]
            )
            ->add('exclude_location_ids',
                TextType::class, [
                    'label' => "Exclude Parent Node IDs",
                    'required' => false
                ])
            ->add('content_type',
                ChoiceType::class, [
                    'label' => "Content Type",
                    'required' => false,
                    'choices' => $this->getContentTypeChoices()
                ]
            )
            ->add('search_text',
                TextType::class, [
                    'label' => "Search",
                    'required' => false
                ]
            )
            ->add('title',
                TextType::class, [
                    'label' => "Title",
                    'required' => false
                ]
            )
            ->add('tags',
                ChoiceType::class, [
                    'label' => "Tags Filter",
                    'required' => false,
                    'choice_loader' => $this->getTagsLoader()
                ]
            )
            ->add(self::LANGUAGE_CODE_FIELD,
                ChoiceType::class, [
                    'label' => "Language",
                    'required' => false,
                    'choices' => $this->getLanguageChoices()

                ]
            )
//            ->add('view_language',
//                CheckboxType::class, [
//                    'label' => "View Languages",
//                    'required' => false
//                ]
//            )
            ->add('sort_dir',
                ChoiceType::class,
                [
                    'label' => "Sort Dir",
                    'required' => false,
                    'data' => 'asc',
                    'choices' => [
                        'Asc' => 'asc',
                        'Desc' => 'desc'
                    ]
                ]
            )
            ->add('limit',
                ChoiceType::class,
                [
                    'label' => "Limit",
                    'required' => false,
                    'data' => self::DEFAULT_LIMIT,
                    'choices' => [
                        '25' => '25',
                        '50' => '50',
                        '100' => '100',
                    ]
                ])
            ->add('submit',
                SubmitType::class,
                [
                    'label' => "Submit"
                ]
            )
            ->add(self::BUTTON_DOWNLOAD_XML,
                SubmitType::class,
                [
                    'label' => "Download XML",
                ]
            )
            ->add(self::BUTTON_DOWNLOAD_CSV,
                SubmitType::class,
                [
                    'label' => "Download CSV",
                ]
            )
        ;
    }

    public function getBlockPrefix()
    {
        return self::FORM_IDENTIFIER;
    }

    private function getContentTypeChoices() {
        $choices = [];

        $contentTypeGroups = $this->contentTypeService->loadContentTypeGroups();
        foreach ($contentTypeGroups as $contentTypeGroup) {
            // exclude
            $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup);
            foreach ($contentTypes as $contentType) {
                $choices[$contentType->getName()] = $contentType->id;
            }
        }
        ksort($choices);
        return $choices;
    }

    private function getContentSections() {
        $choices = [];

        $contentSections = $this->sectionService->loadSections();
        foreach ($contentSections as $section) {
            $choices[$section->name] = $section->id;
        }
        return $choices;
    }

    private function getDateFieldChoices() {
        $choices = [
            'Published' => 'published',
            'Modified' => 'modified'
        ];

        $customField = $this->parameterBag->get('admin-ui.reports.content_activity.custom_date_field');
        $customLabel =  $this->parameterBag->get('admin-ui.reports.content_activity.custom_date_label');
        if(!empty($customField) && !empty($customLabel)){
            $choices[$customLabel." (select content type)"] = $customField;
        }

        return $choices;
    }

    private function getLanguageChoices() {
        // TODO: get from CMS
        return [
            'eng-US' => 'eng-US',
            'esl-ES' => 'esl-ES'
        ];
    }

    private function getTagsLoader() {
        $parentTags = [1];
        $customParentTags = $this->parameterBag->get('admin-ui.reports.content_activity.parent_tags');
        if(is_array($customParentTags)) {
            $parentTags = $customParentTags;
        }
        $choiceLoader = new TagChoiceDepth2Loader($this->tagsService,$parentTags);
        return $choiceLoader;
    }

    private function getDateFromDefault() {

        $dateInterval = 'P'.self::DEFAUL_DAYS_AGO.'D';
        $defaultFrom = new \DateTime();
        $fromDays = $this->parameterBag->get('admin-ui.reports.content_activity.from_days');
        if(is_numeric($fromDays)) {
            $dateInterval = "P".$fromDays."D";
        }
        $defaultFrom->sub(new \DateInterval($dateInterval));
        return $defaultFrom;
    }

    private function getDateToDefault() {
        return new \DateTime();
    }


}

?>