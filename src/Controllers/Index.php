<?php

namespace Tool01\Controllers;

use Tool01\Common\Common;

class Index
{
    public function index()
    {
        return Common::view('index/index.html');
    }
}