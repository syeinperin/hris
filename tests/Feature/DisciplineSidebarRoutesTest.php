<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DisciplineSidebarRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function discipline_sidebar_routes_are_all_defined()
    {
        $expected = [
            'discipline.infractions.index',
            'discipline.infractions.create',
            'discipline.infractions.show',
            'discipline.infractions.edit',
            'discipline.infractions.destroy',
            'discipline.actions.index',
            'discipline.actions.create',
            'discipline.actions.show',
            'discipline.actions.edit',
            'discipline.actions.destroy',
            'discipline.investigators.index',
            'discipline.investigators.create',
            'discipline.investigators.show',
            'discipline.investigators.edit',
            'discipline.investigators.destroy',
            'discipline.types.index',
            'discipline.types.create',
            'discipline.types.show',
            'discipline.types.edit',
            'discipline.types.destroy',
        ];

        foreach ($expected as $name) {
            $this->assertTrue(
                Route::has($name),
                "Route [{$name}] should exist but does not."
            );
        }
    }
}
