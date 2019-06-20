<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src/Translatable');

return PhpCsFixer\Config::create()
    ->setRules([
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'method_public_static',
                'method_public',
                'method_protected_static',
                'method_protected',
                'method_private_static',
                'method_private',
                'destruct',
                'magic',
            ],
            'sortAlgorithm' => 'alpha',
        ],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => null,
        ],
    ])
    ->setFinder($finder);
