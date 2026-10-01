<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    // Dados bancários e do Contratado (CCITI) - fixos, não variam de
    // proposta pra proposta, então não fazem parte do formulário.
    public const BANK_NAME = '0260 - NU PAGAMENTOS S.A.';
    public const BANK_AGENCY = '0001';
    public const BANK_ACCOUNT = '94348896-3';
    public const BANK_BENEFICIARY = 'CCITI — Felipe da Silva Aquino (161.088.377-21)';
    public const BANK_PIX = '26.533.625/0001-48';

    public const CONTRATADO_NOME = 'Felipe da Silva Aquino';
    public const CONTRATADO_EMPRESA = 'CCITI — Consultoria de Cloud e Infraestrutura de TI';
    public const CONTRATADO_CNPJ = '26.533.625/0001-48';
    public const CONTRATADO_RESPONSAVEL = 'Felipe da Silva Aquino';

    public const CONFIDENTIALITY_TEXT = 'As Partes obrigam-se a manter o mais absoluto sigilo sobre quaisquer dados, documentos, segredos de negócio, senhas, topologias de rede ou informações comerciais a que venham a ter acesso em razão deste contrato, respondendo civil e criminalmente por qualquer vazamento culposo ou doloso.';

    public const DOWN_PAYMENT_NOTE = 'Pago no ato da assinatura deste instrumento, liberando o início dos trabalhos.';
    public const FINAL_PAYMENT_NOTE = 'Vencimento programado para 30 (trinta) dias após a finalização integral das instalações e configurações do projeto.';
    public const PAYMENT_INTRO = 'Pela execução integral dos serviços descritos neste instrumento, a CONTRATANTE pagará ao CONTRATADO o valor fixo e irreajustável abaixo, conforme condições de pagamento especificadas.';
    public const EXCLUSIONS_TEXT = 'Fica expressamente acordado entre as partes que qualquer serviço, funcionalidade, implantação, integração ou configuração que não esteja explicitamente descrita na Cláusula Primeira não faz parte do escopo deste contrato, devendo ser objeto de nova avaliação, negociação e aditivo contratual caso sejam solicitados pela CONTRATANTE.';
    public const PREREQUISITES_INTRO = 'Para a fiel execução dos serviços, a CONTRATANTE compromete-se a fornecer e manter a infraestrutura e os acessos descritos abaixo:';
    public const SCHEDULE_NOTE = 'Atrasos no fornecimento de acessos a equipamentos, demora na disponibilização de informações de processos ou indisponibilidade da infraestrutura por parte da CONTRATANTE suspenderão temporariamente a contagem do prazo de entrega.';

    // Local do fechamento do contrato - fixo, não varia de proposta pra
    // proposta (ver Cláusula de Assinaturas em admin.proposal.document).
    public const SIGNATURE_LOCATION = 'Rio de Janeiro - RJ';

    private const MONTHS_PT = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    public static function monthNamePt(int $month): string
    {
        return self::MONTHS_PT[$month] ?? '';
    }

    protected $fillable = [
        'customer_id',
        'title',
        'subtitle',
        'document_label',
        'proposal_year',
        'about_text',
        'total_value',
        'down_payment_percent',
        'final_payment_percent',
        'estimated_days',
        'contratante_responsavel',
        'object_intro',
        'status',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'proposal_year' => 'integer',
            'total_value' => 'decimal:2',
            'down_payment_percent' => 'decimal:2',
            'final_payment_percent' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function phases()
    {
        return $this->hasMany(ProposalPhase::class)->orderBy('order');
    }

    public function prerequisites()
    {
        return $this->hasMany(ProposalPrerequisite::class)->orderBy('order');
    }

    public function scheduleSteps()
    {
        return $this->hasMany(ProposalScheduleStep::class)->orderBy('order');
    }

    public function versions()
    {
        return $this->hasMany(ProposalVersion::class)->orderByDesc('version_number');
    }

    public function billingPolicies()
    {
        return $this->morphMany(BillingPolicy::class, 'billable');
    }

    public function latestVersion(): ?ProposalVersion
    {
        return $this->versions()->first();
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Rascunho',
            self::STATUS_SENT => 'Enviada',
            self::STATUS_ACCEPTED => 'Aceita',
            self::STATUS_REJECTED => 'Recusada',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function downPaymentValue(): float
    {
        return round((float) $this->total_value * ((float) $this->down_payment_percent / 100), 2);
    }

    public function finalPaymentValue(): float
    {
        return round((float) $this->total_value * ((float) $this->final_payment_percent / 100), 2);
    }
}
