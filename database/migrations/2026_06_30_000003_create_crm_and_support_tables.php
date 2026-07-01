<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function(Blueprint $t){
            $t->increments('id'); $t->string('nome',120); $t->string('email',150)->unique(); $t->string('senha_hash');
            $t->enum('perfil',['ADMIN','COMUM','PRODUTOR'])->default('COMUM'); $t->unsignedInteger('produtor_id')->nullable();
            $t->boolean('duas_etapas')->default(false); $t->boolean('ativo')->default(true); $t->dateTime('ultimo_acesso')->nullable(); $t->timestamps();
            $t->foreign('produtor_id','fk_user_prod')->references('id')->on('produtores');
        });
        Schema::create('permissoes', function(Blueprint $t){
            $t->id(); $t->unsignedInteger('usuario_id'); $t->string('modulo',40); $t->boolean('pode_ver')->default(true); $t->boolean('pode_editar')->default(false);
            $t->foreign('usuario_id','fk_perm_user')->references('id')->on('usuarios')->cascadeOnDelete(); $t->unique(['usuario_id','modulo'],'uq_perm');
        });
        Schema::create('leads', function(Blueprint $t){
            $t->id(); $t->string('nome',150); $t->string('email',150)->nullable(); $t->string('telefone',20)->nullable(); $t->string('cpf',14)->nullable();
            $t->string('origem',60)->nullable(); $t->string('ramo_interesse',40)->nullable();
            $t->enum('etapa',['NOVO','QUALIFICADO','PROPOSTA','FECHADO','PERDIDO'])->default('NOVO');
            $t->enum('score',['QUENTE','MORNO','FRIO','DESCARTAR'])->nullable(); $t->integer('score_valor')->nullable();
            $t->unsignedInteger('produtor_id')->nullable(); $t->unsignedBigInteger('cliente_id')->nullable(); $t->string('motivo_perda',120)->nullable();
            $t->string('google_contact_id',80)->nullable(); $t->date('proximo_contato')->nullable(); $t->timestamps();
            $t->foreign('produtor_id','fk_lead_prod')->references('id')->on('produtores'); $t->foreign('cliente_id','fk_lead_cli')->references('id')->on('clientes');
            $t->index('etapa','idx_lead_etapa'); $t->index('score','idx_lead_score'); $t->index('proximo_contato','idx_lead_contato');
        });
        Schema::create('lead_interacoes', function(Blueprint $t){
            $t->id(); $t->unsignedBigInteger('lead_id'); $t->enum('tipo',['LIGACAO','WHATSAPP','EMAIL','REUNIAO','NOTA']); $t->text('descricao')->nullable();
            $t->unsignedInteger('usuario_id')->nullable(); $t->timestamp('created_at')->useCurrent();
            $t->foreign('lead_id','fk_int_lead')->references('id')->on('leads')->cascadeOnDelete(); $t->index('lead_id','idx_int_lead');
        });
        Schema::create('campanhas', function(Blueprint $t){
            $t->id(); $t->string('nome',120); $t->enum('canal',['EMAIL','WHATSAPP'])->default('EMAIL'); $t->string('gatilho',60)->nullable();
            $t->string('segmento',120)->nullable(); $t->text('template')->nullable(); $t->enum('status',['RASCUNHO','AGENDADA','ENVIANDO','CONCLUIDA'])->default('RASCUNHO');
            $t->dateTime('agendada_para')->nullable(); $t->timestamp('created_at')->useCurrent();
        });
        Schema::create('chamados', function(Blueprint $t){
            $t->id(); $t->enum('tipo',['CLIENTE','SEGURO','FINANCEIRO','SINISTRO'])->default('CLIENTE'); $t->string('subtipo',80)->nullable();
            $t->unsignedBigInteger('cliente_id')->nullable(); $t->unsignedBigInteger('apolice_id')->nullable(); $t->text('descricao')->nullable();
            $t->enum('status',['PENDENTE','EM_ANDAMENTO','FINALIZADO'])->default('PENDENTE'); $t->enum('prioridade',['BAIXA','MEDIA','ALTA'])->default('MEDIA');
            $t->date('data_resolucao')->nullable(); $t->unsignedInteger('responsavel_id')->nullable(); $t->enum('quem_fecha',['CRIADOR','QUALQUER'])->default('CRIADOR');
            $t->unsignedInteger('created_by')->nullable(); $t->timestamps();
            $t->foreign('cliente_id','fk_cham_cli')->references('id')->on('clientes'); $t->foreign('responsavel_id','fk_cham_resp')->references('id')->on('usuarios');
            $t->index('status','idx_cham_status'); $t->index('data_resolucao','idx_cham_data');
        });
        Schema::create('chamado_movimentos', function(Blueprint $t){
            $t->id(); $t->unsignedBigInteger('chamado_id'); $t->text('historico')->nullable(); $t->enum('novo_status',['PENDENTE','EM_ANDAMENTO','FINALIZADO'])->nullable();
            $t->date('nova_data')->nullable(); $t->unsignedInteger('usuario_id')->nullable(); $t->timestamp('created_at')->useCurrent();
            $t->foreign('chamado_id','fk_mov_cham')->references('id')->on('chamados')->cascadeOnDelete(); $t->index('chamado_id','idx_mov_cham');
        });
    }
    public function down(): void { foreach(['chamado_movimentos','chamados','campanhas','lead_interacoes','leads','permissoes','usuarios'] as $table) Schema::dropIfExists($table); }
};
