<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produtores', function (Blueprint $t) {
            $t->increments('id'); $t->string('nome', 120); $t->boolean('ativo')->default(true); $t->timestamps();
        });
        Schema::create('clientes', function (Blueprint $t) {
            $t->id(); $t->string('codigo', 20)->nullable()->unique();
            $t->enum('pessoa', ['PF','PJ'])->default('PF');
            $t->enum('tipo_cliente', ['EFETIVO','PROSPECT','RELACIONAMENTO','CONDUTOR','LOCADOR'])->default('PROSPECT');
            $t->enum('status', ['ATIVO','INATIVO'])->default('ATIVO');
            $t->unsignedInteger('produtor_id')->nullable(); $t->string('intermedio', 80)->nullable();
            $t->string('nome', 150); $t->string('cpf_cnpj', 18)->nullable();
            $t->string('doc_tipo', 20)->nullable(); $t->string('doc_orgao', 20)->nullable();
            $t->string('doc_numero', 30)->nullable(); $t->date('doc_emissao')->nullable(); $t->date('doc_validade')->nullable();
            $t->string('profissao', 120)->nullable();
            $t->enum('estado_civil', ['SOLTEIRO','CASADO','DIVORCIADO','VIUVO','UNIAO_ESTAVEL'])->nullable();
            $t->date('nascimento')->nullable(); $t->enum('sexo', ['M','F','OUTRO'])->nullable();
            $t->string('faixa_renda', 40)->nullable(); $t->string('nome_fantasia', 150)->nullable();
            $t->string('inscricao_est', 30)->nullable(); $t->date('data_abertura')->nullable();
            $t->string('apelido', 80)->nullable(); $t->string('celular_padrao', 20)->nullable();
            $t->string('email_padrao', 150)->nullable(); $t->text('observacoes')->nullable();
            $t->date('data_cadastro')->useCurrent(); $t->timestamps();
            $t->foreign('produtor_id', 'fk_clientes_produtor')->references('id')->on('produtores');
            $t->index('nome', 'idx_clientes_nome'); $t->index('cpf_cnpj', 'idx_clientes_cpf');
            $t->index(['tipo_cliente','status'], 'idx_clientes_tipo');
        });
        Schema::create('cliente_conjuge', function (Blueprint $t) {
            $t->unsignedBigInteger('cliente_id')->primary(); $t->string('nome',150)->nullable();
            $t->string('cpf',14)->nullable(); $t->date('nascimento')->nullable();
            $t->foreign('cliente_id','fk_conjuge_cliente')->references('id')->on('clientes')->cascadeOnDelete();
        });
        Schema::create('cliente_cnh', function (Blueprint $t) {
            $t->unsignedBigInteger('cliente_id')->primary(); $t->string('numero_registro',20)->nullable();
            $t->string('categoria',5)->nullable(); $t->date('validade')->nullable(); $t->date('primeira_habilitacao')->nullable();
            $t->foreign('cliente_id','fk_cnh_cliente')->references('id')->on('clientes')->cascadeOnDelete();
        });
        Schema::create('cliente_enderecos', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('cliente_id'); $t->boolean('padrao')->default(false);
            $t->enum('tipo',['RESIDENCIAL','COMERCIAL','COBRANCA','OUTRO'])->default('RESIDENCIAL');
            $t->string('cep',9)->nullable(); $t->string('logradouro',150)->nullable(); $t->string('numero',15)->nullable();
            $t->string('complemento',80)->nullable(); $t->string('bairro',80)->nullable(); $t->string('cidade',80)->nullable(); $t->char('uf',2)->nullable();
            $t->foreign('cliente_id','fk_end_cliente')->references('id')->on('clientes')->cascadeOnDelete(); $t->index('cliente_id','idx_end_cliente');
        });
        Schema::create('cliente_telefones', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('cliente_id'); $t->boolean('padrao')->default(false);
            $t->enum('tipo',['CELULAR','RESIDENCIAL','COMERCIAL','WHATSAPP','0800','OUTRO'])->default('CELULAR');
            $t->string('numero',20); $t->string('observacao',120)->nullable();
            $t->foreign('cliente_id','fk_tel_cliente')->references('id')->on('clientes')->cascadeOnDelete(); $t->index('cliente_id','idx_tel_cliente');
        });
        Schema::create('cliente_emails', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('cliente_id'); $t->boolean('padrao')->default(false);
            $t->string('email',150); $t->string('observacao',120)->nullable();
            $t->foreign('cliente_id','fk_eml_cliente')->references('id')->on('clientes')->cascadeOnDelete(); $t->index('cliente_id','idx_eml_cliente');
        });
        Schema::create('cliente_contas_bancarias', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('cliente_id'); $t->string('banco',80)->nullable(); $t->string('agencia',15)->nullable();
            $t->string('conta',25)->nullable(); $t->enum('tipo',['CORRENTE','POUPANCA'])->nullable(); $t->string('titular',150)->nullable();
            $t->foreign('cliente_id','fk_cb_cliente')->references('id')->on('clientes')->cascadeOnDelete(); $t->index('cliente_id','idx_cb_cliente');
        });
        Schema::create('audit_log', function (Blueprint $t) {
            $t->id(); $t->string('usuario',120)->nullable(); $t->string('entidade',60); $t->unsignedBigInteger('entidade_id')->nullable();
            $t->enum('acao',['CRIAR','ALTERAR','EXCLUIR']); $t->json('dados_antes')->nullable(); $t->json('dados_depois')->nullable();
            $t->string('ip',45)->nullable(); $t->timestamp('created_at')->useCurrent();
            $t->index(['entidade','entidade_id'],'idx_audit_entidade'); $t->index('created_at','idx_audit_data');
        });
    }

    public function down(): void
    {
        foreach (['audit_log','cliente_contas_bancarias','cliente_emails','cliente_telefones','cliente_enderecos','cliente_cnh','cliente_conjuge','clientes','produtores'] as $table) Schema::dropIfExists($table);
    }
};
