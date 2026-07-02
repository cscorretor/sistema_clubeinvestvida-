<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $digitsOrNull = static function (mixed $value): ?string {
            $digits = preg_replace('/\D/', '', (string) $value);

            return $digits !== '' ? $digits : null;
        };

        $conjuge = is_array($this->input('conjuge')) ? $this->input('conjuge') : [];
        $conjuge['cpf'] = $digitsOrNull($conjuge['cpf'] ?? null);

        $enderecosInput = is_array($this->input('enderecos')) ? $this->input('enderecos') : [];
        $enderecos = collect($enderecosInput)
            ->map(function (mixed $endereco) use ($digitsOrNull): mixed {
                if (! is_array($endereco)) {
                    return $endereco;
                }

                $endereco['cep'] = $digitsOrNull($endereco['cep'] ?? null);
                $endereco['uf'] = mb_strtoupper(trim((string) ($endereco['uf'] ?? '')));

                return $endereco;
            })->all();

        $emailsInput = is_array($this->input('emails')) ? $this->input('emails') : [];
        $emails = collect($emailsInput)
            ->map(function (mixed $email): mixed {
                if (! is_array($email)) {
                    return $email;
                }

                $normalized = mb_strtolower(trim((string) ($email['email'] ?? '')));
                $email['email'] = $normalized !== '' ? $normalized : null;

                return $email;
            })->all();

        $this->merge([
            'pessoa' => mb_strtoupper((string) $this->input('pessoa', 'PF')),
            'nome' => trim((string) $this->input('nome')),
            'cpf_cnpj' => $digitsOrNull($this->input('cpf_cnpj')),
            'conjuge' => $conjuge,
            'enderecos' => $enderecos,
            'emails' => $emails,
        ]);
    }

    public function rules(): array
    {
        $documentRule = $this->input('pessoa') === 'PJ' ? new Cnpj : new Cpf;
        $routeCliente = $this->route('cliente');
        $clienteId = $routeCliente instanceof Cliente
            ? $routeCliente->getKey()
            : (is_numeric($routeCliente) ? (int) $routeCliente : null);
        $documentRules = ['required', 'string', 'max:14', $documentRule];
        $documentoAtual = $clienteId
            ? Cliente::query()->whereKey($clienteId)->value('cpf_cnpj')
            : null;

        if (! $clienteId || $documentoAtual !== $this->input('cpf_cnpj')) {
            $documentRules[] = Rule::unique('clientes', 'cpf_cnpj')->ignore($clienteId);
        }

        return [
            'pessoa' => ['required', Rule::in(['PF', 'PJ'])],
            'nome' => ['required', 'string', 'max:150'],
            'cpf_cnpj' => $documentRules,
            'nascimento' => ['nullable', 'date', 'before_or_equal:today'],
            'estado_civil' => ['nullable', Rule::in(['SOLTEIRO', 'CASADO', 'DIVORCIADO', 'VIUVO', 'UNIAO_ESTAVEL'])],
            'sexo' => ['nullable', Rule::in(['M', 'F', 'OUTRO'])],
            'profissao' => ['nullable', 'string', 'max:120'],
            'faixa_renda' => ['nullable', 'string', 'max:40'],
            'tipo_cliente' => ['required', Rule::in(['EFETIVO', 'PROSPECT', 'RELACIONAMENTO', 'CONDUTOR', 'LOCADOR'])],
            'intermedio' => ['nullable', Rule::in(Cliente::ORIGENS)],

            'conjuge' => ['nullable', 'array'],
            'conjuge.nome' => ['nullable', 'string', 'max:150'],
            'conjuge.cpf' => ['nullable', 'string', 'max:11', new Cpf],
            'conjuge.nascimento' => ['nullable', 'date', 'before_or_equal:today'],

            'tem_cnh' => ['nullable', 'boolean'],
            'cnh' => ['nullable', 'array'],
            'cnh.numero_registro' => ['nullable', 'string', 'max:20'],
            'cnh.categoria' => ['nullable', 'string', 'max:5'],
            'cnh.validade' => ['nullable', 'date'],
            'cnh.primeira_habilitacao' => ['nullable', 'date', 'before_or_equal:today'],

            'endereco_padrao' => ['nullable', 'integer', 'min:0'],
            'enderecos' => ['nullable', 'array', 'max:5'],
            'enderecos.*.tipo' => ['nullable', Rule::in(['RESIDENCIAL', 'COMERCIAL', 'COBRANCA', 'OUTRO'])],
            'enderecos.*.cep' => ['nullable', 'digits:8'],
            'enderecos.*.logradouro' => ['nullable', 'string', 'max:150'],
            'enderecos.*.numero' => ['nullable', 'string', 'max:15'],
            'enderecos.*.complemento' => ['nullable', 'string', 'max:80'],
            'enderecos.*.bairro' => ['nullable', 'string', 'max:80'],
            'enderecos.*.cidade' => ['nullable', 'string', 'max:80'],
            'enderecos.*.uf' => ['nullable', 'string', 'size:2'],

            'telefones' => ['nullable', 'array', 'max:10'],
            'telefones.*.tipo' => ['nullable', Rule::in(['CELULAR', 'RESIDENCIAL', 'COMERCIAL', 'WHATSAPP', '0800', 'OUTRO'])],
            'telefones.*.numero' => ['nullable', 'string', 'max:20'],
            'emails' => ['nullable', 'array', 'max:10'],
            'emails.*.email' => ['nullable', 'email:rfc', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'cpf_cnpj' => $this->input('pessoa') === 'PJ' ? 'CNPJ' : 'CPF',
            'nascimento' => 'data de nascimento',
            'conjuge.cpf' => 'CPF do cônjuge',
            'emails.*.email' => 'e-mail',
            'enderecos.*.cep' => 'CEP',
        ];
    }

    public function messages(): array
    {
        return [
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado. Localize o cliente existente antes de continuar.',
            'intermedio.in' => 'Selecione uma origem válida para o cliente.',
        ];
    }
}
