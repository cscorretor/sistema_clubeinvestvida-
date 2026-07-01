<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\Usuario;

class ClientePolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->isProdutor() || $usuario->pode('clientes');
    }

    public function view(Usuario $usuario, Cliente $cliente): bool
    {
        if ($usuario->isProdutor()) {
            return $usuario->produtor_id !== null
                && $usuario->produtor_id === $cliente->produtor_id;
        }

        return $usuario->pode('clientes');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isProdutor() || $usuario->pode('clientes', 'pode_editar');
    }

    public function update(Usuario $usuario, Cliente $cliente): bool
    {
        return $this->view($usuario, $cliente)
            && ($usuario->isProdutor() || $usuario->pode('clientes', 'pode_editar'));
    }

    public function delete(Usuario $usuario, Cliente $cliente): bool
    {
        return $this->update($usuario, $cliente);
    }
}
