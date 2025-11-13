<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Setting;

class PrintHeader extends Component
{
    public $hospitalName;
    public $hospitalAddress;
    public $hospitalPhone;
    public $hospitalEmail;
    public $hospitalLogo;
    public $title;
    public $patient;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title = 'Document', $patient = null)
    {
        $this->hospitalName = Setting::find('hospital_name')->value ?? 'Community General Hospital';
        $this->hospitalAddress = Setting::find('hospital_address')->value ?? '123 Main St, Anytown, USA 12345';
        $this->hospitalPhone = Setting::find('hospital_phone')->value ?? '(123) 456-7890';
        $this->hospitalEmail = Setting::find('hospital_email')->value ?? 'contact@communitygeneral.com';
        $this->hospitalLogo = Setting::find('hospital_logo')->value ?? null;
        $this->title = $title;
        $this->patient = $patient;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.print-header');
    }
}
