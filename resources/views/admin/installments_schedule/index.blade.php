@extends('layouts.admin')

@section('title', __('global.installments_schedule'))

@section('content')
<div class="container-fluid mt-4">

    {{-- Filtro --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.installments-schedule.index') }}" class="row g-2 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label for="filter" class="form-label mb-1">{{ __('global.filter_by') }}</label>
                    <select id="filter" name="filter" class="form-select">
                        <option value=""      {{ ($filter ?? '') === '' ? 'selected' : '' }}>{{ __('global.all') }}</option>
                        <option value="overdue" {{ ($filter ?? '') === 'overdue' ? 'selected' : '' }}>{{ __('global.overdue') }}</option>
                        <option value="today"   {{ ($filter ?? '') === 'today'   ? 'selected' : '' }}>{{ __('global.due_today') }}</option>
                        <option value="soon3"   {{ ($filter ?? '') === 'soon3'   ? 'selected' : '' }}>{{ __('global.due_in_3_days') }}</option>
                        <option value="soon5"   {{ ($filter ?? '') === 'soon5'   ? 'selected' : '' }}>{{ __('global.due_in_5_days') }}</option>
                        <option value="others"  {{ ($filter ?? '') === 'others'  ? 'selected' : '' }}>{{ __('global.others_future') }}</option>
                    </select>
                </div>

                <div class="col-sm-8 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        {{ __('global.apply_filter') }}
                    </button>
                    <a href="{{ route('admin.installments-schedule.index') }}" class="btn btn-outline-secondary">
                        {{ __('global.clear_filter') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ __('global.installments_schedule') }}</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('global.customer') }}</th>
                        <th>{{ __('global.sale') }}</th>
                        <th>{{ __('global.installment') }}</th>
                        <th>{{ __('global.amount') }}</th>
                        <th>{{ __('global.due_date') }}</th>
                        <th>{{ __('global.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($installments as $i)
                        @php
                            $classes = match($i->highlight) {
                                'overdue' => 'table-danger border-danger border-2',
                                'today'   => 'table-warning border-warning border-2',
                                'soon3'   => 'table-warning border-opacity-75',
                                'soon5'   => 'table-warning border-opacity-50',
                                default   => '',
                            };
                        @endphp
                        <tr class="{{ $classes }}">
                            <td>{{ $i->sale->customer->name ?? '-' }}</td>
                            <td>#{{ $i->sale_id }}</td>
                            <td>{{ $i->number ?? '-' }}</td>
                            <td>R$ {{ number_format($i->amount, 2, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($i->due_date)->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($i->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4">{{ __('global.no_pending_installments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
