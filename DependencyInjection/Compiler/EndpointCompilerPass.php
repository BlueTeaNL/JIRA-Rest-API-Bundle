<?php

namespace Bluetea\JiraRestApiBundle\DependencyInjection\Compiler;


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

        // Initialize the authentication
        if (!isset($config['authentication']['type']) || $config['authentication']['type'] == 'basic') {
            if (!isset($config['authentication']['username']) || !isset($config['authentication']['password'])) {
                throw new \LogicException('Username and password are mandatory if using the basic authentication');
            }
            // Create an authentication service
            $authenticationDefinition = new Definition(
                'Bluetea\Api\Authentication\BasicAuthentication',
                array('username' => $config['authentication']['username'], 'password' => $config['authentication']['password'])
            );
        } elseif ($config['authentication']['type'] == 'anonymous') {
            // Create an authentication service
            $authenticationDefinition = new Definition(
                'Bluetea\Api\Authentication\AnonymousAuthentication'
            );
        } else {
            throw new \LogicException('Invalid authentication');
        }
        $container->setDefinition('jira_rest_api.authentication', $authenticationDefinition);

        // Initialize the api client
        if (!isset($config['api_client']) || $config['api_client'] == 'curl') {
            // Create an API client service
            $apiClientDefinition = new Definition(
                'Bluetea\Api\Client\CurlClient',
                array($config['base_url'], new Reference('jira_rest_api.authentication'))
            );
        } else {
            throw new \LogicException('Invalid api client');
        }
        $container->setDefinition('jira_rest_api.api_client', $apiClientDefinition);

        // Add the api client to the endpoints
        $taggedEndpoints = $container->findTaggedServiceIds('jira_rest_api.endpoint');
        foreach ($taggedEndpoints as $serviceId => $attributes) {
            $endpoint = $container->getDefinition($serviceId);
            // Override the arguments to prevent errors
            $endpoint->setArguments(array(new Reference('jira_rest_api.api_client')));
        }
    }
} 