<div class="modal fade" id="disciplineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form action="{{ route('discipline.store') }}" method="POST">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-exclamation-octagon me-2"></i>
            Violations / Suspension
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        {{-- Scrollable body --}}
        <div class="modal-body p-3" style="max-height:70vh; overflow-y:auto;">
          <div class="row g-3">
            <div class="col-md-5">
              <div class="card h-100">
                <div class="card-header bg-light fw-semibold">Record New Action</div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                      <option value="">-- choose employee --</option>
                      @foreach($employees as $id => $name)
                        <option value="{{ $id }}" {{ old('employee_id')==$id?'selected':'' }}>{{ $name }}</option>
                      @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Action Type</label>
                    <select id="discipline-type" name="action_type" class="form-select" required>
                      <option value="violation"  {{ old('action_type','violation')==='violation'?'selected':'' }}>Violation</option>
                      <option value="suspension" {{ old('action_type')==='suspension'?'selected':'' }}>Suspension</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Category (optional)</label>
                    <input name="category" value="{{ old('category') }}" class="form-control" placeholder="Attendance, Conduct, Safety...">
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select" required>
                      @foreach(['minor','major','critical'] as $s)
                        <option value="{{ $s }}" {{ old('severity','minor')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Points (optional)</label>
                    <input type="number" name="points" min="0" max="100" value="{{ old('points') }}" class="form-control">
                  </div>

                  <div id="suspension-dates" class="row g-2 {{ old('action_type','violation')==='suspension' ? '' : 'd-none' }}">
                    <div class="col-md-6">
                      <label class="form-label">Start Date</label>
                      <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">End Date</label>
                      <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Reason / Description</label>
                    <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="mb-0">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-7">
              <div class="card h-100">
                <div class="card-header bg-light fw-semibold">Recent Actions</div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th>When</th>
                          <th>Employee</th>
                          <th>Type</th>
                          <th>Category</th>
                          <th>Severity</th>
                          <th>Status</th>
                          <th class="text-end">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($recentActions ?? [] as $a)
                          <tr>
                            <td class="text-nowrap">{{ $a->created_at->format('Y-m-d') }}</td>
                            <td>{{ $a->employee->name }}</td>
                            <td>
                              <span class="badge {{ $a->action_type==='suspension'?'bg-danger':'bg-warning text-dark' }}">
                                {{ ucfirst($a->action_type) }}
                              </span>
                            </td>
                            <td>{{ $a->category ?? '' }}</td>
                            <td>{{ ucfirst($a->severity) }}</td>
                            <td>
                              <span class="badge {{ $a->status==='active'?'bg-primary':'bg-secondary' }}">
                                {{ ucfirst($a->status) }}
                              </span>
                            </td>
                            <td class="text-end">
                              @if($a->status==='active')
                                <form action="{{ route('discipline.resolve',$a) }}" method="POST" class="d-inline">
                                  @csrf @method('PUT')
                                  <button class="btn btn-sm btn-outline-success" title="Mark Resolved">
                                    <i class="bi bi-check2"></i>
                                  </button>
                                </form>
                              @endif
                              <form action="{{ route('discipline.destroy',$a) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                  <i class="bi bi-trash"></i>
                                </button>
                              </form>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="7" class="text-center text-muted py-3">No recent actions.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
                @if(($recentActions ?? null) && $recentActions->isNotEmpty())
                  <div class="card-footer small text-muted">
                    Showing latest {{ $recentActions->count() }} actions.
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>

        {{-- Fixed (non-scrolling) footer with buttons --}}
        <div class="modal-footer bg-white border-top">
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-save me-1"></i> Save Action
          </button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const typeSel = document.getElementById('discipline-type');
  const block   = document.getElementById('suspension-dates');
  if (typeSel && block) {
    const toggle = () => {
      if (typeSel.value === 'suspension') block.classList.remove('d-none');
      else block.classList.add('d-none');
    };
    typeSel.addEventListener('change', toggle);
    toggle(); // initial
  }
  @if ($errors->any())
    new bootstrap.Modal('#disciplineModal').show();
  @endif
});
</script>
@endpush
