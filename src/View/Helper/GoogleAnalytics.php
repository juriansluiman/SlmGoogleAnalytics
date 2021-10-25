<?php
namespace LaminasGoogleAnalytics\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\HeadScript;
use LaminasGoogleAnalytics\Exception\RuntimeException;

class GoogleAnalytics extends AbstractHelper
{
    protected string $containerName = 'InlineScript';
    protected bool $rendered = false;

    protected Script\ScriptInterface $script;

    public function __construct(Script\ScriptInterface $script)
    {
        $this->script = $script;
    }

    public function getContainerName(): string
    {
        return $this->containerName;
    }

    public function setContainerName($container)
    {
        $this->containerName = $container;
    }

    protected function getContainer()
    {
        $containerName = $this->getContainerName();
        $container     = $this->view->plugin($containerName);

        return $container;
    }

    public function __invoke(): GoogleAnalytics
    {
        return $this;
    }

    public function appendScript()
    {
        // Do not render the GA twice
        if ($this->rendered) {
            return;
        }

        // We need to be sure $container->appendScript() can be called
        $container = $this->getContainer();
        if (!$container instanceof HeadScript) {
            throw new RuntimeException(sprintf(
                'Container %s does not extend HeadScript view helper',
                $this->getContainerName()
            ));
        }

        $code = $this->script->getCode();

        if (empty($code)) {
            return;
        }

        $container->appendScript($code);

        // Mark this GA as rendered
        $this->rendered = true;

        return $this;
    }
}
