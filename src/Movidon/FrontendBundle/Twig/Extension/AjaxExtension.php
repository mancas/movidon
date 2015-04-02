<?php

namespace Movidon\FrontendBundle\Twig\Extension;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Movidon\FrontendBundle\Twig\Extension\CustomTwigExtension;

class AjaxExtension extends CustomTwigExtension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array('updateSelectContentDynamically' => new \Twig_Function_Method($this, 'updateSelectContentDynamically'),
            'ajaxUpdateContent'=> new \Twig_Function_Method($this, 'updateContent'),
            'resetForm' => new \Twig_Function_Method($this, 'resetForm'),
            'ajaxForm' => new \Twig_Function_Method($this, 'ajaxForm'));
    }

    public function resetForm()
    {
        $script = "function resetForm(element) {
                $('#'+element).get(0).reset();
        }";

        $this->printScript($script);
    }

    public function ajaxForm($selector, $loaderSelector = null, $notificationSelector = null)
    {
        $script = $this->getHeaderAjaxRequest($selector) . $this->getBodyAjaxRequest($loaderSelector, $notificationSelector) . '});';

        $this->printScript($script);
    }

    private function getHeaderAjaxRequest($selector)
    {
        return "$('$selector').submit(function(e){";
    }

    private function getBodyAjaxRequest($loaderSelector = null, $notificationSelector = null)
    {
        $script = "e.preventDefault();
                var form = $(this);
                var loader = form.find('$loaderSelector');";

        if ($notificationSelector != null) {
            $script .= "var notifications = form.find('$notificationSelector');";
        }

        $script .= "loader.show();
                $.ajax({url: form.attr('action'), type: form.attr('method'), data: form.serialize()}).done(function(res){
                    loader.hide();";

        if ($notificationSelector != null) {
            $script .= "if (res.ok == false) {
                        notifications.find('.error-notification').fadeIn(400).delay(1000).fadeOut(800);
                    }
                    else {
                        notifications.find('.ok-notification').fadeIn(400).delay(1000).fadeOut(800);
                    }";
        }

        $script .= "var callbacks = form.attr('data-callback');
                    if (callbacks) {
                        var array_callbacks = callbacks.split(';');
                        for(var i=0; i<array_callbacks.length; i++) {
                            var callback = array_callbacks[i];
                            eval(callback);
                        }
                    }
            })";

        return $script;
    }

    public function updateContent()
    {
        $script = "function updateContent(url, element) {
            $.get(url, function(content){
                $('#'+element).html(content);
            });
        }";

        $this->printScript($script);
    }

    public function updateSelectContentDynamically($selectors, $updateUrl, $target = null)
    {
        $urlized = Urlizer::urlize($updateUrl, "");
        $functionName = "updateSelectContent$urlized";
        $script = "function $functionName(select)
                    {
                        var selected_parameter = select.val();";

        if (empty($target)) {
            $script .= "var select_to_update = select.next('select');" ;
        } else {
            $script .= "var select_to_update = $('$target');" ;
        }

        $script .= "var selected_content = select_to_update.val();
                        if (selected_parameter != '') {
                            $.get('$updateUrl',{selected_parameter: selected_parameter, selected_content : selected_content }, function(html_response){
                                select_to_update.html(html_response);
                            });
                        }
                    }

                    $(document).ready(function() {
                        $functionName($('$selectors'));

                        $('$selectors').change(function(){
                            $functionName($(this));
                        });
                    });";

        $this->printScript($script);
    }

    public function getName()
    {
        return 'ajax_extension';
    }
}
