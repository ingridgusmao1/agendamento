@php
/** @var array<string,string>|null $paymentMethods */
$paymentMethods = $paymentMethods ?? [
    'dinheiro' => 'Dinheiro',
    'pix'      => 'Pix',
    'credito'  => 'Crédito',
    'debito'   => 'Débito',
    'outro'    => 'Outro',
];
@endphp

<div class="modal fade" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Registrar pagamento — Parcela {{ $installment->number }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <form id="paymentForm"
            method="POST"
            action="{{ route('admin.sales.installments.payments.store', ['sale' => $sale->id, 'installment' => $installment->id]) }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Valor a pagar</label>
            <input type="number"
                   name="amount"
                   class="form-control"
                   min="0.01"
                   step="0.01"
                   max="{{ number_format($remaining, 2, '.', '') }}"
                   placeholder="0,00">
            <div class="form-text">Restante desta parcela: {{ number_format($remaining, 2, ',', '.') }}</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Forma de pagamento</label>
            <select name="payment_method" class="form-select" required>
            @foreach($paymentMethods as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Observação (opcional)</label>
            <textarea name="note" class="form-control" rows="3" maxlength="500"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Registrar pagamento</button>
        </div>
      </form>

    </div>
  </div>
</div>
