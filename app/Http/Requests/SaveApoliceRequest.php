<?php

namespace App\Http\Requests;

use App\Models\Apolice;
use App\Models\Ramo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveApoliceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $money = static function (mixed $value): mixed {
            if (! is_string($value) || trim($value) === '') {
                return $value;
            }

            $value = trim($value);

            return str_contains($value, ',')
                ? str_replace(',', '.', str_replace('.', '', $value))
                : $value;
        };

        $vidas = collect(is_array($this->input('vidas')) ? $this->input('vidas') : [])
            ->map(function (mixed $vida) use ($money): mixed {
                if (is_array($vida)) {
                    $vida['capital'] = $money($vida['capital'] ?? null);
                }

                return $vida;
            })->all();

        $beneficiarios = collect(is_array($this->input('beneficiarios')) ? $this->input('beneficiarios') : [])
            ->map(function (mixed $beneficiario) use ($money): mixed {
                if (is_array($beneficiario)) {
                    $beneficiario['percentual'] = $money($beneficiario['percentual'] ?? null);
                }

                return $beneficiario;
            })->all();

        $this->merge([
            'num_proposta' => $this->filled('num_proposta') ? trim((string) $this->input('num_proposta')) : null,
            'num_apolice' => $this->filled('num_apolice') ? trim((string) $this->input('num_apolice')) : null,
            'capital_segurado' => $money($this->input('capital_segurado')),
            'valor_mensal' => $money($this->input('valor_mensal')),
            'vidas' => $vidas,
            'beneficiarios' => $beneficiarios,
            'dados_produto' => [
                ...(is_array($this->input('dados_produto')) ? $this->input('dados_produto') : []),
                'uf_imovel' => mb_strtoupper(trim((string) $this->input('dados_produto.uf_imovel'))),
            ],
        ]);
    }

    public function rules(): array
    {
        $apoliceId = is_numeric($this->route('apolice')) ? (int) $this->route('apolice') : null;
        $coberturasPermitidas = collect(Apolice::COBERTURAS_POR_PRODUTO)->flatten()->all();

        return [
            'ramo_id' => ['required', 'integer', 'exists:ramos,id'],
            'seguradora_id' => ['required', 'integer', 'exists:seguradoras,id'],
            'tipo_proposta' => ['required', Rule::in(['NOVO', 'RENOVACAO', 'ENDOSSO'])],
            'num_proposta' => [
                'nullable',
                'required_without:num_apolice',
                'string',
                'max:40',
                Rule::unique('apolices', 'num_proposta')->ignore($apoliceId),
            ],
            'num_apolice' => [
                'nullable',
                'required_without:num_proposta',
                'string',
                'max:40',
                Rule::unique('apolices', 'num_apolice')->ignore($apoliceId),
            ],
            'status' => ['required', Rule::in(['PROSPECCAO', 'EM_EMISSAO', 'ATIVO', 'RENOVACAO', 'CANCELADO', 'INATIVO'])],
            'inicio_vigencia' => ['nullable', 'date'],
            'fim_vigencia' => ['nullable', 'date', 'after_or_equal:inicio_vigencia'],
            'capital_segurado' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],

            'vidas' => ['nullable', 'array', 'max:20'],
            'vidas.*.nome' => ['required', 'string', 'max:150'],
            'vidas.*.parentesco' => ['required', Rule::in(['TITULAR', 'CONJUGE', 'FILHO', 'PAI_MAE', 'OUTRO'])],
            'vidas.*.nascimento' => ['nullable', 'date', 'before_or_equal:today'],
            'vidas.*.capital' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],

            'beneficiarios' => ['nullable', 'array', 'max:30'],
            'beneficiarios.*.nome' => ['required', 'string', 'max:150'],
            'beneficiarios.*.parentesco' => ['nullable', 'string', 'max:30'],
            'beneficiarios.*.percentual' => ['required', 'numeric', 'min:0.01', 'max:100'],

            'coberturas' => ['required', 'array', 'min:1'],
            'coberturas.*' => ['required', 'string', Rule::in($coberturasPermitidas)],

            'valor_mensal' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'primeiro_vencimento' => ['required', 'date'],

            'dados_produto' => ['nullable', 'array'],
            'dados_produto.modalidade_previdencia' => ['nullable', Rule::in(['PGBL', 'VGBL'])],
            'dados_produto.regime_tributario' => ['nullable', Rule::in(['PROGRESSIVO', 'REGRESSIVO'])],
            'dados_produto.acomodacao' => ['nullable', Rule::in(['ENFERMARIA', 'APARTAMENTO'])],
            'dados_produto.abrangencia' => ['nullable', Rule::in(['REGIONAL', 'ESTADUAL', 'NACIONAL'])],
            'dados_produto.coparticipacao' => ['nullable', 'boolean'],
            'dados_produto.tipo_imovel' => ['nullable', Rule::in(['CASA', 'APARTAMENTO', 'CONDOMINIO', 'OUTRO'])],
            'dados_produto.cep_imovel' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],
            'dados_produto.endereco_imovel' => ['nullable', 'string', 'max:180'],
            'dados_produto.cidade_imovel' => ['nullable', 'string', 'max:80'],
            'dados_produto.uf_imovel' => ['nullable', 'string', 'size:2'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $ramo = Ramo::query()->find($this->integer('ramo_id'));

                if (! $ramo || ! in_array($ramo->nome, Apolice::PRODUTOS_SUPORTADOS, true)) {
                    $validator->errors()->add('ramo_id', 'Selecione um produto de seguro disponível nesta etapa.');

                    return;
                }

                if (in_array($this->input('status'), ['ATIVO', 'RENOVACAO'], true) && ! $this->filled('num_apolice')) {
                    $validator->errors()->add('num_apolice', 'Informe o número da apólice para os status Ativo ou Renovação.');
                }

                if (in_array($this->input('status'), ['ATIVO', 'RENOVACAO'], true)) {
                    if (! $this->filled('inicio_vigencia')) {
                        $validator->errors()->add('inicio_vigencia', 'Informe o início da vigência.');
                    }
                    if (! $this->filled('fim_vigencia')) {
                        $validator->errors()->add('fim_vigencia', 'Informe o fim da vigência.');
                    }
                }

                $coberturas = array_values(array_filter(
                    (array) $this->input('coberturas'),
                    static fn (mixed $cobertura): bool => is_string($cobertura) && $cobertura !== '',
                ));
                $permitidas = Apolice::COBERTURAS_POR_PRODUTO[$ramo->nome] ?? [];
                if (array_diff($coberturas, $permitidas) !== []) {
                    $validator->errors()->add('coberturas', 'Existe uma cobertura incompatível com o produto selecionado.');
                }

                if (in_array($ramo->nome, ['Vida', 'Saúde'], true) && count((array) $this->input('vidas')) === 0) {
                    $validator->errors()->add('vidas', 'Informe ao menos uma vida segurada.');
                }

                if ($ramo->nome === 'Previdência') {
                    if (! $this->filled('dados_produto.modalidade_previdencia')) {
                        $validator->errors()->add('dados_produto.modalidade_previdencia', 'Informe se o plano é PGBL ou VGBL.');
                    }
                    if (! $this->filled('dados_produto.regime_tributario')) {
                        $validator->errors()->add('dados_produto.regime_tributario', 'Informe o regime tributário.');
                    }
                }

                if ($ramo->nome === 'Saúde') {
                    if (! $this->filled('dados_produto.acomodacao')) {
                        $validator->errors()->add('dados_produto.acomodacao', 'Informe a acomodação do plano.');
                    }
                    if (! $this->filled('dados_produto.abrangencia')) {
                        $validator->errors()->add('dados_produto.abrangencia', 'Informe a abrangência do plano.');
                    }
                }

                if ($ramo->nome === 'Residencial') {
                    foreach ([
                        'dados_produto.tipo_imovel' => 'Informe o tipo do imóvel.',
                        'dados_produto.cep_imovel' => 'Informe o CEP do imóvel.',
                        'dados_produto.endereco_imovel' => 'Informe o endereço do imóvel.',
                        'dados_produto.cidade_imovel' => 'Informe a cidade do imóvel.',
                        'dados_produto.uf_imovel' => 'Informe a UF do imóvel.',
                    ] as $field => $message) {
                        if (! $this->filled($field)) {
                            $validator->errors()->add($field, $message);
                        }
                    }
                }

                $beneficiarios = (array) $this->input('beneficiarios');
                if ($beneficiarios !== []) {
                    $total = collect($beneficiarios)->sum(
                        fn (mixed $item): float => is_array($item) ? (float) ($item['percentual'] ?? 0) : 0,
                    );
                    if (abs($total - 100) > 0.001) {
                        $validator->errors()->add('beneficiarios', 'Os percentuais dos beneficiários devem somar exatamente 100%.');
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'num_proposta.required_without' => 'Informe o número da proposta ou o número da apólice.',
            'num_apolice.required_without' => 'Informe o número da apólice ou o número da proposta.',
            'num_proposta.unique' => 'Este número de proposta já está cadastrado.',
            'num_apolice.unique' => 'Este número de apólice já está cadastrado.',
            'coberturas.required' => 'Selecione ao menos uma cobertura.',
            'fim_vigencia.after_or_equal' => 'O fim da vigência não pode ser anterior ao início.',
        ];
    }
}
