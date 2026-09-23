@extends('layouts.master')
@section('page_title', 'System Users & Access Control (RBAC)')

@section('content')
<div class="content">

    @if($pendingUsers->count() > 0)
    <div class="card border-warning">
        <div class="card-header header-elements-inline" style="background-color: #fffbeb;">
            <h6 class="card-title font-weight-bold">
                <i class="icon-user-plus mr-2 text-warning"></i> Pending Account Requests
                <span class="badge badge-warning badge-pill ml-1">{{ $pendingUsers->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 50px;">#</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th class="text-center">Requested Role</th>
                            <th class="text-center">Requested On</th>
                            <th class="text-center" style="width: 190px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingUsers as $pending)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-weight-bold text-dark">{{ $pending->name }}</td>
                            <td><span class="badge badge-secondary">{{ $pending->username }}</span></td>
                            <td>{{ $pending->email }}</td>
                            <td class="text-center">
                                @php
                                    $pendingRoleBadge = match($pending->role) {
                                        'Storekeeper', 'Store Keeper', 'Shopkeeper' => 'badge-warning',
                                        'Accountant' => 'badge-success',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $pendingRoleBadge }} font-weight-bold">{{ $pending->role }}</span>
                            </td>
                            <td class="text-center text-muted font-size-xs">{{ $pending->created_at ? $pending->created_at->format('d M Y') : '—' }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                    <form method="POST" action="{{ route('users.approve', $pending->id) }}" id="approve-user-{{ $pending->id }}" class="d-inline">
                                        @csrf @method('PUT')
                                    </form>
                                    <button type="button" onclick="document.getElementById('approve-user-{{ $pending->id }}').submit();" class="btn btn-xs btn-success font-weight-semibold px-2" title="Approve Account">
                                        <i class="icon-checkmark3 mr-1"></i> Approve
                                    </button>

                                    <form method="POST" action="{{ route('users.reject', $pending->id) }}" id="reject-user-{{ $pending->id }}" class="d-inline">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button" onclick="if(confirm('Reject the account request from \'{{ $pending->username }}\'? This cannot be undone.')) { document.getElementById('reject-user-{{ $pending->id }}').submit(); }" class="btn btn-xs btn-outline-danger font-weight-semibold px-2" title="Reject Account">
                                        <i class="icon-cross mr-1"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-user-lock mr-2 text-primary"></i> System Users &amp; Role-Based Access Control (RBAC)
            </h6>
            <div class="header-elements">
                <button type="button" class="btn btn-primary btn-sm font-weight-semibold mr-1" data-toggle="modal" data-target="#modal-add-user">
                    <i class="icon-plus2 mr-1"></i> Add System User
                </button>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 50px;">#</th>
                            <th>Staff Member Name</th>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th class="text-center">Assigned Role</th>
                            <th class="text-center">Registered</th>
                            <th class="text-center no-export" style="width: 170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-bold text-dark">{{ $user->name }}</span>
                                @if(Auth::id() === $user->id)
                                    <span class="badge badge-success ml-1">You</span>
                                @endif
                            </td>
                            <td><span class="badge badge-secondary">{{ $user->username }}</span></td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">
                                @php
                                    $roleBadge = match($user->role) {
                                        'Admin' => 'badge-dark',
                                        'Manager' => 'badge-primary',
                                        'Storekeeper', 'Store Keeper', 'Shopkeeper' => 'badge-warning',
                                        'Accountant' => 'badge-success',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }} font-weight-bold">{{ $user->role }}</span>
                            </td>
                            <td class="text-center text-muted font-size-xs">{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                    <button type="button" class="btn btn-xs btn-outline-info font-weight-semibold px-2" data-toggle="modal" data-target="#modal-edit-user-{{ $user->id }}" title="Change RBAC Role">
                                        <i class="icon-pencil mr-1"></i> Role
                                    </button>

                                    @if(Auth::id() !== $user->id)
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" id="del-user-{{ $user->id }}" class="d-inline">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button" onclick="if(confirm('Delete user account \'{{ $user->username }}\'?')) { document.getElementById('del-user-{{ $user->id }}').submit(); }" class="btn btn-xs btn-outline-danger font-weight-semibold px-2" title="Delete Account">
                                            <i class="icon-trash mr-1"></i> Delete
                                        </button>
                                    @endif
                                </div>

                                {{-- Change Role Modal --}}
                                <div id="modal-edit-user-{{ $user->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-slate-800 text-white">
                                                <h6 class="modal-title font-weight-bold">Change RBAC Role: {{ $user->username }}</h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('users.update', $user->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <p class="text-muted font-size-sm">
                                                        Name, username, email, and password belong to <strong>{{ $user->name }}</strong>'s own account and can only be changed by them from their "My Account" settings. As Admin you may only reassign their system role here.
                                                    </p>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-semibold">System RBAC Role <span class="text-danger">*</span></label>
                                                        <select name="role" class="form-control" required>
                                                            @foreach($roles as $r)
                                                                <option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary font-weight-semibold">Save Role</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Add User Modal --}}
<div id="modal-add-user" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate-800 text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-plus2 mr-2"></i> Register New System User
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-semibold">Full Staff Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Samuel Mutiso" required>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="smutiso" required>
                            <small class="form-text text-muted">Can be shared across staff in the same role (e.g. several Accountants) — email keeps each account unique.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="smutiso@metonia.co.ke" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">System Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required>
                            @foreach($roles as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Password (min 6 chars) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="add-user-pass" class="form-control" required minlength="6" value="password">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-light border" onclick="togglePasswordVisibility('add-user-pass', this)" title="Show/Hide Password">👁️</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
