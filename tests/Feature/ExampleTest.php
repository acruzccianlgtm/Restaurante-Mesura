<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_muestra_las_opciones_de_acceso(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('acceso.opciones');
    }
}
