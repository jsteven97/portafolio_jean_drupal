<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/contrib/views_bootstrap/templates/views-bootstrap-accordion.html.twig */
class __TwigTemplate_65cfad6224d2a9e4876f19b47c5bbc381d49f6dec666e7a32edf8577e3f06377 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["set" => 2, "for" => 4];
        $filters = ["escape" => 1, "length" => 3];
        $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'for'],
                ['escape', 'length'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<div id=\"";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["id"] ?? null)), "html", null, true);
        echo "\" ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["attributes"] ?? null), "addClass", [0 => ($context["classes"] ?? null)], "method")), "html", null, true);
        echo ">
  ";
        // line 2
        $context["i"] = 0;
        // line 3
        echo "  ";
        $context["len"] = twig_length_filter($this->env, $this->sandbox->ensureToStringAllowed(($context["rows"] ?? null)));
        // line 4
        echo "  ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["rows"] ?? null));
        foreach ($context['_seq'] as $context["key"] => $context["row"]) {
            // line 5
            $context["i"] = (($context["i"] ?? null) + 1);
            // line 6
            echo "    ";
            $context["collapse_class"] = (((((((            // line 7
($context["i"] ?? null) == 1) && $this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", [], "any", false, true), "first", [], "any", true, true)) && ($this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", []), "first", []) > 0)) || (((            // line 8
($context["i"] ?? null) == ($context["len"] ?? null)) && $this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", [], "any", false, true), "last", [], "any", true, true)) && ($this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", []), "last", []) > 0))) || ((((            // line 9
($context["i"] ?? null) != 1) && (($context["i"] ?? null) != ($context["len"] ?? null))) && $this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", [], "any", false, true), "middle", [], "any", true, true)) && ($this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", []), "middle", []) > 0)))) ? ("collapse show") : ("collapse"));
            // line 12
            echo "    ";
            $context["collapse_class_boolean"] = (((((((            // line 13
($context["i"] ?? null) == 1) && $this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", [], "any", false, true), "first", [], "any", true, true)) && ($this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", []), "first", []) > 0)) || (((            // line 14
($context["i"] ?? null) == ($context["len"] ?? null)) && $this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", [], "any", false, true), "last", [], "any", true, true)) && ($this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", []), "last", []) > 0))) || ((((            // line 15
($context["i"] ?? null) != 1) && (($context["i"] ?? null) != ($context["len"] ?? null))) && $this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", [], "any", false, true), "middle", [], "any", true, true)) && ($this->getAttribute($this->getAttribute(($context["options"] ?? null), "collapse", []), "middle", []) > 0)))) ? ("true") : ("false"));
            // line 18
            echo "      <div class=\"card\">
        <div class=\"class-header\" id=\"heading";
            // line 19
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($context["key"]), "html", null, true);
            echo "\">
          <h5 class=\"mb-0\">
            <button class=\"btn btn-link\" data-toggle=\"collapse\" data-target=\"#";
            // line 21
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["id"] ?? null)), "html", null, true);
            echo "-collapse-";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($context["key"]), "html", null, true);
            echo "\" aria-expanded=\"";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["collapse_class_boolean"] ?? null)), "html", null, true);
            echo "\" aria-controls=\"collapseOne\">
              ";
            // line 22
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute($context["row"], "title", [])), "html", null, true);
            echo "
            </button>
          </h5>
        </div>

        <div id=\"";
            // line 27
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["id"] ?? null)), "html", null, true);
            echo "-collapse-";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($context["key"]), "html", null, true);
            echo "\" class=\"";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["collapse_class"] ?? null)), "html", null, true);
            echo "\" aria-labelledby=\"heading";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($context["key"]), "html", null, true);
            echo "\" data-parent=\"#";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["id"] ?? null)), "html", null, true);
            echo "\">
          <div class=\"card-body\">
              ";
            // line 29
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute($context["row"], "content", [])), "html", null, true);
            echo "
          </div>
        </div>
      </div>";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['key'], $context['row'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 34
        echo "</div>
";
    }

    public function getTemplateName()
    {
        return "modules/contrib/views_bootstrap/templates/views-bootstrap-accordion.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  133 => 34,  123 => 29,  110 => 27,  102 => 22,  94 => 21,  89 => 19,  86 => 18,  84 => 15,  83 => 14,  82 => 13,  80 => 12,  78 => 9,  77 => 8,  76 => 7,  74 => 6,  72 => 5,  67 => 4,  64 => 3,  62 => 2,  55 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "modules/contrib/views_bootstrap/templates/views-bootstrap-accordion.html.twig", "/var/www/html/web/modules/contrib/views_bootstrap/templates/views-bootstrap-accordion.html.twig");
    }
}
