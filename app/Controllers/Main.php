<?php

namespace App\Controllers;

class Main extends BaseController
{
    public function index(): string
    {
        $menu = GetMenu();

        return view('home', [
            'title' => 'Home',
            'menu' => $menu,
            'menuJson' => json_encode($menu, JSON_UNESCAPED_SLASHES),
        ]);
    }
}
