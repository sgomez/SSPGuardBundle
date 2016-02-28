<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle\DependencyInjection;


use Sgomez\Bundle\SSPGuardBundle\SimpleSAMLphp\SSPAuthSource;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class SSPGuardExtension extends Extension
{
    private $autoloadPath;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('twig.xml');

        // Find SimpleSAMLphp _autoload.php file
        $autoloadPath = sprintf('%s/lib/_autoload.php', rtrim($config['installation_path'], '/'));
        if (false === file_exists($autoloadPath)) {
            throw new InvalidConfigurationException('The path "simple_saml.path" doesn\'t contain a valid SimpleSAMLphp installation.');
        }
        $this->autoloadPath = $autoloadPath;

        $authSources = $config['auth_sources'];
        $authSourcesKeys = [];

        foreach ($authSources as $key => $authSource) {
            $tree = new TreeBuilder();
            $node = $tree->root('ssp_guard/auth_sources/'.$key);
            $this->buildConfigurationForAuthSource($node, $key);
            $processor = new Processor();
            $config = $processor->process($tree->buildTree(), [$authSource]);

            $authSourceKey = $this->configureAuthSource(
                $container,
                $key,
                $config
            );

            $authSourcesKeys[$key] = $authSourceKey;
        }

        $container->getDefinition('ssp.guard.registry')
            ->replaceArgument(1, $authSourcesKeys)
        ;
    }

    private function configureAuthSource(ContainerBuilder $container, $authSource, $options = [])
    {
        $authSourceKey = sprintf('ssp.guard.auth_source.%s', $authSource);

        $authSourceDefinition = $container->register(
            $authSourceKey,
            SSPAuthSource::class
        );
        $authSourceDefinition->setFile($this->autoloadPath);

        $authSourceDefinition->setFactory([
            new Reference('ssp.guard.auth_source_factory'),
            'createAuthSource'
        ]);

        $authSourceDefinition->setArguments([
            $authSource,
            $options
        ]);

        return $authSourceKey;
    }

    private function buildConfigurationForAuthSource(NodeDefinition $node, $authSourceId)
    {
        $optionsNode = $node->children();
        $optionsNode
            ->scalarNode('title')->isRequired()->end()
            ->scalarNode('user_id')->isRequired()->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'ssp_guard';
    }
}