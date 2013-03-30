<?php


require('configs/config.php');
require('utils.php');

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initDefaultModuleAutoloader()
    { 
    
        $resourceLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath'  => APPLICATION_PATH,
        ));
    
        return $resourceLoader;
    
    }
}