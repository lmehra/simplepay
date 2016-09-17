<?php

Route::group(['namespace' => 'Mansa\Simplepay\Controllers', 'prefix'=>'simplepaydemo'], function() {
    // Your route goes here
        Route::get('foo', 'SimplepayController@foo');
});
?>