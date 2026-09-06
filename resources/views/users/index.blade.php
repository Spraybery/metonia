@extends('layouts.master')
@section('page_title', 'System Users & Access Control (RBAC)')

@section('content')
<div class="content">

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
                                    <button type="button" class="btn btn-xs btn-outline-info font-weight-semibold px-2" data-toggle="modal" data-target="#modal-edit-user-{{ $user->id }}" title="Edit Account">
                                        <i class="icon-pencil mr-1"></i> Edit
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

                                {{-- Edit Modal --}}
                                <div id="modal-edit-user-{{ $user->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-slate-800 text-white">
                                                <h6 class="modal-title font-weight-bold">Edit User Account: {{ $user->username }}</h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('users.update', $user->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Full Staff Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Username <span class="text-danger">*</span></label>
                                                            <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Email Address <span class="text-danger">*</span></label>
                                                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">System RBAC Role <span class="text-danger">*</span></label>
                                                        <select name="role" class="form-control" required>
                                                            @foreach($roles as $r)
                                                                <option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">New Password</label>
                                                        <div class="input-group">
                                                            <input type="password" name="password" id="edit-user-pass-{{ $user->id }}" class="form-control" placeholder="Leave blank to preserve existing password" minlength="6">
                                                            <div class="input-group-append">
                                                                <button type="button" class="btn btn-light border" onclick="togglePasswordVisibility('edit-user-pass-{{ $user->id }}', this)" title="Show/Hide Password">👁️</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary font-weight-semibold">Save Changes</button>
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
