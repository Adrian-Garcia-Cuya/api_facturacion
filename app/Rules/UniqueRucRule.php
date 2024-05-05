<?php

namespace App\Rules;

use App\Models\Company;
use Illuminate\Contracts\Validation\Rule;

class UniqueRucRule implements Rule
{
    public $company_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($company_id = null)
    {
        $this->company_id = $company_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $company = Company::where('ruc', $value)
                            ->where('user_id', auth()->id())
                            ->when($this->company_id, function($query, $company){
                                $query->where('id', '!=', $company);
                            })
                            ->first();
        if($company)
        {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La compania ya existe';
    }
}
