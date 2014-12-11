JIRA Rest API for Symfony2
==========================

This Symfony2 Bundle uses the <a href="https://github.com/BlueTeaNL/JIRA-Rest-API-PHP">JIRA REST API PHP Library</a>.
This bundle adds configuration and initializes the endpoints with dependency injection and tagged services.

# Installation

Add this bundle to your composer.json

```
composer.phar require "bluetea/jira-rest-api-bundle" dev-master
```

Enable it in the AppKernel.php

```
new Bluetea\JiraRestApiBundle\BlueteaJiraRestApiBundle(),
```

Add the configuration to your config.yml

```
bluetea_jira_rest_api:
    api_client: guzzle
    api:
        jira: https://atlassian.domain.com/rest/api/2/
        crowd: https://atlassian.domain.com/crowd/rest/usermanagement/latest/
    authentication:
        jira:
            type: basic # or anonymous
            username: username # mandatory is basic authentication is chosen
            password: password # mandatory is basic authentication is chosen
        crowd:
            type: basic # or anonymous
            username: username # mandatory is basic authentication is chosen
            password: password # mandatory is basic authentication is chosen
```

# Usage


```
<?php

namespace Acme\DemoBundle\Controller;

class TestController extends Controller
{
    public function testAction()
    {
        $projectEndpoint = $this->get('jira_rest_api.jira.endpoint.project');
        // Get all projects
        return new JsonResponse($projectEndpoint->findAll());
    }
}
```

Check the <a href="https://github.com/BlueTeaNL/JIRA-Rest-API-PHP">JIRA REST API PHP Library</a> for all the endpoints.
The endpoints are loaded in the services.yml. If you want to add your custom endpoints you can do this in your own bundle
and tag them with the `jira_rest_api.jira_endpoint` tag for a jira endpoint and the `jira_rest_api.crowd_endpoint` tag
for a crowd endpoint.

Example:

```
parameters:
    acme_demo.endpoint.custom.class: Acme\DemoBundle\Endpoint\CustomEndpoint

services:
    acme_demo.endpoint.custom:
        class: %acme_demo.endpoint.custom.class%
        tags:
            - { name: jira_rest_api.jira_endpoint }
```

We would greatly appreciate if you submit a PR if you expand the endpoints in <a href="https://github.com/BlueTeaNL/JIRA-Rest-API-PHP">JIRA REST API PHP Library</a>!

# Documentation

<a href="https://docs.atlassian.com/jira/REST/6.3.10/">JIRA 6.3.10 REST API Documentation</a>

<a href="https://developer.atlassian.com/display/JIRADEV/JIRA+REST+APIs">JIRA REST API Developers Documentation</a>

<a href="https://developer.atlassian.com/display/CROWDDEV/Crowd+REST+Resources">Crowd REST Resources</a>

# Debug

Install the JIRA REST API browser to test your calls. You find it at https://atlassian.yourdomain.com/plugins/servlet/restbrowser.
Your getting a 404? Install the plugin first via the plugin manager.
