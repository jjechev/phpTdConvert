<?php

class Routes
{

    public static $routePath = array(
        //system
#        '/FWerrorlog' => array(null, '/FWerrorlog', '../core/system/controllers/errorlog'),
#        '/FWphpinfo' => array(null, '/FWphpinfo', '../core/system/controllers/phpinfo'),
        '/errorPage404' => array(null, '/errorPage404', '../core/system/controllers/page404'),
        //projects
        
        'pageHome' => array(null, '/', 'PageHomePage', 'index'),
        'pageConvert' => array(null, '/convert', 'PageConvert', 'index'),
        'pageSetExport' => array(null, '/setExport', 'PageSetExport', 'index'),
        'pageOptions' => array(null, '/setOptions', 'PageSetOptions', 'index'),
        

    );

}
