<?php

require_once('classes/apc.caching.php');
$oCache = new CacheAPC();

if ($oCache->bEnabled) { // if APC enabled

    $oCache->delData('my_object'); // removing data from memory
    $oCache->delData('our_class_object'); // removing data from memory

    $aMemData = $oCache->getData('my_object'); // lets try to get data again
    $aMemData2 = $oCache->getData('our_class_object');

    echo 'Data from memory: <pre>'; // lets see what we have from memory
    print_r($aMemData);
    echo '</pre>';

    echo 'Data from memory of object of CacheAPC class: <pre>';
    print_r($aMemData2);
    echo '</pre>';

    echo 'As you can see - all data successfully removed. Great !';

} else {
    echo 'Seems APC not installed, please install it to perform tests';
}

?>

