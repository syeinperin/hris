@extends('layouts.app')

@section('content')
<div class="container">
  <h3>New Performance Evaluation</h3>

  <form method="POST" action="{{ route('evaluation.store') }}">
    @csrf

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Employee</label>
        <select name="employee_id" class="form-select" required>
          <option value="">Select…</option>
          @foreach($employees as $id=>$name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Evaluator</label>
        <select name="evaluator_id" class="form-select" required>
          <option value="">Select…</option>
          @foreach($users as $id=>$name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Plan</label>
        <select id="planSelect" name="performance_plan_id" class="form-select" required>
          <option value="">— pick a plan —</option>
          @foreach($plans as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Evaluation Date</label>
      <input type="date" name="evaluation_date"
             class="form-control" required>
    </div>

    <div id="planItemsContainer"></div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select" required>
        <option value="pending">Pending</option>
        <option value="completed">Completed</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('evaluation.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection

@push('scripts')
<script>
  const plans = @json($plans);
  const container = document.getElementById('planItemsContainer');

  document.getElementById('planSelect').addEventListener('change', function(){
    const plan = plans.find(p=> p.id == this.value);
    container.innerHTML = '';

    if (!plan) return;

    let html = '<table class="table table-striped">'
             + '<thead><tr>'
             + '<th>Metric</th><th class="text-center">Weight (%)</th>'
             + '<th>Score (0–100)</th><th>Notes</th>'
             + '</tr></thead><tbody>';

    plan.items.forEach(item => {
      html += `<tr>
        <td>
          ${item.metric}
          <input type="hidden"
                 name="items[${item.id}][metric]"
                 value="${item.metric}">
        </td>
        <td class="text-center">
          ${item.weight}
          <input type="hidden"
                 name="items[${item.id}][weight]"
                 value="${item.weight}">
        </td>
        <td>
          <input type="number"
                 name="items[${item.id}][actual]"
                 class="form-control"
                 min="0" max="100"
                 required>
        </td>
        <td>
          <input type="text"
                 name="items[${item.id}][notes]"
                 class="form-control">
        </td>
      </tr>`;
    });

    html += '</tbody></table>';
    container.innerHTML = html;
  });
</script>
@endpush
