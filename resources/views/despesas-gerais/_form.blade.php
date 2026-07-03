@php $despesa = $despesa ?? null; @endphp
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Categoria *</label>
        <select name="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
            @foreach(['aluguel'=>'Aluguel','salarios'=>'Salários','contas'=>'Contas (água, luz, internet...)','seguro'=>'Seguro','impostos'=>'Impostos','marketing'=>'Marketing','outros'=>'Outros'] as $valor => $rotulo)
            <option value="{{ $valor }}" {{ old('categoria', $despesa?->categoria) == $valor ? 'selected' : '' }}>
                {{ $rotulo }}
            </option>
            @endforeach
        </select>
        @error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label fw-semibold">Descrição *</label>
        <input type="text" name="descricao" class="form-control @error('descricao') is-invalid @enderror"
               value="{{ old('descricao', $despesa?->descricao) }}" required>
        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Valor (R$) *</label>
        <input type="number" name="valor" step="0.01" min="0"
               class="form-control @error('valor') is-invalid @enderror"
               value="{{ old('valor', $despesa?->valor) }}" required>
        @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Data *</label>
        <input type="date" name="data_despesa" class="form-control @error('data_despesa') is-invalid @enderror"
               value="{{ old('data_despesa', $despesa?->data_despesa?->format('Y-m-d')) }}" required>
        @error('data_despesa')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 d-flex align-items-center">
        <div class="form-check mt-4">
            <input type="checkbox" name="recorrente" id="recorrente" class="form-check-input" value="1"
                   {{ old('recorrente', $despesa?->recorrente) ? 'checked' : '' }}>
            <label class="form-check-label" for="recorrente">
                Despesa recorrente (se repete todo mês)
            </label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Observação</label>
        <textarea name="observacao" class="form-control" rows="2">{{ old('observacao', $despesa?->observacao) }}</textarea>
    </div>
</div>
