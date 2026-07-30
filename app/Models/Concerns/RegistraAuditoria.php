<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait RegistraAuditoria
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept($this->camposExcluidosDoLog())
            // Nome da tabela mesmo (no plural) como log_name: o singularizador
            // do Laravel é só pra inglês e erra em português ("viagens" vira
            // "viagen") — não vale a pena mapear manualmente só por estética.
            ->useLogName($this->getTable());
    }

    // Campos que nunca devem ir pro log de auditoria: timestamps redundantes
    // e, quando o model sobrescrever este método, segredos/dados sensíveis
    // (senha, token, CPF em texto puro) que não podem parar em texto plano
    // numa tabela sem a mesma criptografia do campo original.
    protected function camposExcluidosDoLog(): array
    {
        return $this->camposPadraoExcluidosDoLog();
    }

    // Traits não entram na cadeia de herança do PHP — um model que sobrescreve
    // camposExcluidosDoLog() não consegue chamar "parent::" pra herdar esta
    // lista base (isso resolveria pra Illuminate\Database\Eloquent\Model, que
    // não tem esse método). Por isso o padrão fica isolado aqui, acessível via
    // $this->camposPadraoExcluidosDoLog() em vez de parent::.
    protected function camposPadraoExcluidosDoLog(): array
    {
        return ['created_at', 'updated_at', 'created_by', 'updated_by'];
    }
}
