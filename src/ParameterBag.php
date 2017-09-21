<?php

namespace Railken\Laravel\Manager;

use Railken\Bag;
use Railken\Laravel\Manager\Contracts\AgentContract;
use Railken\Laravel\Manager\Contracts\ParameterBagContract;

abstract class ParameterBag extends Bag implements ParameterBagContract
{
    
    /**
     * Filter current bag using agent
     *
     * @param AgentContract $agent
     *
     * @return this
     */
    public function filterByAgent(AgentContract $agent)
    {
        return $this;
    }
}
