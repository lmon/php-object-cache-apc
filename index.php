<?php

$aData = array(
    'name' => 'table',
    'color' => 'brown',
    'size' => array(
        'x' => 200,
        'y' => 120,
        'z' => 150,
    ),
    'strength' => 10,
);

require_once('classes/apc.caching.php');
$oCache = new CacheAPC();

echo 'Initial data: <pre>'; // lets see what we have
print_r($aData);
echo '</pre>';


if ($oCache->bEnabled) { // if APC enabled

    $oCache->setData('my_object', $aData); // saving data to memory
    $oCache->setData('our_class_object', $oCache); // saving object of our class into memory too

    echo 'Now we saved all in memory, click <a href="index2.php">here</a> to check what we have in memory';

} else {
    echo 'Seems APC not installed, please install it to perform tests';
}

?>

