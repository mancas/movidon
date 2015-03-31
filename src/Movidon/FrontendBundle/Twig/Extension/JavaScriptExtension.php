<?php

namespace Movidon\FrontendBundle\Twig\Extension;

use Movidon\FrontendBundle\Twig\Extension\CustomTwigExtension;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JavaScriptExtension extends CustomTwigExtension
{
    public function getFunctions()
    {
        return array(
            'NIFValidator' => new \Twig_Function_Method($this, 'NIFValidator'),
            'getSlug' => new \Twig_Function_Method($this, 'getSlug'),
            'phoneESValidator' => new \Twig_Function_Method($this, 'phoneESValidator')
        );
    }

    public function NIFValidator()
    {
        $script = 'var NIFValidator={NIFLetters:"TRWAGMYFPDXBNJZSQVHLCKET",NIFregExp:"^\\\\d{8}[a-zA-Z]{1}$",CIFregExp:"^[a-zA-Z]{1}\\\\d{7}[a-jA-J0-9]{1}$",check:function(a){if(NIFValidator.checkCIF(a)){return true}else{if(NIFValidator.checkNIF(a)){return true}else{return false}}},checkNIF:function(e){  if(e.length==0)return true;            if((e.length!=8)&&(e.length!=9)){return false}if(e.length==8){e="0"+e}var c=new RegExp(NIFValidator.NIFregExp);if(!e.match(c)){return false}var d=e.charAt(e.length-1);var b=e.substring(0,e.length-1);var a=NIFValidator.NIFLetters.charAt(b%23);return(a==d.toUpperCase())},checkTR:function(a){if((a.length!=10)&&(a.length!=9)){return false}if((a.charAt(0).toUpperCase()!="X")&&(a.charAt(0).toUpperCase()!="Y")&&(a.charAt(0).toUpperCase()!="Z")){return false}var b="0";if(a.charAt(0).toUpperCase()=="Y"){b="1"}if(a.length==9){return NIFValidator.checkNIF(b+a.substring(1,a.length))}else{return NIFValidator.checkNIF(a.substring(1,a.length))}},checkCIF:function(e){var g=new Array(0,2,4,6,8,1,3,5,7,9);var c=e.toUpperCase();var b=0;var f;var a;var d=new RegExp(NIFValidator.CIFregExp);if(!c.match(d)){return false}if(!/^[ABCDEFGHKLMNPQS]/.test(c)){return false}for(i=2;i<=6;i+=2){b=b+g[parseInt(e.substr(i-1,1))];b=b+parseInt(e.substr(i,1))}b=b+g[parseInt(e.substr(7,1))];b=(10-(b%10));if(b==10){b=0}a=e.toUpperCase().charAt(8);return(a==b)||(b==1&&a=="A")||(b==2&&a=="B")||(b==3&&a=="C")||(b==4&&a=="D")||(b==5&&a=="E")||(b==6&&a=="F")||(b==7&&a=="G")||(b==8&&a=="H")||(b==9&&a=="I")||(b==0&&a=="J")}};';

        $this->printScript($script);
    }

    public function getSlug($capitalize = false)
    {
        $script = "function getSlug(text){
            slug=text;
            slug=slug.replace(/[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250]/g, 'a');
            slug=slug.replace(/[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD]/g, 'e');
            slug=slug.replace(/[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131]/g, 'i');
            slug=slug.replace(/[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275]/g, 'o');
            slug=slug.replace(/[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289]/g, 'u');
            slug=slug.replace(/[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5]/g, 'n');
            slug = slug.replace(/[^a-zA-Z0-9\/_|+ -]/, '');
            slug = slug.toLowerCase();
            slug = slug.replace(/^\s+|\s+$/g, '');
            slug =  slug.replace(/[\/_|+ -]+/, '-');";
        if ($capitalize) {
            $script .= "slug = slug.charAt(0).toUpperCase() + slug.slice(1);";
        }

        $script .= "return slug;}";

        $this->printScript($script);
    }

    public function getName()
    {
        return 'javascript_extension';
    }

    public function phoneESValidator()
    {
        $script = "jQuery.validator.addMethod(\"phoneES\", function(phone_number, element) {
            phone_number = phone_number.replace(/\s+/g, \"\").replace(/-/, \"\");
            return phone_number.match(/(6|9)\d{8}$/) || phone_number == '';
        }, '{{\"Introduce un número de teléfono correcto\"|trans}}');";

        $this->printScript($script);
    }
}
