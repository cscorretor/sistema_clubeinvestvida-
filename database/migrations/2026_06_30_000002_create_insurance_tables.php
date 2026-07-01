<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seguradoras', fn (Blueprint $t) => [$t->increments('id'), $t->string('nome',100), $t->boolean('ativo')->default(true)]);
        Schema::create('ramos', fn (Blueprint $t) => [$t->increments('id'), $t->string('nome',60), $t->enum('grupo',['PESSOAS','PATRIMONIAL'])->default('PESSOAS')]);
        Schema::create('apolices', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('cliente_id'); $t->unsignedInteger('ramo_id'); $t->unsignedInteger('seguradora_id')->nullable(); $t->unsignedInteger('produtor_id')->nullable();
            $t->string('num_proposta',40)->nullable(); $t->string('num_apolice',40)->nullable();
            $t->enum('status',['PROSPECCAO','EM_EMISSAO','ATIVO','RENOVACAO','CANCELADO','INATIVO'])->default('EM_EMISSAO');
            $t->date('inicio_vigencia')->nullable(); $t->date('fim_vigencia')->nullable(); $t->decimal('capital_segurado',14,2)->nullable();
            $t->enum('tipo_proposta',['NOVO','RENOVACAO','ENDOSSO'])->default('NOVO'); $t->unsignedBigInteger('apolice_origem_id')->nullable(); $t->timestamps();
            $t->foreign('cliente_id','fk_ap_cli')->references('id')->on('clientes'); $t->foreign('ramo_id','fk_ap_ramo')->references('id')->on('ramos');
            $t->foreign('seguradora_id','fk_ap_seg')->references('id')->on('seguradoras'); $t->foreign('produtor_id','fk_ap_prod')->references('id')->on('produtores');
            $t->index('cliente_id','idx_ap_cliente'); $t->index('fim_vigencia','idx_ap_venc'); $t->index('status','idx_ap_status');
        });
        $this->child('apolice_vidas', function(Blueprint $t){ $t->string('nome',150); $t->enum('parentesco',['TITULAR','CONJUGE','FILHO','PAI_MAE','OUTRO'])->default('TITULAR'); $t->date('nascimento')->nullable(); $t->decimal('capital',14,2)->nullable(); }, 'fk_vida_ap','idx_vida_ap');
        $this->child('apolice_beneficiarios', function(Blueprint $t){ $t->string('nome',150); $t->string('parentesco',30)->nullable(); $t->decimal('percentual',5,2)->default(0); }, 'fk_benef_ap','idx_benef_ap');
        $this->child('apolice_coberturas', function(Blueprint $t){ $t->string('descricao',120); $t->decimal('capital',14,2)->nullable(); }, 'fk_cob_ap','idx_cob_ap');
        $this->child('apolice_parcelas', function(Blueprint $t){ $t->integer('numero'); $t->date('vencimento')->nullable(); $t->decimal('valor_cliente',12,2)->nullable(); $t->decimal('valor_comissao',12,2)->nullable(); $t->decimal('percentual_comissao',5,2)->nullable(); $t->enum('status',['ABERTO','LIQUIDADO','CANCELADO'])->default('ABERTO'); $t->index('vencimento','idx_parc_venc'); $t->index('status','idx_parc_status'); }, 'fk_parc_ap','idx_parc_ap');
        Schema::create('apolice_rateio', function(Blueprint $t){ $t->id(); $t->unsignedBigInteger('apolice_id'); $t->unsignedInteger('produtor_id'); $t->decimal('percentual',5,2); $t->foreign('apolice_id','fk_rat_ap')->references('id')->on('apolices')->cascadeOnDelete(); $t->foreign('produtor_id','fk_rat_prod')->references('id')->on('produtores'); });
    }
    private function child(string $name, callable $columns, string $fk, string $index): void
    {
        Schema::create($name, function(Blueprint $t) use($columns,$fk,$index){ $t->id(); $t->unsignedBigInteger('apolice_id'); $columns($t); $t->foreign('apolice_id',$fk)->references('id')->on('apolices')->cascadeOnDelete(); $t->index('apolice_id',$index); });
    }
    public function down(): void { foreach(['apolice_rateio','apolice_parcelas','apolice_coberturas','apolice_beneficiarios','apolice_vidas','apolices','ramos','seguradoras'] as $table) Schema::dropIfExists($table); }
};
