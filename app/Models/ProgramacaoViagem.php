<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramacaoViagem extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $table = 'programacoes_viagem';

    protected $fillable = [
        'motorista_id',
        'veiculo_id',
        'carreta_id',
        'cliente_id',
        'viagem_origem_id',
        'origem',
        'origem_uf',
        'origem_codigo_municipio',
        'destino',
        'destino_uf',
        'destino_codigo_municipio',
        'valor_frete',
        'data_prevista',
        'hora_coleta',
        'data_entrega_prevista',
        'hora_entrega_prevista',
        'chegada_horario_informado',
        'chegada_informada_em',
        'observacoes',
    ];

    protected $casts = [
        'data_prevista'             => 'date',
        'data_entrega_prevista'     => 'date',
        'valor_frete'               => 'decimal:2',
        'chegada_horario_informado' => 'datetime',
        'chegada_informada_em'      => 'datetime',
    ];

    public function motorista()
    {
        return $this->belongsTo(Motorista::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    // Carreta usada nesta viagem específica — pode ser diferente da carreta
    // vinculada ao cavalo no cadastro, se essa estiver indisponível.
    public function carreta()
    {
        return $this->belongsTo(Veiculo::class, 'carreta_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Viagem a partir da qual esta programação foi criada (rastreabilidade)
    public function viagemOrigem()
    {
        return $this->belongsTo(Viagem::class, 'viagem_origem_id');
    }

    // Viagem real, criada ao confirmar a programação
    public function viagem()
    {
        return $this->belongsTo(Viagem::class, 'viagem_id');
    }

    // Paradas além da Origem/Destino principais (ex.: 2ª coleta em Curitiba,
    // entregas extras em Salvador e Maceió) — ordenadas pra já deixar pronta
    // a sequência real da viagem (roteirização futura). Entregas viram
    // sugestão de Carga ao confirmar esta programação em Viagem (ver
    // Viagem::entregasPendentes()); coletas são só informativas por ora.
    public function paradas()
    {
        return $this->hasMany(ParadaProgramacao::class, 'programacao_viagem_id')->orderBy('ordem');
    }

    public function paradasColeta()
    {
        return $this->paradas()->where('tipo', 'coleta');
    }

    public function paradasEntrega()
    {
        return $this->paradas()->where('tipo', 'entrega');
    }

    public function estaPendente(): bool
    {
        return $this->status === 'pendente';
    }

    // Combina data_prevista + hora_coleta num único instante — null se a hora
    // de coleta ainda não foi preenchida (programação antiga, ou cadastrada
    // sem esse dado).
    public function getColetaPrevistaEmAttribute(): ?\Illuminate\Support\Carbon
    {
        if (! $this->data_prevista || ! $this->hora_coleta) {
            return null;
        }

        return \Illuminate\Support\Carbon::parse(
            $this->data_prevista->format('Y-m-d') . ' ' . $this->hora_coleta
        );
    }

    public function chegadaInformada(): bool
    {
        return $this->chegada_horario_informado !== null;
    }

    // Regra da Devolutiva: risco de no-show é a coleta prevista chegando (ou já
    // passada) sem confirmação de chegada — sempre a partir do horário que o
    // motorista informou, nunca de quando o operador revisou o check-in.
    public function getEmRiscoDeNoShowAttribute(): bool
    {
        return $this->estaPendente()
            && $this->coleta_prevista_em !== null
            && ! $this->chegadaInformada()
            && $this->coleta_prevista_em->lte(now()->addHours(2));
    }

    // Filtra em PHP (via accessor), não em SQL: combinar data + hora e comparar
    // com "agora + 2h" não tem uma expressão portável entre MySQL (produção) e
    // SQLite (suíte de testes) — o universo candidato (pendente, com hora de
    // coleta, sem chegada) já é pequeno o bastante pra não pesar.
    public static function emRiscoDeNoShow()
    {
        return static::where('status', 'pendente')
            ->whereNotNull('hora_coleta')
            ->whereNull('chegada_horario_informado')
            ->get()
            ->filter(fn (self $programacao) => $programacao->em_risco_de_no_show)
            ->values();
    }

    // Grava o horário que o motorista (ou, na ausência dele, o operador) informa
    // como o momento real da chegada no local de coleta — sempre no dia da
    // coleta prevista. chegada_informada_em registra separadamente quando o
    // sistema recebeu o registro, só para auditoria.
    public function marcarChegada(string $horario): void
    {
        $this->update([
            'chegada_horario_informado' => \Illuminate\Support\Carbon::parse(
                $this->data_prevista->format('Y-m-d') . ' ' . $horario
            ),
            'chegada_informada_em' => now(),
        ]);
    }
}
