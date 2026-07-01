@extends('layouts.app')
@section('title', 'Novo Cliente')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Novo Cliente</h4>
        <small class="text-muted">Preencha os dados do cliente</small>
    </div>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('clientes.store') }}" method="POST">
@csrf
<div class="row g-4">

    {{-- Dados Principais --}}
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
                            <option value="juridica" {{ old('tipo_pessoa','juridica') === 'juridica' ? 'selected' : '' }}>Pessoa Jurídica</option>
                            <option value="fisica"   {{ old('tipo_pessoa') === 'fisica' ? 'selected' : '' }}>Pessoa Física</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" id="label_doc">CNPJ *</label>
                        <input type="text" name="cpf_cnpj" id="cpf_cnpj"
                               class="form-control @error('cpf_cnpj') is-invalid @enderror"
                               value="{{ old('cpf_cnpj') }}"
                               placeholder="00.000.000/0000-00" required>
                        @error('cpf_cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Inscrição Estadual</label>
                        <input type="text" name="ie" class="form-control"
                               value="{{ old('ie') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome / Fantasia *</label>
                        <input type="text" name="nome"
                               class="form-control @error('nome') is-invalid @enderror"
                               value="{{ old('nome') }}" required>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6" id="razao_social_group">
                        <label class="form-label fw-semibold">Razão Social</label>
                        <input type="text" name="razao_social" class="form-control"
                               value="{{ old('razao_social') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Telefone</label>
                        <input type="text" name="telefone" id="telefone" class="form-control"
                               value="{{ old('telefone') }}" placeholder="(00) 0000-0000">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Celular</label>
                        <input type="text" name="celular" id="celular" class="form-control"
                               value="{{ old('celular') }}" placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome do Contato</label>
                        <input type="text" name="contato" class="form-control"
                               value="{{ old('contato') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Frete Padrão (R$/km)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" name="tabela_frete" class="form-control"
                                   value="{{ old('tabela_frete') }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Endereço --}}
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
                                   value="{{ old('cep') }}" placeholder="00000-000" maxlength="9">
                            <button type="button" class="btn btn-outline-secondary" id="btn_cep">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Logradouro</label>
                        <input type="text" name="logradouro" id="logradouro" class="form-control"
                               value="{{ old('logradouro') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Número</label>
                        <input type="text" name="numero" class="form-control"
                               value="{{ old('numero') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Complemento</label>
                        <input type="text" name="complemento" class="form-control"
                               value="{{ old('complemento') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Bairro</label>
                        <input type="text" name="bairro" id="bairro" class="form-control"
                               value="{{ old('bairro') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Cidade</label>
                        <input type="text" name="cidade" id="cidade" class="form-control"
                               value="{{ old('cidade') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <input type="text" name="estado" id="estado" class="form-control"
                               value="{{ old('estado') }}" maxlength="2"
                               style="text-transform:uppercase">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Salvar Cliente
        </button>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
    // ── Tipo de pessoa: alterna label e máscara do documento
    const tipoPessoa = document.getElementById('tipo_pessoa');
    const labelDoc   = document.getElementById('label_doc');
    const inputDoc   = document.getElementById('cpf_cnpj');
    const razaoGroup = document.getElementById('razao_social_group');

    function atualizarTipo() {
        if (tipoPessoa.value === 'juridica') {
            labelDoc.textContent   = 'CNPJ *';
            inputDoc.placeholder   = '00.000.000/0000-00';
            razaoGroup.style.display = '';
        } else {
            labelDoc.textContent   = 'CPF *';
            inputDoc.placeholder   = '000.000.000-00';
            razaoGroup.style.display = 'none';
        }
        inputDoc.value = '';
    }

    tipoPessoa.addEventListener('change', atualizarTipo);
    atualizarTipo();

    // ── Máscara CNPJ/CPF
    inputDoc.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        if (tipoPessoa.value === 'juridica') {
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }
        this.value = v;
    });

    // ── Máscara Telefone
    document.getElementById('telefone').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '($1) $2');
        v = v.replace(/(\d{4})(\d{4})$/, '$1-$2');
        this.value = v;
    });

    // ── Máscara Celular
    document.getElementById('celular').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '($1) $2');
        v = v.replace(/(\d{5})(\d{4})$/, '$1-$2');
        this.value = v;
    });

    // ── Máscara CEP
    document.getElementById('cep').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/(\d{5})(\d)/, '$1-$2');
        this.value = v;
    });

    // ── Busca CEP via ViaCEP
    document.getElementById('btn_cep').addEventListener('click', function () {
        const cep = document.getElementById('cep').value.replace(/\D/g, '');
        if (cep.length !== 8) {
            alert('Digite um CEP válido com 8 dígitos.');
            return;
        }
        this.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(r => r.json())
            .then(data => {
                if (data.erro) {
                    alert('CEP não encontrado.');
                } else {
                    document.getElementById('logradouro').value = data.logradouro || '';
                    document.getElementById('bairro').value     = data.bairro || '';
                    document.getElementById('cidade').value     = data.localidade || '';
                    document.getElementById('estado').value     = data.uf || '';
                }
            })
            .catch(() => alert('Erro ao buscar CEP.'))
            .finally(() => {
                this.innerHTML = '<i class="bi bi-search"></i>';
            });
    });
</script>
@endpush