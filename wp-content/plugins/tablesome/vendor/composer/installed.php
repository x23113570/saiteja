<?php return array(
    'root' => array(
        'name' => 'pauple/tablesome',
        'pretty_version' => 'dev-develop',
        'version' => 'dev-develop',
        'reference' => '64d2e58d872c94ec4919242d23ca934e2aa00acd',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'pauple/pluginator' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '271ba9f50f187868a40e37bafe0a90e0bf66e4c2',
            'type' => 'pauple-library',
            'install_path' => __DIR__ . '/../pauple/pluginator',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'pauple/tablesome' => array(
            'pretty_version' => 'dev-develop',
            'version' => 'dev-develop',
            'reference' => '64d2e58d872c94ec4919242d23ca934e2aa00acd',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
