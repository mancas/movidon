<?php

namespace Movidon\FrontendBundle\Twig\Extension;

class CustomTwigExtension extends \Twig_Extension
{
    protected $environment;

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getFunctions()
    {
    }

    public function getName()
    {
    }

    protected function printScript($script, $printDocumentReady=false)
    {
        if ($printDocumentReady) {
            printf('$(document).ready(function(){%s});', $script);
        } else {
            print($script);
        }
    }

    protected function getJqueryClose()
    {
        return "});";
    }
}
