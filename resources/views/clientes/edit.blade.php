@extends('layouts.app')
@section('title', 'Editar Cliente')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Editar Cliente</h4>
        <small class="text-muted">{{ $cliente->nome }}</small>
    </div>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('clientes.update', $cliente) }}" method="POST">
@csrf @method('PUT')
<div class="row g-4">

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-vcard me-2 text-primary"></i>Dados Principais
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo de Pessoa *</label>
                        <select name="tipo_pessoa" id="tipo_pessoa" class="form-select" required>
                            <option value="juridica" {{ old('tipo_pessoa', $cliente->tipo_pessoa) === 'juridica' ? 'selected' : '' }}>Pessoa Jurídica</option>
                            <option value="fisica"   {{ old('tipo_pessoa', $cliente->tipo_pessoa) === 'fisica'   ? 'selected' : '' }}>Pessoa Física</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" id="label_doc">
                            {{ $cliente->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }} *
                        </label>
                        <input type="text" name="cpf_cnpj" id="cpf_cnpj"
                               class="form-control @error('cpf_cnpj') is-invalid @enderror"
                               value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}" required>
                        @error('cpf_cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Inscrição Estadual</label>
                        <input type="text" name="ie" class="form-control"
                               value="{{ old('ie', $cliente->ie) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome / Fantasia *</label>
                        <input type="text" name="nome" class="form-control"
                               value="{{ old('nome', $cliente->nome) }}" required>
                    </div>
                    <div class="col-md-6" id="razao_social_group">
                        <label class="form-label fw-semibold">Razão Social</label>
                        <input type="text" name="razao_social" class="form-control"
                               value="{{ old('razao_social', $cliente->razao_social) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Telefone</label>
                        <input type="text" name="telefone" id="telefone" class="form-control"
                               value="{{ old('telefone', $cliente->telefone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Celular</label>
                        <input type="text" name="celular" id="celular" class="form-control"
                               value="{{ old('celular', $cliente->celular) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $cliente->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome do Contato</label>
                        <input type="text" name="contato" class="form-control"
                               value="{{ old('contato', $cliente->contato) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Frete Padrão (R$/km)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" name="tabela_frete" class="form-control"
                                   value="{{ old('tabela_frete', $cliente->tabela_frete) }}"
                                   step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="ativo"   {{ old('status', $cliente->status) === 'ativo'   ? 'selected' : '' }}>Ativo</option>
                            <option value="inativo" {{ old('status', $cliente->status) === 'inativo' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes', $cliente->observacoes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-geo-alt me-2 text-primary"></i>Endereço
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">CEP</label>
                        <div class="input-group">
                            <input type="text" name="cep" id="cep" class="form-control"
                                   value="{{ old('cep', $cliente->cep) }}" maxlength="9">
                            <button type="button" class="btn btn-outline-secondary" id="btn_cep">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Logradouro</label>
                        <input type="text" name="logradouro" id="logradouro" class="form-control"
                               value="{{ old('logradouro', $cliente->logradouro) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Número</label>
                        <input type="text" name="numero" class="form-control"
                               value="{{ old('numero', $cliente->numero) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Complemento</label>
                        <input type="text" name="complemento" class="form-control"
                               value="{{ old('complemento', $cliente->complemento) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Bairro</label>
                        <input type="text" name="bairro" id="bairro" class="form-control"
                               value="{{ old('bairro', $cliente->bairro) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Cidade</label>
                        <input type="text" name="cidade" id="cidade" class="form-control"
                               value="{{ old('cidade', $cliente->cidade) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <input type="text" name="estado" id="estado" class="form-control"
                               value="{{ old('estado', $cliente->estado) }}"
                               maxlength="2" style="text-transform:uppercase">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Atualizar Cliente
        </button>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
    const tipoPessoa = document.getElementById('tipo_pessoa');
    const labelDoc   = document.getElementById('label_doc');
    const razaoGroup = document.getElementById('razao_social_group');

    function atualizarTipo() {
        if (tipoPessoa.value === 'juridica') {
            labelDoc.textContent     = 'CNPJ *';
            razaoGroup.style.display = '';
        } else {
            labelDoc.textContent     = 'CPF *';
            razaoGroup.style.display = 'none';
        }
    }

    tipoPessoa.addEventListener('change', atualizarTipo);
    atualizarTipo();

    document.getElementById('cep').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/(\d{5})(\d)/, '$1-$2');
        this.value = v;
    });

    document.getElementById('btn_cep').addEventListener('click', function () {
        const cep = document.getElementById('cep').value.replace(/\D/g, '');
        if (cep.length !== 8) { alert('CEP inválido.'); return; }
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(r => r.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('logradouro').value = data.logradouro || '';
                    document.getElementById('bairro').value     = data.bairro || '';
                    document.getElementById('cidade').value     = data.localidade || '';
                    document.getElementById('estado').value     = data.uf || '';
                }
            });
    });
</script>
@endpush