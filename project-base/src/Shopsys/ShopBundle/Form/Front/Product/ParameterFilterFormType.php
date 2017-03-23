<?php

namespace Shopsys\ShopBundle\Form\Front\Product;

use Shopsys\ShopBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\ShopBundle\Model\Product\Filter\ProductFilterConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterFilterFormType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var \Shopsys\ShopBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    private $parameterChoicesIndexedByParameterId;

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $options['product_filter_config'];
        /* @var $config \Shopsys\ShopBundle\Model\Product\Filter\ProductFilterConfig */

        $this->parameterChoicesIndexedByParameterId = [];
        foreach ($config->getParameterChoices() as $parameterChoice) {
            $parameter = $parameterChoice->getParameter();
            $parameterValues = $parameterChoice->getValues();

            $this->parameterChoicesIndexedByParameterId[$parameter->getId()] = $parameterChoice;

            $builder->add($parameter->getId(), ChoiceType::class, [
                'label' => $parameter->getName(),
                'choices' => $parameterValues,
                'choice_label' => 'text',
                'choice_value' => 'id',
                'choice_name' => 'id',
                'choices_as_values' => true, // Switches to Symfony 3 choice mode, remove after upgrade from 2.8
                'multiple' => true,
                'expanded' => true,
            ]);
        }

        $builder->addViewTransformer($this);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('product_filter_config')
            ->setAllowedTypes('product_filter_config', ProductFilterConfig::class)
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\Parameter\ParameterValue[][]|null $value
     * @return \Shopsys\ShopBundle\Model\Product\Filter\ParameterFilterData[]|null
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return null;
        }

        $parametersFilterData = [];
        foreach ($value as $parameterId => $parameterValues) {
            if (!array_key_exists($parameterId, $this->parameterChoicesIndexedByParameterId)) {
                continue; // invalid parameter IDs are ignored
            }

            $parametersFilterData[] = new ParameterFilterData(
                $this->parameterChoicesIndexedByParameterId[$parameterId]->getParameter(),
                $parameterValues
            );
        }

        return $parametersFilterData;
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\Filter\ParameterFilterData[]|null $value
     * @return \Shopsys\ShopBundle\Model\Product\Parameter\ParameterValue[][]|null
     */
    public function transform($value)
    {
        if ($value === null) {
            return null;
        }

        $parameterValuesIndexedByParameterId = [];
        foreach ($value as $parameterFilterData) {
            $parameterId = $parameterFilterData->parameter->getId();
            $parameterValuesIndexedByParameterId[$parameterId] = $parameterFilterData->values;
        }

        return $parameterValuesIndexedByParameterId;
    }
}