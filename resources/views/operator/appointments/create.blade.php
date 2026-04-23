@extends('layouts.operator')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h1 class="h3">Create New Appointment</h1>
        <a href="{{ route('operator.appointments.index') }}" class="btn btn-secondary">Back</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('operator.appointments.store') }}" id="appointmentForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Appointment Date *</label>
                        <input type="date" name="appointment_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Appointment Type</label>
                        <select name="type" id="type" class="form-control">
                            <option value="single">Single</option>
                            <option value="multiple">Multiple</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Contact Name *</label>
                        <input type="text" name="contact_name" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Contact Mobile *</label>
                        <input type="text" name="contact_mobile" class="form-control" required>
                    </div>
                </div>
                
                <div id="clientsContainer">
                    <div class="client-card card mb-3">
                        <div class="card-header">
                            <h6>Client 1</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2"><label>First Name *</label><input type="text" name="clients[0][first_name]" class="form-control" required></div>
                                <div class="col-md-3 mb-2"><label>Middle Name</label><input type="text" name="clients[0][middle_name]" class="form-control"></div>
                                <div class="col-md-3 mb-2"><label>Last Name *</label><input type="text" name="clients[0][last_name]" class="form-control" required></div>
                                <div class="col-md-3 mb-2"><label>Suffix</label><input type="text" name="clients[0][suffix]" class="form-control"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-2"><label>Sex *</label><select name="clients[0][sex]" class="form-control" required><option value="Male">Male</option><option value="Female">Female</option></select></div>
                                <div class="col-md-4 mb-2"><label>Birthdate *</label><input type="date" name="clients[0][birthdate]" class="form-control" required></div>
                                <div class="col-md-4 mb-2"><label>Service *</label><select name="clients[0][service]" class="form-control" required><option value="reg">Registration</option><option value="correction">Correction</option><option value="ephilid">ePhilID</option><option value="trn">TRN Retrieval</option></select></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="addClientBtn" class="btn btn-secondary mb-3">+ Add Another Client</button>
                <button type="submit" class="btn btn-primary d-block">Create Appointment</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let clientCount = 1;
$('#addClientBtn').click(function() {
    let html = `<div class="client-card card mb-3">
        <div class="card-header d-flex justify-content-between">
            <h6>Client ${clientCount + 1}</h6>
            <button type="button" class="btn btn-sm btn-danger remove-client">Remove</button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2"><input type="text" name="clients[${clientCount}][first_name]" class="form-control" placeholder="First Name *" required></div>
                <div class="col-md-3 mb-2"><input type="text" name="clients[${clientCount}][middle_name]" class="form-control" placeholder="Middle Name"></div>
                <div class="col-md-3 mb-2"><input type="text" name="clients[${clientCount}][last_name]" class="form-control" placeholder="Last Name *" required></div>
                <div class="col-md-3 mb-2"><input type="text" name="clients[${clientCount}][suffix]" class="form-control" placeholder="Suffix"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-2"><select name="clients[${clientCount}][sex]" class="form-control" required><option value="Male">Male</option><option value="Female">Female</option></select></div>
                <div class="col-md-4 mb-2"><input type="date" name="clients[${clientCount}][birthdate]" class="form-control" placeholder="Birthdate *" required></div>
                <div class="col-md-4 mb-2"><select name="clients[${clientCount}][service]" class="form-control" required><option value="reg">Registration</option><option value="correction">Correction</option><option value="ephilid">ePhilID</option><option value="trn">TRN Retrieval</option></select></div>
            </div>
        </div>
    </div>`;
    $('#clientsContainer').append(html);
    clientCount++;
    
    $('.remove-client').click(function() {
        $(this).closest('.client-card').remove();
    });
});
</script>
@endpush
@endsection