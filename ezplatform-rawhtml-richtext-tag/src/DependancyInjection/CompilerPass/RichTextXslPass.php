<?php

use Ibexa\Bundle\Core\DependencyInjection\Configuration\ConfigResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RichTextXslPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $scopes = array_merge(
            [ConfigResolver::SCOPE_DEFAULT],
            $container->getParameter('ibexa.site_access.list')
        );
        $configs = [
            'output_custom_xsl' => ['xhtml5/output/rawhtml.xsl'],
        ];
        foreach ($scopes as $scope) {
            foreach ($configs as $type => $extraRules) {
                $this->addCustomXsl($container, $scope, $type, $extraRules);
            }
        }
    }
    private function addCustomXsl(
        ContainerBuilder $container,
        string $scope,
        string $type,
        array $rules
    ): void {
        $parameter = "ibexa.site_access.config.{$scope}.fieldtypes.ezrichtext.{$type}";
        if (!$container->hasParameter($parameter)) {
            return;
        }
        $extraRules = [];
        foreach ($rules as $rule) {
            $extraRules[] = [
                'path' => __DIR__ . '/../../Resources/xsl/' . $rule,
                'priority' => 250,
            ];
        }
        $newRules = array_merge(
            $container->getParameter($parameter),
            $extraRules
        );
        $container->setParameter($parameter, $newRules);
    }
}
?>