<?php

namespace Btn\MailerBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MailerTestType extends AbstractType
{
    /**
     *
     */
    protected $key;

    /**
     *
     */
    protected $template;

    /**
     *
     */
    public function __construct($key, $template)
    {
        $this->key      = $key;
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('toEmail', 'email', array(
            'required' => true,
            'label'    => 'btn_mailer.form.toEmail',
        ));

        foreach ($this->template['contextFields'] as $key => $field) {
            $builder->add($key, $field['type'], !empty($field['options']) ? $field['options'] : null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mailer_test';
    }
}
