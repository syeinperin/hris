<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SearchBar extends Component
{
    /**
     * The URL or route the form should submit to.
     */
    public $action;

    /**
     * Placeholder text for the input.
     */
    public $placeholder;

    /**
     * Any extra filters (as an associative array of name=>options).
     */
    public $filters;

    /**
     * Create the component instance.
     */
    public function __construct($action, $placeholder = 'Search...', $filters = [])
    {
        $this->action      = $action;
        $this->placeholder = $placeholder;
        $this->filters     = $filters;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.search-bar');
    }
}
