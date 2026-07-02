@props(['mascarado', 'completo'])

@if($completo)
    <span class="dado-sensivel" data-mascarado="{{ $mascarado }}" data-completo="{{ $completo }}">{{ $mascarado }}</span>
    <button type="button"
            class="btn btn-sm btn-link p-0 align-baseline toggle-dado-sensivel"
            title="Mostrar/ocultar">
        <i class="bi bi-eye"></i>
    </button>
@else
    <span class="text-muted">-</span>
@endif
