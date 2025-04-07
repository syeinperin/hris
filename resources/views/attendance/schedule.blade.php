@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Schedules</h3>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Display Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Shift Creation Form --}}
    <form method="POST" action="{{ route('schedule.store') }}" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <label>Name <small>(no spaces)</small></label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Shift-One" required>
            </div>
            <div class="col-md-3">
                <label>Time In</label>
                <input type="time" name="time_in" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Time Out</label>
                <input type="time" name="time_out" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Save</button>
            </div>
        </div>
    </form>

    {{-- Shifts Table --}}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Shift</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($schedules as $index => $schedule)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $schedule->name }}</td>
                <td>{{ $schedule->time_in }}</td>
                <td>{{ $schedule->time_out }}</td>
                <td>
                    <!-- Edit Button -->
                    <button type="button"
                            class="btn btn-sm btn-warning edit-button"
                            data-id="{{ $schedule->id }}"
                            data-name="{{ $schedule->name }}"
                            data-time_in="{{ $schedule->time_in }}"
                            data-time_out="{{ $schedule->time_out }}"
                            style="position: relative; z-index: 9999;">
                        Edit
                    </button>

                    <!-- Delete Button -->
                    <form action="{{ route('schedule.destroy', $schedule->id) }}" method="POST"
                          class="d-inline-block"
                          onsubmit="return confirm('Are you sure you want to delete this shift?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Edit Schedule Modal (hidden by default) -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
         @csrf
         @method('PUT')
         <div class="modal-header">
           <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="mb-3">
                <label for="edit-name" class="form-label">Name <small>(no spaces)</small></label>
                <input type="text" name="name" id="edit-name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="edit-time_in" class="form-label">Time In</label>
                <input type="time" name="time_in" id="edit-time_in" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="edit-time_out" class="form-label">Time Out</label>
                <input type="time" name="time_out" id="edit-time_out" class="form-control" required>
            </div>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
           <button type="submit" class="btn btn-primary">Update Schedule</button>
         </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("Script block loaded"); // Debug log

    // Get the modal element and initialize the Bootstrap modal
    const editModalEl = document.getElementById("editScheduleModal");
    const editModal = new bootstrap.Modal(editModalEl);
    const form = editModalEl.querySelector("form");

    // Attach click event to each edit button
    document.querySelectorAll(".edit-button").forEach(button => {
        button.addEventListener("click", () => {
            console.log("Edit button clicked"); // Debug log

            // Extract schedule data from the button's data attributes
            const scheduleId = button.getAttribute("data-id");
            const scheduleName = button.getAttribute("data-name");
            const timeIn = button.getAttribute("data-time_in");
            const timeOut = button.getAttribute("data-time_out");

            // Populate the modal input fields with the schedule data
            document.getElementById("edit-name").value = scheduleName;
            document.getElementById("edit-time_in").value = timeIn;
            document.getElementById("edit-time_out").value = timeOut;

            // Update the form's action to include the schedule ID (e.g., /schedule/1)
            form.setAttribute("action", "{{ url('schedule') }}/" + scheduleId);

            // Show the modal
            editModal.show();
        });
    });
});
</script>
@endsection
