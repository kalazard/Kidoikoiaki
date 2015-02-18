<?php

/* OCKidoikoiakiBundle:Default:index.html.twig */
class __TwigTemplate_26c4869f1057522b46262187b8e2c2fc0e6d4bc2b0b569e9d3d8283734d1137a extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<html>
  <body>
    Hello ";
        // line 3
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : $this->getContext($context, "name")), "html", null, true);
        echo "!
  </body>
</html>";
    }

    public function getTemplateName()
    {
        return "OCKidoikoiakiBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  23 => 3,  19 => 1,);
    }
}
