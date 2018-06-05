<?php

namespace Jenky\GateKeeper;

use Illuminate\Contracts\Validation\Factory;
use Jenky\GateKeeper\Exceptions\GateKeeperException;

trait SelfValidates
{
    /**
     * The gate keeper object.
     *
     * @var GateKeeper
     */
    protected $gateKeeperInstance;

    /**
     * Current context.
     *
     * @var string
     */
    protected $currenctContext;

    /**
     * Indicates if the model is currently force deleting.
     *
     * @var bool
     */
    protected $selfValidating = true;

    /**
     * Boot the self validate trait for a model.
     *
     * @return void
     */
    public static function bootSelfValidates()
    {
        static::registerModelEvent('saving', GateKeeperObserver::class.'@saving');
    }

    /**
     * Get the gate keeper instance.
     *
     * @return GateKeeper
     * @throws Exceptions\GateKeeperException
     */
    public function gateKeeper()
    {
        if (is_object($this->gateKeeper)) {
            return $this->gateKeeper;
        }

        if (property_exists($this, 'gateKeeper') and class_exists($this->gateKeeper)) {
            $gateKeeper = new $this->gateKeeper($this);

            if (! $gateKeeper instanceof GateKeeper) {
                throw new GateKeeperException(get_class($this).' must be an instance of '.GateKeeper::class);
            }

            return $this->gateKeeperInstance = $gateKeeper;
        }

        throw new GateKeeperException('Property $gateKeeper was not set correctly in '.get_class($this));
    }

    /**
     * Set the gate keeper instance.
     *
     * @param  GateKeeper $gateKeeper
     * @return $this
     */
    public function setGateKeeper(GateKeeper $gateKeeper)
    {
        $this->gateKeeperInstance = $gateKeeper;

        return $this;
    }

    /**
     * Validate the given data with the given rules.
     *
     * @return void
     */
    public function validate()
    {
        if (! $this->selfValidating) {
            return;
        }

        $rules = $this->gateKeeper()->getRules($this->context);

        dd($rules);

        throw new \Exception('Validation failed.');
    }

    /**
     * Validate the given data with the given rules.
     *
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     */
    public function validateWith(array $rules, array $messages = [], array $customAttributes = [])
    {
    }

    /**
     * Force a save without validation on a model.
     *
     * @return bool|null
     */
    public function forceSave(array $options = [])
    {
        $this->selfValidating = false;

        return tap($this->save($options), function ($saved) {
            $this->selfValidating = true;

            if ($saved) {
                $this->fireModelEvent('forceSaved', false);
            }
        });
    }

    /**
     * Register a validating model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function validating($callback)
    {
        static::registerModelEvent('validating', $callback);
    }

    /**
     * Register a validated model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function validated($callback)
    {
        static::registerModelEvent('validated', $callback);
    }

    /**
     * Set current context.
     *
     * @param  string $context
     * @return $this
     */
    public function onContext($key)
    {
        $this->currentContext = is_array($key) ? $key : func_get_args();

        return $this;
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
}
