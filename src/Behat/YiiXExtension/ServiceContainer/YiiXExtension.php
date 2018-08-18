<?php

/*
 * This file is part of the Behat\YiiXExtension
 *
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Behat\YiiXExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Yii extension for Behat class.
 *
 * @author Jeff Liu <jeff.liu.guo@gmail.com>
 */
class YiiXExtension implements ExtensionInterface
{
    const YIIX_ID = 'yiix';

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('yiix.xml');

        if (!isset($config['framework_script']))
        {
            throw new \InvalidArgumentException('Specify `framework_script` parameter for yiix_extension.');
        }

        if ($config['file_path_style'] == 'relative')
        {//relative
            $basePath = $container->getParameter('paths.base');
        }
        else
        {//absolute
            $basePath = '';//
        }
        if (file_exists($cfg = $basePath . DIRECTORY_SEPARATOR . $config['framework_script']))
        {
            $config['framework_script'] = $cfg;
        }
        $container->setParameter('yiix.framework_script', $config['framework_script']);

        if (!isset($config['config_script']))
        {
            throw new \InvalidArgumentException('Specify `config_script` parameter for yiix_extension.');
        }

        foreach ($config['config_script'] as $index => $configScript)
        {
            if (!file_exists($cfg = $basePath . DIRECTORY_SEPARATOR . $configScript))
            {
                unset($config['config_script'][$index]);
            }
        }

        $container->setParameter('yiix.config_script', $config['config_script']);

        if (!isset($config['application_class_name']))
        {
            throw new \InvalidArgumentException('Specify `application_class_name` parameter for yiix_extension.');
        }
        $container->setParameter('yiix.application_class_name', $config['application_class_name']);

        $this->loadContextInitializer($container);

    }

    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\YiiXExtension\Context\Initializer\YiiXAwareInitializer', array(
            $container->getParameter('yiix.framework_script'),
            $container->getParameter('yiix.config_script'),
            $container->getParameter('yiix.application_class_name'),
        ));
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $container->setDefinition('yiix.context_initializer', $definition);
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'yiix';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->children()->scalarNode('file_path_style')->defaultValue('absolute')->end()->scalarNode('parameters')->defaultValue('')->end()->scalarNode('framework_script')->isRequired()->end()->arrayNode('config_script')->performNoDeepMerging()->defaultValue(array())->prototype('scalar')->end()->end()->scalarNode('application_class_name')->isRequired()->end()->end();
    }
}
