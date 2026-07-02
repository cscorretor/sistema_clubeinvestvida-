<?php

namespace App\Policies;

use App\Models\Apolice;
use App\Models\Cliente;
use App\Models\Usuario;

class ApolicePolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->isProdutor() || $usuario->pode('clientes');
    }

    public function view(Usuario $usuario, Apolice $apolice): bool
    {
        return $usuario->can('view', $apolice->cliente);
    }

    public function create(Usuario $usuario, Cliente $cliente): bool
    {
        return $usuario->can('update', $cliente);
    }

    public function update(Usuario $usuario, Apolice $apolice): bool
    {
        return $usuario->can('update', $apolice->cliente);
    }
}
