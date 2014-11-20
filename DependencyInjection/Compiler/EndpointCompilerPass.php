<?php

namespace Bluetea\JiraRestApiBundle\DependencyInjection\Compiler;


use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EndpointCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Get the configuration
        $config = $container->getExtensionConfig('bluetea_jira_rest_api')[0];

        // Check if api's are defined
        if (!isset($config['api']) || (!isset($config['api']['jira']) && !isset($config['api']['crowd']))) {
            throw new InvalidConfigurationException('Configure at least one API');
        }

        // Check if authentications are defined
        if (!isset($config['authentication']) || (!isset($config['authentication']['jira']) && !isset($config['authentication']['crowd']))) {
            throw new InvalidConfigurationException('Configure at least one authentication');
        }

        if (isset($config['authentication']['jira'])) {
            $this->createAuthentication($container, $config['authentication']['jira'], 'jira');
        }
        if (isset($config['authentication']['crowd'])) {
            $this->createAuthentication($container, $config['authentication']['crowd'], 'crowd');
        }

        // Initialize the api client
        if (!isset($config['api_client'])) {
            $config['api_client'] = 'guzzle';
        }
        if (isset($config['api']['jira'])) {
            $this->createApiClient($container, $config['api_client'], $config['api']['jira'], 'jira');
        }
        if (isset($config['api']['crowd'])) {
            $this->createApiClient($container, $config['api_client'], $config['api']['crowd'], 'crowd');
        }

        $this->initializeEndpoints($container, $config['api']);
    }

    /**
     * Create authentication services
     *
     * @param ContainerBuilder $container
     * @param $authentication
     * @param $type
     * @throws \LogicException
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function createAuthentication(ContainerBuilder $container, $authentication, $type)
    {
        if ($authentication['type'] == 'basic' && (!isset($authentication['username']) || !isset($authentication['password']))) {
            throw new \LogicException('Username and password are mandatory if using the basic authentication');
        }

        if ($authentication['type'] == 'basic') {
            // Create an authentication service
            $authenticationDefinition = new Definition(
                'Bluetea\Api\Authentication\BasicAuthentication',
                array('username' => $authentication['username'], 'password' => $authentication['password'])
            );
        } elseif ($authentication['type'] == 'anonymous') {
            // Create an authentication service
            $authenticationDefinition = new Definition(
                'Bluetea\Api\Authentication\AnonymousAuthentication'
            );
        } else {
            throw new InvalidConfigurationException('Invalid authentication');
        }

        $container->setDefinition(sprintf('jira_rest_api.%s_authentication', $type), $authenticationDefinition);
    }

    /**
     * Create API client
     *
     * @param ContainerBuilder $container
     * @param $apiClient
     * @param $baseUrl
     * @param $type
     * @throws \LogicException
     */
    protected function createApiClient(ContainerBuilder $container, $apiClient, $baseUrl, $type)
    {
        if ($apiClient == 'guzzle') {
            // Create an API client service
            $apiClientDefinition = new Definition(
                'Bluetea\Api\Client\GuzzleClient',
                array($baseUrl, new Reference(sprintf('jira_rest_api.%s_authentication', $type)))
            );
            $apiClientDefinition->addMethodCall('setContentType', array('application/json'));
            $apiClientDefinition->addMethodCall('setAccept', array('application/json'));
        } else {
            throw new \LogicException('Invalid api client');
        }
        $container->setDefinition(sprintf('jira_rest_api.%s_api_client', $type), $apiClientDefinition);
    }

    /**
     * Initialize API endpoints
     *
     * @param ContainerBuilder $container
     * @param $availableApi
     */
    protected function initializeEndpoints(ContainerBuilder $container, $availableApi)
    {
        // Add the jira api client to the jira endpoints
        if (isset($availableApi['jira'])) {
            $taggedEndpoints = $container->findTaggedServiceIds('jira_rest_api.jira_endpoint');
            foreach ($taggedEndpoints as $serviceId => $attributes) {
                $endpoint = $container->getDefinition($serviceId);
                // Override the arguments to prevent errors
                $endpoint->setArguments(array(new Reference('jira_rest_api.jira_api_client')));
            }
        }

        // Add the crowd api client to the jira endpoints
        if (isset($availableApi['crowd'])) {
            $taggedEndpoints = $container->findTaggedServiceIds('jira_rest_api.crowd_endpoint');
            foreach ($taggedEndpoints as $serviceId => $attributes) {
                $endpoint = $container->getDefinition($serviceId);
                // Override the arguments to prevent errors
                $endpoint->setArguments(array(new Reference('jira_rest_api.crowd_api_client')));
            }
        }
    }
} 