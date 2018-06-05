<?php

namespace Jenky\GateKeeper;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Eloquent\Model;

abstract class GateKeeper
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $contexts;

    /**
     * Create new gate keeper instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get default validation rules.
     *
     * @return array
     */
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
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        return $this->model->getAttributes();
    }

    /**
     * Get all validation rules.
     *
     * @param  mixed|null $context
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

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app(Factory::class);
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance($contexts = null)
    {
        $factory = $this->getValidationFactory();
        $this->contexts = $contexts;

        if (method_exists($this, 'validator')) {
            $validator = call_user_func_array([$this, 'validator'], compact('factory'));
        } else {
            $validator = $this->createDefaultValidator($factory);
        }

        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator);
        }

        return $validator;
    }

    /**
     * Create the default validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Factory $factory
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createDefaultValidator(Factory $factory)
    {
        return $factory->make(
            $this->validationData(),
            $this->getRules($this->contexts),
            $this->messages(),
            $this->attributes()
        );
    }
}
