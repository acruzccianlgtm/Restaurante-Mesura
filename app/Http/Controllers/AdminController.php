<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\EncargadoPlato;
use App\Models\Plato;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function panel(): View
    {
        return view('panel.index', [
            'totalPlatos' => Plato::query()->count(),
            'totalCategorias' => Categoria::query()->count(),
            'totalEncargados' => EncargadoPlato::query()->count(),
        ]);
    }
}
