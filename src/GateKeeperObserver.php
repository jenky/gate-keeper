<?php

namespace Jenky\GateKeeper;

use Illuminate\Database\Eloquent\Model;

class GateKeeperObserver
{
    /**
     * Listen to the model saving event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function saving(Model $model)
    {
        $model->validate();
    }
}
