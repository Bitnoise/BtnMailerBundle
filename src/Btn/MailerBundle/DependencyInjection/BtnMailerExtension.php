<?php

namespace Btn\MailerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Btn\BaseBundle\DependencyInjection\AbstractExtension;

class BtnMailerExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);

        $config = $this->getProcessedConfig($container, $configs);

        $container->setParameter('btn_mailer.parameters', $config);
        $container->setParameter('btn_mailer.service', !empty($config['service']) ? $config['service'] : 'btn.mailer');
    }
}
