<?php

namespace Jenky\GateKeeper;

abstract class GateKeeper
{
    abstract public function rules();

     /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * Get all validation rules.
     *
     * @return array
     */
    public function getRules($context = null)
    {
        if (! $context) {
            $rules = $this->rules();

            return is_array($rules) ? $rules : [];
        }

        $rules = [];

        foreach ((array) $context as $method) {
            if (method_exists($this, $method)) {
                $rule = call_user_func($this, $method);

                if (is_array($rule)) {
                    $rules = array_merge($rule, $rules);
                }
            }
        }

        return $rules;
    }
}
